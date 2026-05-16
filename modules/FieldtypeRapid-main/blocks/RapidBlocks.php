<?php namespace ProcessWire;

require_once __DIR__ . '/RapidBlockAbstract.php';

// ── Paragraph ─────────────────────────────────────────────────────────────

class RapidBlockParagraph extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$text    = self::text($block['data']['text'] ?? '');
		$classes = !empty($block['data']['large']) ? ['rapid-paragraph--large'] : [];
		return "<p " . self::attr($block, $classes) . ">$text</p>";
	}
	public static function toText(array $block): string {
		return strip_tags($block['data']['text'] ?? '');
	}
}

// ── Header ────────────────────────────────────────────────────────────────

class RapidBlockHeader extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$level = max(1, min(6, (int)($block['data']['level'] ?? 2)));
		$text  = self::text($block['data']['text'] ?? '');
		// Auto-generate anchor ID from text if not set via tunes
		if (empty($block['tunes']['id'] ?? null)) {
			$block['tunes']['id'] = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower(strip_tags($text))), '-');
		}
		return "<h{$level} " . self::attr($block) . ">$text</h{$level}>";
	}
	public static function toText(array $block): string {
		return strip_tags($block['data']['text'] ?? '');
	}
}

// ── Quote ─────────────────────────────────────────────────────────────────

class RapidBlockQuote extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$text    = self::text($block['data']['text']    ?? '');
		$caption = self::text($block['data']['caption'] ?? '');
		$cap     = $caption ? "<figcaption>$caption</figcaption>" : '';
		return "<blockquote " . self::attr($block) . "><figure><p>$text</p>$cap</figure></blockquote>";
	}
	public static function toText(array $block): string {
		return strip_tags(($block['data']['text'] ?? '') . ' ' . ($block['data']['caption'] ?? ''));
	}
}

// ── NestedList ────────────────────────────────────────────────────────────

class RapidBlockNestedList extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$tag   = ($block['data']['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
		$inner = self::items((array)($block['data']['items'] ?? []), $tag);
		return "<div " . self::attr($block) . ">$inner</div>";
	}
	public static function toText(array $block): string {
		return self::itemsText((array)($block['data']['items'] ?? []));
	}
	private static function items(array $items, string $tag): string {
		if (!$items) return '';
		$h = "<$tag>";
		foreach ($items as $i) {
			$children = !empty($i['items']) ? self::items($i['items'], $tag) : '';
			$h .= '<li>' . self::text($i['content'] ?? '') . $children . '</li>';
		}
		return $h . "</$tag>";
	}
	private static function itemsText(array $items): string {
		$p = [];
		foreach ($items as $i) {
			$p[] = strip_tags($i['content'] ?? '');
			if (!empty($i['items'])) $p[] = self::itemsText($i['items']);
		}
		return implode(' ', array_filter($p));
	}
}

// ── Table ─────────────────────────────────────────────────────────────────

class RapidBlockTable extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$rows = (array)($block['data']['content'] ?? []);
		$h    = "<div " . self::attr($block) . "><table class='rapid-table'>";
		if (!empty($block['data']['withHeadings']) && $rows) {
			$head = array_shift($rows);
			$h   .= '<thead><tr>' . implode('', array_map(fn($c) => '<th>' . self::text((string)$c) . '</th>', $head)) . '</tr></thead>';
		}
		$h .= '<tbody>';
		foreach ($rows as $row) {
			$h .= '<tr>' . implode('', array_map(fn($c) => '<td>' . self::text((string)$c) . '</td>', $row)) . '</tr>';
		}
		return $h . '</tbody></table></div>';
	}
	public static function toText(array $block): string {
		$p = [];
		foreach ((array)($block['data']['content'] ?? []) as $row) {
			foreach ($row as $c) $p[] = strip_tags((string)$c);
		}
		return implode(' ', array_filter($p));
	}
}

// ── Code ──────────────────────────────────────────────────────────────────

class RapidBlockCode extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$code  = htmlspecialchars($block['data']['code'] ?? '');
		$lang  = self::plain($block['data']['language'] ?? 'none');
		$lines = !empty($block['data']['lineNumbers']) ? ' class="line-numbers"' : '';
		return "<div " . self::attr($block) . "><pre$lines><code class=\"language-$lang\">$code</code></pre></div>";
	}
	public static function toText(array $block): string {
		return $block['data']['code'] ?? '';
	}
}

// ── Image ─────────────────────────────────────────────────────────────────

class RapidBlockImage extends RapidBlockAbstract {

	public static function render(array $block, ?Page $page): string {
		$data = $block['data'];

		// @editorjs/image stores URL in data.file.url (upload response) or data.url (after reload)
		$url = $data['url'] ?? $data['file']['url'] ?? '';
		if (empty($url)) return '';

		// Normalise: always keep url at top level for processImage()
		$data['url'] = $url;

		$alt     = self::plain($data['alt']     ?? '');
		$cap     = !empty($data['caption']) ? '<figcaption>' . self::text($data['caption']) . '</figcaption>' : '';
		$classes = array_filter([
			!empty($data['withBorder'])     ? 'rapid-image--border'    : '',
			!empty($data['withBackground']) ? 'rapid-image--bg'        : '',
			!empty($data['stretched'])      ? 'rapid-image--stretched' : '',
		]);

		// Resize: per-block settings fall back to field defaults
		$resize   = $data['_resize'] ?? $block['tunes']['rapidResize'] ?? [];
		$defaults = $block['_imgDefaults'] ?? [];
		$width  = (int)($resize['width']  ?? 0) ?: (int)($defaults['width']  ?? 0);
		$height = (int)($resize['height'] ?? 0) ?: (int)($defaults['height'] ?? 0);
		$webp   = !empty($resize['webp']) || !empty($defaults['webp']);
		$crop   = !empty($resize['crop']) || !empty($defaults['crop']);

		$src = self::processImage($data['url'], $page, $width, $height, $webp, $crop);

		// Always output width/height attributes for CLS prevention
		$widthAttr  = $width  ? " width=\"$width\""  : '';
		$heightAttr = $height ? " height=\"$height\"" : '';

		$img = "<img src=\"$src\" alt=\"$alt\" loading=\"lazy\"$widthAttr$heightAttr>";

		if (!empty($data['link'])) {
			$target = !empty($data['openInNewTab']) ? ' target="_blank" rel="noopener"' : '';
			$img = "<a href=\"" . htmlspecialchars($data['link']) . "\"$target>$img</a>";
		}

		return "<figure " . self::attr($block, $classes) . ">$img$cap</figure>";
	}

	/**
	 * Resize and/or convert image via PW Pageimage::size().
	 * Falls back to original URL if page not available or file not found.
	 */
	protected static function processImage(
		string $url,
		?Page $page,
		int $width,
		int $height,
		bool $webp,
		bool $crop
	): string {
		// No processing needed
		if (!$width && !$height && !$webp) {
			return self::resolveUrl($url, $page);
		}

		// WebP-only without resize is not meaningful — skip processing
		if ($webp && !$width && !$height) {
			return self::resolveUrl($url, $page);
		}

		// Need a page with files to use Pageimage
		if (!$page || !$page->id) {
			return self::resolveUrl($url, $page);
		}

		// Strip domain from URL for findPageimage (it uses basename internally)
		$localUrl  = preg_replace('#^https?://[^/]+#', '', $url);
		$pageimage = self::findPageimage($localUrl, $page);
		if (!$pageimage) {
			return self::resolveUrl($url, $page);
		}

		$options = [];

		if ($crop && $width && $height) {
			$options['cropping'] = true;
		} else {
			$options['cropping'] = false;
		}

		if ($webp) {
			$options['webpAdd'] = true;
		}

		try {
			$sized = $pageimage->size(
				$width  ?: 0,
				$height ?: 0,
				$options
			);

			// Verify sized file actually exists on disk
			if (!$sized || !file_exists($sized->filename)) {
				return self::resolveUrl($url, $page);
			}

			// If webp requested, prefer the .webp version
			if ($webp) {
				$webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $sized->filename);
				if (file_exists($webpPath)) {
					$webpUrl  = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $sized->url);
					return htmlspecialchars($webpUrl);
				}
			}

			return htmlspecialchars($sized->url);
		} catch (\Throwable $e) {
			return self::resolveUrl($url, $page);
		}
	}

	/**
	 * Find a Pageimage object by URL within a page's files directory.
	 *
	 * Strategy:
	 * 1. Extract filename from URL
	 * 2. Verify file exists on disk
	 * 3. Search page's Pageimages fields for a match
	 * 4. Fall back to constructing a temporary Pageimages collection
	 */
	private static function findPageimage(string $url, Page $page): ?\ProcessWire\Pageimage {
		$filesPath = rtrim($page->filesManager()->path(), '/') . '/';
		$filename  = basename(parse_url($url, PHP_URL_PATH) ?: $url);

		if (!$filename) return null;

		$filepath = $filesPath . $filename;
		if (!file_exists($filepath)) return null;

		// Search through all Pageimages fields on this page
		foreach ($page->fields as $field) {
			if (!$field->type instanceof \ProcessWire\FieldtypeImage) continue;
			$images = $page->get($field->name);
			if (!$images instanceof \ProcessWire\Pageimages) continue;
			$found = $images->get($filename);
			if ($found instanceof \ProcessWire\Pageimage) return $found;
		}

		// File exists on disk but not in any Images field —
		// uploaded via Rapid upload endpoint. Create a temporary Pageimage.
		try {
			$pageimages = new \ProcessWire\Pageimages($page);
			$pageimage  = new \ProcessWire\Pageimage($pageimages, $filepath);
			// Pageimage uses $this->filename to locate the original.
			// Without setting pagefiles it won't resolve the path correctly —
			// force the basepath so size() can find the file.
			if (!$pageimage->filename || !file_exists($pageimage->filename)) {
				return null;
			}
			return $pageimage;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function toText(array $block): string {
		return trim(($block['data']['alt'] ?? '') . ' ' . ($block['data']['caption'] ?? ''));
	}
}

// ── Gallery ───────────────────────────────────────────────────────────────

class RapidBlockGallery extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$files = (array)($block['data']['files'] ?? []);
		if (!$files) return '';
		$colW  = (int)($block['data']['columnWidthPx'] ?? 250);
		$gap   = (int)($block['data']['gapPx']         ?? 4);
		$css   = "style=\"--rapid-col-width:{$colW}px;--rapid-gap:{$gap}px\"";
		$items = '';
		foreach ($files as $file) {
			$src    = self::resolveUrl((string)$file, $page);
			$items .= "<a href=\"$src\" class=\"rapid-gallery__item\"><img src=\"$src\" loading=\"lazy\" alt=\"\"></a>";
		}
		return "<div " . self::attr($block, ['rapid-gallery']) . " $css>$items</div>";
	}
}

// ── ImageSlideshow ────────────────────────────────────────────────────────

class RapidBlockImageSlideshow extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$files = (array)($block['data']['files'] ?? []);
		if (!$files) return '';
		$d        = $block['data'];
		$settings = htmlspecialchars(json_encode([
			'loop'         => $d['loop']         ?? true,
			'autoplay'     => $d['autoplay']      ?? false,
			'effect'       => $d['effect']        ?? 'slide',
			'delay'        => $d['delay']         ?? 3000,
			'slidesPerView'=> $d['slidesPerView'] ?? 1,
			'gap'          => $d['gapPx']         ?? 0,
		], JSON_UNESCAPED_SLASHES));
		$slides = '';
		foreach ($files as $file) {
			$src     = self::resolveUrl((string)$file, $page);
			$slides .= "<div class=\"swiper-slide rapid-slideshow__slide\"><img src=\"$src\" loading=\"lazy\" alt=\"\"></div>";
		}
		$id   = 'rs-' . substr(md5(uniqid()), 0, 8);
		$attr = self::attr($block, ['rapid-slideshow', 'swiper']);
		$init = "<script>
(function(){
	var el=document.getElementById('$id');
	if(!el||typeof Swiper==='undefined')return;
	var cfg=JSON.parse(el.dataset.rapidSlideshow);
	new Swiper(el,{
		loop:cfg.loop,effect:cfg.effect,
		slidesPerView:cfg.slidesPerView||1,
		spaceBetween:cfg.gap||0,
		autoplay:cfg.autoplay?{delay:cfg.delay}:false,
		navigation:{nextEl:'#$id .swiper-button-next',prevEl:'#$id .swiper-button-prev'},
		pagination:{el:'#$id .swiper-pagination',clickable:true},
	});
})();
</script>";
		return "<div id='$id' $attr data-rapid-slideshow='$settings'>"
		     . "<div class=\"swiper-wrapper\">$slides</div>"
		     . "<div class=\"swiper-button-prev\"></div>"
		     . "<div class=\"swiper-button-next\"></div>"
		     . "<div class=\"swiper-pagination\"></div>"
		     . "</div>"
		     . $init;
	}
}

// ── Embed ─────────────────────────────────────────────────────────────────

class RapidBlockEmbed extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$data    = $block['data'];
		$embed   = $data['embed'] ?? '';
		if (!$embed) return '';
		// Normalize YouTube URLs to nocookie domain to avoid Error 153
		$embed   = str_replace('https://www.youtube.com/embed/', 'https://www.youtube-nocookie.com/embed/', $embed);
		$service = self::plain($data['service'] ?? '');
		$caption = self::text($data['caption'] ?? '');
		$cap     = $caption ? "<figcaption>$caption</figcaption>" : '';
		$ratio   = self::ratio((int)($data['width'] ?? 16), (int)($data['height'] ?? 9));
		$src  = htmlspecialchars($embed);
		$attr = self::attr($block, ['rapid-embed', "rapid-embed--$service"]);
		// referrerpolicy required for YouTube Error 153 fix
		$allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen';
		return "<figure $attr><div class=\"rapid-embed__ratio\" style=\"aspect-ratio:$ratio\">"
		     . "<iframe src=\"$src\" frameborder=\"0\" allow=\"$allow\" allowfullscreen loading=\"lazy\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe>"
		     . "</div>$cap</figure>";
	}
	public static function toText(array $block): string {
		return strip_tags($block['data']['caption'] ?? '');
	}
	private static function ratio(int $w, int $h): string {
		if (!$w || !$h) return '16/9';
		$g = self::gcd($w, $h);
		return ($w/$g) . '/' . ($h/$g);
	}
	private static function gcd(int $a, int $b): int {
		return $b === 0 ? $a : self::gcd($b, $a % $b);
	}
}

// ── LayoutSection ─────────────────────────────────────────────────────────

class RapidBlockLayoutSection extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$data  = $block['data'];
		$inner = '';
		if (!empty($data['content']['blocks'])) {
			// Pass same options to nested renderer (allowedBlocks, etc.)
			$opts  = !empty($data['allowedBlocks']) ? ['allowedBlocks' => $data['allowedBlocks']] : [];
			$inner = (new RapidRenderer($opts))->renderData($data['content'], $page);
		}

		$styleKeys = ['backgroundColor','backgroundBlendMode','borderWidth','borderRadius','borderStyle','paddingTop','paddingBottom'];
		$ds      = (array)($data['style'] ?? []);
		$styles  = [];
		$classes = ['rapid-layout-section'];

		foreach ($styleKeys as $k) {
			if (!empty($ds[$k])) $styles[$k] = preg_replace('/[<>]/', '', $ds[$k]);
		}
		if (!empty($data['gap']))          $styles['--rapid-flex-gap']            = preg_replace('/[<>]/', '', $data['gap']);
		if (!empty($data['minBlockWidth'])) $styles['--rapid-flex-min-block-width'] = preg_replace('/[<>]/', '', $data['minBlockWidth']);
		if (!empty($ds['backgroundImage'])) $styles['backgroundImage'] = "url('" . preg_replace('/[<>\'"()]/', '', $ds['backgroundImage']) . "')";
		if (!empty($ds['overflowHidden']))  $styles['overflow']  = 'hidden';
		if (!empty($ds['matchRowHeight']))  $styles['height']    = '100%';
		if (!empty($ds['shadow']))          $styles['boxShadow'] = 'var(--rapid-section-shadow,0 2px 8px rgba(0,0,0,.15))';
		if (!empty($ds['color']))           $styles['--rapid-section-color']        = preg_replace('/[<>]/', '', $ds['color']);
		if (!empty($ds['borderColor']))     $styles['--rapid-section-border-color'] = preg_replace('/[<>]/', '', $ds['borderColor']);
		if (!empty($ds['card']))            $classes[] = 'rapid-card';
		if (!empty($data['justify']))       $classes[] = 'rapid-justify-' . preg_replace('/[^a-z\-]/', '', $data['justify']);
		if (!empty($data['align']))         $classes[] = 'rapid-align-'   . preg_replace('/[^a-z\-]/', '', $data['align']);

		$attr = RapidAttr::render($block['tunes'] ?? [], $classes, $styles);
		return "<section><div $attr>$inner</div></section>";
	}
	public static function toText(array $block): string {
		return RapidRenderer::blocksToText($block['data']['content']['blocks'] ?? []);
	}
}

// ── Code ──────────────────────────────────────────────────────────────────
// (already defined above as RapidBlockCode)

// ── Delimiter ─────────────────────────────────────────────────────────────

class RapidBlockDelimiter extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$attr = self::attr($block);
		return "<hr $attr>";
	}
}

// ── Warning ───────────────────────────────────────────────────────────────

class RapidBlockWarning extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$title   = self::plain($block['data']['title']   ?? '');
		$message = self::text($block['data']['message']  ?? '');
		$attr    = self::attr($block, ['rapid-warning']);
		$head    = $title ? "<p class=\"rapid-warning__title\">$title</p>" : '';
		return "<div $attr>$head<p class=\"rapid-warning__message\">$message</p></div>";
	}
	public static function toText(array $block): string {
		return strip_tags(($block['data']['title'] ?? '') . ' ' . ($block['data']['message'] ?? ''));
	}
}

// ── Checklist ─────────────────────────────────────────────────────────────

class RapidBlockChecklist extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$items = (array)($block['data']['items'] ?? []);
		$attr  = self::attr($block, ['rapid-checklist']);
		$html  = "<div $attr>";
		foreach ($items as $item) {
			$checked = !empty($item['checked']) ? ' checked' : '';
			$text    = self::text($item['text'] ?? '');
			$html   .= "<label class=\"rapid-checklist__item\">"
			         . "<input type=\"checkbox\"$checked disabled>"
			         . "<span>$text</span>"
			         . "</label>";
		}
		return $html . "</div>";
	}
	public static function toText(array $block): string {
		$parts = [];
		foreach ((array)($block['data']['items'] ?? []) as $item) {
			$parts[] = strip_tags($item['text'] ?? '');
		}
		return implode(' ', array_filter($parts));
	}
}

// ── Raw ───────────────────────────────────────────────────────────────────

class RapidBlockRaw extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		// Raw HTML — output as-is, no escaping
		return $block['data']['html'] ?? '';
	}
}

// ── Attaches ─────────────────────────────────────────────────────────────

class RapidBlockAttaches extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$file = $block['data']['file'] ?? [];
		$url  = self::resolveUrl($file['url'] ?? '', $page);
		$name = self::plain($file['name']            ?? 'Download');
		$ext  = self::plain($file['extension']       ?? '');
		$size = self::formatSize((int)($file['size'] ?? 0));
		if (!$url) return '';
		$attr = self::attr($block, ['rapid-attaches']);
		$badge = $ext ? "<span class=\"rapid-attaches__ext\">$ext</span>" : '';
		$meta  = $size ? "<span class=\"rapid-attaches__size\">$size</span>" : '';
		return "<div $attr>"
		     . "<a href=\"$url\" class=\"rapid-attaches__link\" download>"
		     . $badge
		     . "<span class=\"rapid-attaches__name\">$name</span>"
		     . $meta
		     . "</a></div>";
	}
	public static function toText(array $block): string {
		return $block['data']['file']['name'] ?? '';
	}
	private static function formatSize(int $bytes): string {
		if ($bytes <= 0) return '';
		if ($bytes < 1024) return $bytes . ' B';
		if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
		return round($bytes / 1048576, 1) . ' MB';
	}
}

// ── Alert ─────────────────────────────────────────────────────────────────

class RapidBlockAlert extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$data    = $block['data'];
		$message = self::text($data['message'] ?? '');
		$type    = preg_replace('/[^a-z]/', '', strtolower($data['type'] ?? 'info'));
		if (!$message) return '';
		$attr = self::attr($block, ['rapid-alert', "rapid-alert--$type"]);
		return "<div $attr>$message</div>";
	}
	public static function toText(array $block): string {
		return strip_tags($block['data']['message'] ?? '');
	}
}

// ── LinkTool ──────────────────────────────────────────────────────────────

class RapidBlockLinkTool extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page): string {
		$data  = $block['data'];
		$link  = $data['link']  ?? '';
		$meta  = $data['meta']  ?? [];
		if (!$link) return '';

		$url   = htmlspecialchars($link);
		$title = self::plain($meta['title']       ?? $link);
		$desc  = self::plain($meta['description'] ?? '');
		$img   = htmlspecialchars($meta['image']['url'] ?? '');
		$host  = htmlspecialchars(parse_url($link, PHP_URL_HOST) ?: $link);

		$thumb = $img ? "<div class=\"rapid-link__image\"><img src=\"$img\" alt=\"\" loading=\"lazy\"></div>" : '';
		$ddesc = $desc ? "<p class=\"rapid-link__desc\">$desc</p>" : '';

		$attr = self::attr($block, ['rapid-link']);
		return "<div $attr>"
		     . "<a href=\"$url\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"rapid-link__anchor\">"
		     . $thumb
		     . "<div class=\"rapid-link__body\">"
		     . "<p class=\"rapid-link__title\">$title</p>"
		     . $ddesc
		     . "<p class=\"rapid-link__host\">$host</p>"
		     . "</div>"
		     . "</a>"
		     . "</div>";
	}
	public static function toText(array $block): string {
		return strip_tags($block['data']['meta']['title'] ?? $block['data']['link'] ?? '');
	}
}

// ── Columns ───────────────────────────────────────────────────────────────

class RapidBlockColumns extends RapidBlockAbstract {
	public static function render(array $block, ?Page $page = null, ?Field $field = null): string {
		$cols = $block['data']['cols'] ?? [];
		if (empty($cols)) return '';

		$count  = count($cols);
		$cls    = "rapid-columns rapid-columns--{$count}";
		$inner  = '';

		foreach ($cols as $colData) {
			$blocks  = $colData['blocks'] ?? [];
			$colHtml = '';

			if ($blocks) {
				// Recursively render each column's blocks using RapidRenderer
				$colHtml = (new \ProcessWire\RapidRenderer())->renderData(
					['blocks' => $blocks, 'time' => 0, 'version' => ''],
					$page,
					$field
				);
			}

			$inner .= "<div class='rapid-col'>$colHtml</div>";
		}

		return "<div class='" . htmlspecialchars($cls) . "'>$inner</div>";
	}

	public static function toText(array $block): string {
		$cols  = $block['data']['cols'] ?? [];
		$parts = [];
		foreach ($cols as $col) {
			foreach ($col['blocks'] ?? [] as $b) {
				$type  = $b['type'] ?? '';
				$cls   = 'ProcessWire\\RapidBlock' . ucfirst($type);
				if (class_exists($cls) && method_exists($cls, 'toText')) {
					$parts[] = $cls::toText($b);
				}
			}
		}
		return implode(' ', array_filter($parts));
	}
}
