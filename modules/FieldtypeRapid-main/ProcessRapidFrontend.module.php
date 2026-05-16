<?php namespace ProcessWire;

/**
 * ProcessRapidFrontend
 *
 * Frontend editing helper for Rapid fields.
 *
 * Save requests are handled via a URL hook at the configured endpoint
 * (default: /rapid-save/), so no admin URL is needed — the frontend
 * session cookie works normally.
 *
 * Template usage:
 *
 *   $editor = $modules->get('ProcessRapidFrontend');
 *   echo $editor->renderField($page, 'body');
 *
 *   // or manually:
 *   if ($editor->canEdit($page, 'body')) {
 *       echo $editor->editorFor($page, 'body');
 *   } else {
 *       echo $page->body;
 *   }
 */
class ProcessRapidFrontend extends WireData implements Module {

	public static function getModuleInfo(): array {
		return [
			'title'    => 'Rapid Frontend Editor',
			'version'  => 100,
			'summary'  => 'Provides frontend editing support for Rapid fields.',
			'author'   => 'Maxim Semenov',
			'href'     => 'https://github.com/mxmsmnv/Rapid',
			'icon'     => 'bolt',
			'singular' => true,
			'autoload' => true,   // needed for URL hook
			'requires' => 'FieldtypeRapid',
		];
	}

	// ── Init: register URL hook ────────────────────────────────────────

	public function init(): void {
		// Register save hook on every non-admin request.
		// Hook fires when URL matches /rapid-save/ (or custom path).
		$this->addHookBefore('ProcessPageView::execute', $this, 'handleSaveRequest');
	}

	public function handleSaveRequest(HookEvent $event): void {
		$config = $this->wire('config');
		$input  = $this->wire('input');

		// Determine all registered save endpoints across all Rapid fields
		$saveUrls = [];
		foreach ($this->wire('fields')->find('type=FieldtypeRapid') as $field) {
			if ($field->get('frontendEdit')) {
				$url = trim((string)($field->get('frontendSaveUrl') ?: '/rapid-save/'));
				if ($url) $saveUrls[$url] = true;
			}
		}

		if (!$saveUrls) return;

		$page = $this->wire('page');
		$requestPath = '/' . ltrim(($page ? $page->url : null) ?: $input->url, '/');
		// Normalize: compare path without subdirectory prefix
		$rootUrl = rtrim($config->urls->root, '/');
		$path    = '/' . ltrim(substr($requestPath, strlen($rootUrl)), '/');
		if (!str_ends_with($path, '/')) $path .= '/';

		if (!isset($saveUrls[$path]) && !isset($saveUrls[rtrim($path, '/')])) return;

		// This is a save request — handle it
		header('Content-Type: application/json');

		if ($input->requestMethod() !== 'POST') {
			echo json_encode(['success' => false, 'error' => 'Method not allowed']);
			exit;
		}

		$pageId    = (int)$input->post('pageId');
		$fieldName = $this->wire('sanitizer')->fieldName((string)$input->post('fieldName'));
		$nonce     = (string)$input->post('nonce');
		$json      = (string)$input->post('data');

		if (!$pageId || !$fieldName || !$json) {
			echo json_encode(['success' => false, 'error' => 'Missing parameters']);
			exit;
		}

		// Verify nonce
		if (!$this->verifyNonce($nonce, $pageId, $fieldName)) {
			echo json_encode(['success' => false, 'error' => 'Invalid nonce']);
			exit;
		}

		$page  = $this->wire('pages')->get($pageId);
		$field = $this->wire('fields')->get($fieldName);

		if (!$page || !$page->id || !$field || !($field->type instanceof FieldtypeRapid)) {
			echo json_encode(['success' => false, 'error' => 'Page or field not found']);
			exit;
		}

		if (!$this->canEdit($page, $fieldName)) {
			echo json_encode(['success' => false, 'error' => 'Permission denied']);
			exit;
		}

		$data = json_decode($json, true);
		if (!is_array($data)) {
			echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
			exit;
		}

		try {
			$page->setOutputFormatting(false);
			$page->set($fieldName, $json);
			$page->save($fieldName);
			echo json_encode(['success' => true]);
		} catch (\Throwable $e) {
			echo json_encode(['success' => false, 'error' => $e->getMessage()]);
		}

		exit;
	}

	// ── Permission check ───────────────────────────────────────────────

	public function canEdit(Page $page, string $fieldName): bool {
		$user  = $this->wire('user');
		$field = $this->wire('fields')->get($fieldName);
		if (!$field || !($field->type instanceof FieldtypeRapid)) return false;
		if (!(bool)$field->get('frontendEdit')) return false;

		$perm = (string)($field->get('frontendPermission') ?: 'page-edit');
		if ($perm === 'superuser') return $user->isSuperuser();

		return $user->isLoggedin() && ($user->hasPermission($perm, $page) || $user->hasPermission('page-edit', $page));
	}

	// ── Render helpers ─────────────────────────────────────────────────

	public function renderField(Page $page, string $fieldName): string {
		if ($this->canEdit($page, $fieldName)) return $this->editorFor($page, $fieldName);
		$value = $page->get($fieldName);
		return $value ? (string)$value : '';
	}

	public function editorFor(Page $page, string $fieldName): string {
		$field = $this->wire('fields')->get($fieldName);
		if (!$field) return '';

		$config    = $this->wire('config');
		$value     = $page->get($fieldName);
		$json      = $value instanceof RapidValue ? $value->toJSON() : (string)$value;

		$moduleUrl = $config->urls->FieldtypeRapid;
		$adminUrl  = rtrim($config->urls->admin, '/');
		$uploadUrl = $adminUrl . '/setup/rapid-upload/upload/';
		$saveUrl   = '/rapid-save/';
		$nonce     = $this->generateNonce((int)$page->id, $fieldName);
		$debounce  = (int)($field->get('autosaveDebounce') ?? 500);
		$minHeight = (int)($field->get('editorMinHeight') ?: 200);
		$bundleVer = trim(@file_get_contents($config->paths->FieldtypeRapid . 'js/dist/version.txt') ?: '1');

		// Absolutize image URLs for editor display only
		$editorData = json_decode($json, true) ?: ['blocks' => []];
		$base = ($config->https ? 'https' : 'http') . '://' . $config->httpHost;
		if (!empty($editorData['blocks'])) {
			foreach ($editorData['blocks'] as &$blk) {
				if (($blk['type'] ?? '') === 'image') {
					if (!empty($blk['data']['url']) && str_starts_with($blk['data']['url'], '/')) {
						$blk['data']['url'] = $base . $blk['data']['url'];
					}
					if (!empty($blk['data']['file']['url']) && str_starts_with($blk['data']['file']['url'], '/')) {
						$blk['data']['file']['url'] = $base . $blk['data']['file']['url'];
					}
				}
			}
			unset($blk);
		}

		$holderId  = 'rapid-fe-' . $page->id . '-' . $fieldName;

		$bootstrap = json_encode([
			'holderId' => $holderId,
			'valueId'  => $holderId . '-json',
			'data'     => $editorData,
			'config'   => [
				'uploadUrl'     => $uploadUrl,
				'pageId'        => (int)$page->id,
				'minHeight'     => $minHeight,
				'placeholder'   => 'Start writing…',
				'debounce'      => 0,   // no autosave on frontend — user clicks Save
				'allowedBlocks' => (array)($field->get('allowedBlocks') ?: []),
				'headerLevels'  => (array)($field->get('headerLevels') ?: []),
				'headerDefault' => (int)($field->get('headerDefaultLevel') ?: 2),
				'maxUploadMB'   => (int)($field->get('maxUploadSizeMB') ?: 10),
				'inlineTools'   => (array)($field->get('inlineTools') ?: []),
			],
			'pageId' => (int)$page->id,
		], JSON_UNESCAPED_UNICODE);

		$saveConfig = json_encode([
			'saveUrl'   => $saveUrl,
			'pageId'    => (int)$page->id,
			'fieldName' => $fieldName,
			'nonce'     => $nonce,
			'holderId'  => $holderId,
		], JSON_UNESCAPED_UNICODE);

		$out  = "<div class='rapid-frontend-editor' data-field='{$fieldName}'>";
		$out .= "  <div id='{$holderId}' class='rapid-holder' style='min-height:{$minHeight}px'></div>";
		$out .= "  <input type='hidden' id='{$holderId}-json' value='" . htmlspecialchars($json) . "'>";
		$out .= "  <div class='rapid-fe-toolbar'>";
		$out .= "    <button type='button' class='rapid-fe-save' data-config='" . htmlspecialchars($saveConfig) . "'>Save</button>";
		$out .= "    <span class='rapid-fe-status'></span>";
		$out .= "  </div>";
		$out .= "  <script>window.EJSQueue=window.EJSQueue||[];window.EJSQueue.push($bootstrap);</script>";
		$out .= "</div>";

		// Assets — only once per page load
		static $assetsOutput = false;
		if (!$assetsOutput) {
			$assetsOutput = true;
			$v = $bundleVer;
			$out = "<link rel='stylesheet' href='{$moduleUrl}js/vendor/swiper.min.css'>"
			     . "<link rel='stylesheet' href='{$moduleUrl}js/editor.css?v={$v}'>"
			     . "<script src='{$moduleUrl}js/vendor/swiper.min.js'></script>"
			     . "<script src='{$moduleUrl}js/dist/editor.js?v={$v}'></script>"
			     . $this->styles()
			     . $this->script()
			     . $out;
		}

		return $out;
	}

	// ── Nonce ──────────────────────────────────────────────────────────

	private function generateNonce(int $pageId, string $fieldName): string {
		return hash_hmac('sha256', $pageId . ':' . $fieldName, (string)($this->wire('config')->userAuthSalt ?: 'rapid'));
	}

	private function verifyNonce(string $nonce, int $pageId, string $fieldName): bool {
		return hash_equals($this->generateNonce($pageId, $fieldName), $nonce);
	}

	// ── Assets ─────────────────────────────────────────────────────────

	private function styles(): string {
		return '<style>
.rapid-frontend-editor{position:relative}
.rapid-fe-toolbar{display:flex;align-items:center;gap:10px;padding:8px 0;margin-top:6px;border-top:1px solid #e2e8f0}
.rapid-fe-save{padding:6px 18px;background:#2563eb;color:#fff;border:none;cursor:pointer;font-size:14px;font-family:inherit;border-radius:4px}
.rapid-fe-save:hover{background:#1d4ed8}
.rapid-fe-save:disabled{opacity:.6;cursor:default}
.rapid-fe-status{font-size:13px;color:#64748b}
.rapid-fe-status.ok{color:#16a34a}
.rapid-fe-status.err{color:#dc2626}
</style>';
	}

	private function script(): string {
		return '<script>
(function(){
document.addEventListener("click",async function(e){
	const btn=e.target.closest(".rapid-fe-save");
	if(!btn)return;
	const cfg=JSON.parse(btn.dataset.config);
	const wrap=btn.closest(".rapid-fe-toolbar");
	const status=wrap&&wrap.querySelector(".rapid-fe-status");
	const holder=document.getElementById(cfg.holderId);
	const editor=holder&&holder._ejsEditor;
	if(!editor){if(status)status.textContent="Editor not ready";return;}
	btn.disabled=true;
	if(status){status.className="rapid-fe-status";status.textContent="Saving\u2026";}
	try{
		const saved=await editor.save();
		const data=JSON.stringify(saved);
		const jsonEl=document.getElementById(cfg.holderId+"-json");
		if(jsonEl)jsonEl.value=data;
		const res=await fetch(cfg.saveUrl,{
			method:"POST",
			credentials:"same-origin",
			headers:{"Content-Type":"application/x-www-form-urlencoded"},
			body:new URLSearchParams({pageId:cfg.pageId,fieldName:cfg.fieldName,nonce:cfg.nonce,data:data})
		});
		const json=await res.json();
		if(status){
			if(json.success){status.className="rapid-fe-status ok";status.textContent="Saved \u2713";setTimeout(()=>{status.textContent="";},3000);}
			else{status.className="rapid-fe-status err";status.textContent="Error: "+(json.error||"unknown");}
		}
	}catch(err){
		if(status){status.className="rapid-fe-status err";status.textContent="Network error: "+err.message;}
	}finally{btn.disabled=false;}
});
})();
</script>';
	}
}
