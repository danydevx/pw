<?php namespace ProcessWire;

require_once __DIR__ . '/RapidAttr.php';
require_once __DIR__ . '/RapidRenderer.php';
require_once __DIR__ . '/RapidValue.php';

/**
 * ProcessFieldtypeRapid
 *
 * Image and file upload API endpoints for the Rapid block editor.
 *
 * POST {adminUrl}/setup/rapid-upload/upload/ — image upload (@editorjs/image)
 * POST {adminUrl}/setup/rapid-upload/attach/ — file upload (@editorjs/attaches)
 */
class ProcessFieldtypeRapid extends Process {

	const PAGE_NAME = 'rapid-upload';

	public static function getModuleInfo(): array {
		return [
			'title'      => 'Rapid Upload',
			'version'    => 102,
			'summary'    => 'Image and file upload API for the Rapid block editor.',
			'author'     => 'Maxim Semenov',
			'href'       => 'https://github.com/mxmsmnv/Rapid',
			'icon'       => 'bolt',
			'requires'   => 'FieldtypeRapid',
			'permission' => 'page-edit',
		];
	}

	public function ___execute(): string { return ''; }

	// ── Install / Uninstall ─────────────────────────────────────────────────

	public function ___install(): void {
		$pages  = $this->wire('pages');
		$admin  = $pages->get($this->wire('config')->adminRootPageID);
		$setup  = $pages->get('name=setup, parent=' . $admin->id . ', include=all');
		$parent = $setup->id ? $setup : $admin;

		$exists = $pages->get('name=' . self::PAGE_NAME . ', parent=' . $parent->id . ', include=all');
		if (!$exists->id) {
			$p           = new Page();
			$p->template = 'admin';
			$p->parent   = $parent;
			$p->name     = self::PAGE_NAME;
			$p->title    = 'Rapid Upload';
			$p->process  = $this;
			$p->addStatus(Page::statusHidden);
			$p->save();
		} elseif (!$exists->isHidden()) {
			// Ensure page stays hidden even if it was made visible
			$exists->addStatus(Page::statusHidden);
			$exists->save();
		}
	}

	public function ___uninstall(): void {
		$page = $this->wire('pages')->get('name=' . self::PAGE_NAME . ', include=all');
		if ($page->id) $this->wire('pages')->delete($page);
	}

	// ── Upload: images ────────────────────────────────────────────────────

	/**
	 * POST {adminUrl}/setup/rapid-upload/upload/
	 * Used by @editorjs/image.
	 * Returns: { "success": 1, "file": { "url": "...", "name": "..." } }
	 */
	public function ___executeUpload(): string {
		ob_start();
		header('Content-Type: application/json');

		if ($this->wire('input')->requestMethod() !== 'POST') {
			ob_end_clean();
			return $this->jsonError('Method not allowed');
		}

		$user = $this->wire('user');
		if (!$user->isLoggedin() || !$user->hasPermission('page-edit')) {
			return $this->jsonError('Forbidden');
		}

		$pageId = (int) $this->wire('input')->post('pageId');
		$page   = $pageId ? $this->wire('pages')->get($pageId) : null;

		if (!$page || !$page->id) return $this->jsonError('Invalid page');
		if (!$page->editable())   return $this->jsonError('Page not editable');
		if (empty($_FILES['image'])) return $this->jsonError('No file received');

		$upload = $_FILES['image'];

		if ($upload['error'] !== UPLOAD_ERR_OK) {
			return $this->jsonError('Upload error: ' . $upload['error']);
		}

		$finfo    = new \finfo(FILEINFO_MIME_TYPE);
		$mimeType = $finfo->file($upload['tmp_name']);

		// Get allowed types from field config (fallback to safe defaults)
		$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		if ($page && $page->id) {
			foreach ($page->template->fieldgroup as $f) {
				if ($f->type instanceof FieldtypeRapid) {
					$configured = (array)($f->get('allowedImageTypes') ?: []);
					if ($configured) $allowedMimes = $configured;
					break;
				}
			}
		}
		// SVG always requires extra sanitization — skip unless explicitly allowed
		if ($mimeType === 'image/svg+xml' && !in_array('image/svg+xml', $allowedMimes, true)) {
			return $this->jsonError('SVG not allowed');
		}
		if (!in_array($mimeType, $allowedMimes, true)) {
			return $this->jsonError('File type not allowed: ' . $mimeType);
		}

		$mimeToExt = ['image/jpeg' => ['jpg','jpeg'], 'image/png' => ['png'], 'image/gif' => ['gif'], 'image/webp' => ['webp'], 'image/svg+xml' => ['svg']];
		$validExts = $mimeToExt[$mimeType] ?? [];
		$ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
		if ($validExts && !in_array($ext, $validExts, true)) {
			return $this->jsonError('Extension does not match file type');
		}

		$maxBytes = $this->getMaxUploadBytes($page);
		if ($upload['size'] > $maxBytes) {
			return $this->jsonError('File too large (max ' . round($maxBytes / 1048576) . 'MB)');
		}

		$fm       = $page->filesManager();
		$fm->path();
		$safeName = $this->uniqueFilename($fm->path(), $this->sanitizeFilename($upload['name']));

		if (!move_uploaded_file($upload['tmp_name'], $fm->path() . $safeName)) {
			return $this->jsonError('Failed to save file');
		}

		ob_end_clean();
		return json_encode([
			'success' => 1,
			'file'    => ['url' => $this->filesHttpUrl($fm) . $safeName, 'name' => $safeName],
		]);
	}

	// ── Upload: files ─────────────────────────────────────────────────────

	/**
	 * POST {adminUrl}/setup/rapid-upload/attach/
	 * Used by @editorjs/attaches.
	 * Returns: { "success": 1, "file": { "url": "...", "name": "...", "size": N, "extension": "..." } }
	 */
	public function ___executeAttach(): string {
		ob_start();
		header('Content-Type: application/json');

		if ($this->wire('input')->requestMethod() !== 'POST') {
			ob_end_clean();
			return $this->jsonError('Method not allowed');
		}

		$user = $this->wire('user');
		if (!$user->isLoggedin() || !$user->hasPermission('page-edit')) {
			return $this->jsonError('Forbidden');
		}

		// pageId comes as GET param for attaches (AttachesTool doesn't support additionalRequestData)
		$input  = $this->wire('input');
		$pageId = (int)($input->post('pageId') ?: $input->get('pageId'));
		$page   = $pageId ? $this->wire('pages')->get($pageId) : null;

		if (!$page || !$page->id || !$page->editable()) {
			return $this->jsonError('Invalid page');
		}

		if (empty($_FILES['file'])) return $this->jsonError('No file received');

		$upload = $_FILES['file'];

		if ($upload['error'] !== UPLOAD_ERR_OK) {
			return $this->jsonError('Upload error: ' . $upload['error']);
		}

		$maxBytes = $this->getMaxUploadBytes($page, 50);
		if ($upload['size'] > $maxBytes) {
			return $this->jsonError('File too large (max ' . round($maxBytes / 1048576) . 'MB)');
		}

		// Check allowed file extensions from field config
		$ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
		$allowedExts = [];
		if ($page && $page->id) {
			foreach ($page->template->fieldgroup as $f) {
				if ($f->type instanceof FieldtypeRapid) {
					$raw = trim((string)($f->get('allowedFileExtensions') ?: ''));
					if ($raw) {
						$allowedExts = array_filter(array_map('trim', explode(',', strtolower($raw))));
					}
					break;
				}
			}
		}
		if ($allowedExts && !in_array($ext, array_values($allowedExts), true)) {
			return $this->jsonError('File type .' . $ext . ' is not allowed');
		}

		$fm       = $page->filesManager();
		$fm->path();
		$safeName = $this->uniqueFilename($fm->path(), $this->sanitizeFilename($upload['name']));

		if (!move_uploaded_file($upload['tmp_name'], $fm->path() . $safeName)) {
			return $this->jsonError('Failed to save file');
		}

		ob_end_clean();
		return json_encode([
			'success' => 1,
			'file'    => [
				'url'       => $this->filesHttpUrl($fm) . $safeName,
				'name'      => $safeName,
				'size'      => $upload['size'],
				'extension' => strtolower(pathinfo($safeName, PATHINFO_EXTENSION)),
			],
		]);
	}



/**
	 * Route: {adminUrl}/setup/rapid-upload/link/
	 * Used by @editorjs/link — fetches OpenGraph metadata for a URL.
	 */
	public function ___executeLink(): string {
		header('Content-Type: application/json');

		$user = $this->wire('user');
		if (!$user->isLoggedin() || !$user->hasPermission('page-edit')) {
			return json_encode(['success' => 0, 'error' => 'Forbidden']);
		}

		$url = (string)$this->wire('input')->get('url');
		if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
			return json_encode(['success' => 0, 'error' => 'Invalid URL']);
		}

		$ctx  = stream_context_create(['http' => [
			'timeout'       => 5,
			'user_agent'    => 'Mozilla/5.0 (compatible; RapidLinkTool/1.0)',
			'ignore_errors' => true,
		]]);
		$html = @file_get_contents($url, false, $ctx);
		if (!$html) return json_encode(['success' => 0, 'error' => 'Could not fetch URL']);

		$title = $desc = $image = '';

		if (preg_match('#<title[^>]*>([^<]+)</title>#i', $html, $m)) {
			$title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
		}
		if (preg_match('#property=.og:title.[^>]+content=.([^"\'<>]+)#i', $html, $m) ||
		    preg_match('#content=.([^"\'<>]+).[^>]+property=.og:title.#i', $html, $m)) {
			$title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
		}
		if (preg_match('#name=.description.[^>]+content=.([^"\'<>]+)#i', $html, $m) ||
		    preg_match('#content=.([^"\'<>]+).[^>]+name=.description.#i', $html, $m)) {
			$desc = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
		}
		if (preg_match('#property=.og:description.[^>]+content=.([^"\'<>]+)#i', $html, $m) ||
		    preg_match('#content=.([^"\'<>]+).[^>]+property=.og:description.#i', $html, $m)) {
			$desc = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
		}
		if (preg_match('#property=.og:image.[^>]+content=.([^"\'<>]+)#i', $html, $m) ||
		    preg_match('#content=.([^"\'<>]+).[^>]+property=.og:image.#i', $html, $m)) {
			$image = trim($m[1]);
		}

		$meta = ['title' => $title ?: (string)parse_url($url, PHP_URL_HOST)];
		if ($desc)  $meta['description'] = $desc;
		if ($image) $meta['image'] = ['url' => $image];

		return json_encode(['success' => 1, 'meta' => $meta]);
	}


	/**
	 * POST {adminUrl}/setup/rapid-upload/save/
	 * Frontend save endpoint for ProcessRapidFrontend.
	 */
	public function ___executeSave(): string {
		ob_start();
		header('Content-Type: application/json');

		if ($this->wire('input')->requestMethod() !== 'POST') {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => 'Method not allowed']);
		}

		$input     = $this->wire('input');
		$pageId    = (int)$input->post('pageId');
		$fieldName = $this->wire('sanitizer')->fieldName((string)$input->post('fieldName'));
		$nonce     = (string)$input->post('nonce');
		$json      = (string)$input->post('data');

		if (!$pageId || !$fieldName || !$json) {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => 'Missing parameters']);
		}

		// Verify nonce
		$secret   = (string)($this->wire('config')->userAuthSalt ?: 'rapid');
		$expected = hash_hmac('sha256', $pageId . ':' . $fieldName, $secret);
		if (!hash_equals($expected, $nonce)) {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => 'Invalid nonce']);
		}

		$page  = $this->wire('pages')->get($pageId);
		$field = $this->wire('fields')->get($fieldName);

		if (!$page || !$page->id || !$field || !($field->type instanceof FieldtypeRapid)) {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => 'Page or field not found']);
		}

		// Permission check
		$user = $this->wire('user');
		$perm = (string)($field->get('frontendPermission') ?: 'page-edit');
		$canEdit = $perm === 'superuser'
			? $user->isSuperuser()
			: ($user->isLoggedin() && ($user->hasPermission($perm, $page) || $user->hasPermission('page-edit', $page)));

		if (!$canEdit) {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => 'Permission denied']);
		}

		$data = json_decode($json, true);
		if (!is_array($data)) {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => 'Invalid JSON']);
		}

		try {
			$page->setOutputFormatting(false);
			$page->set($fieldName, $json);
			$page->save($fieldName);
		} catch (\Throwable $e) {
			ob_end_clean();
			return json_encode(['success' => false, 'error' => $e->getMessage()]);
		}

		ob_end_clean();
		return json_encode(['success' => true]);
	}

	// ── Helpers ───────────────────────────────────────────────────────────

	private function getMaxUploadBytes(?Page $page, int $defaultMB = 10): int {
		$mb = $defaultMB;
		if ($page && $page->id) {
			// Find the Rapid field on the page's template and read its maxUploadSizeMB
			foreach ($page->template->fieldgroup as $field) {
				if ($field->type instanceof FieldtypeRapid) {
					$mb = (int)($field->get('maxUploadSizeMB') ?: $defaultMB);
					break;
				}
			}
		}
		// Never exceed server PHP limits
		$phpMax = min(
			$this->toBytes(ini_get('upload_max_filesize')),
			$this->toBytes(ini_get('post_max_size'))
		);
		return min($mb * 1048576, $phpMax);
	}

	private function toBytes(string $val): int {
		$val  = trim($val);
		$last = strtolower($val[-1] ?? '');
		$num  = (int)$val;
		return match($last) {
			'g' => $num * 1073741824,
			'm' => $num * 1048576,
			'k' => $num * 1024,
			default => $num,
		};
	}

	private function sanitizeFilename(string $name): string {
		$name = basename($name);
		$ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		$base = pathinfo($name, PATHINFO_FILENAME);
		$base = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $base);
		$base = trim($base, '_') ?: 'file';
		return $base . '.' . $ext;
	}

	private function uniqueFilename(string $dir, string $name): string {
		if (!file_exists($dir . $name)) return $name;
		$ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		$base = pathinfo($name, PATHINFO_FILENAME);
		$i    = 1;
		do { $name = $base . '_' . $i++ . '.' . $ext; } while (file_exists($dir . $name));
		return $name;
	}

	private function filesHttpUrl(PagefilesManager $fm): string {
		$config = $this->wire('config');
		$scheme = $config->https ? 'https' : 'http';
		return $scheme . '://' . $config->httpHost . $fm->url();
	}

	private function jsonError(string $msg, int $code = 0): string {
		ob_end_clean();
		return json_encode(['success' => 0, 'error' => $msg, 'code' => $code]);
	}
}
