<?php namespace ProcessWire;

/**
 * RapidBlockAbstract — base class for all EditorJS block renderers.
 */
abstract class RapidBlockAbstract {

	abstract public static function render(array $block, ?Page $page): string;

	public static function toText(array $block): string { return ''; }

	protected static function text(string $raw): string {
		return htmlspecialchars_decode($raw);
	}

	protected static function plain(string $raw): string {
		return htmlspecialchars(strip_tags($raw));
	}

	protected static function attr(array $block, array $classes = [], array $styles = []): string {
		return RapidAttr::render($block['tunes'] ?? [], $classes, $styles, $block['_fwClasses'] ?? []);
	}

	/**
	 * Resolve a file URL for use in HTML output.
	 *
	 * Always returns root-relative URLs so they work regardless of domain
	 * (lqrs.com, lqrs.pw, localhost — same result).
	 *
	 * Handles three input formats:
	 *   - https://example.com/path/file.jpg  → as-is (external URL)
	 *   - /site/assets/files/123/file.jpg    → root-relative, use as-is
	 *   - file.jpg                           → prepend page filesManager url
	 */
	protected static function resolveUrl(string $url, ?Page $page): string {
		if (empty($url)) return '';

		// Already an absolute external URL — leave unchanged
		if (preg_match('#^https?://#', $url)) {
			// Strip domain to make root-relative so it works on any domain alias
			$parsed = parse_url($url);
			if (!empty($parsed['path'])) {
				return htmlspecialchars($parsed['path'] . (!empty($parsed['query']) ? '?' . $parsed['query'] : ''));
			}
			return htmlspecialchars($url);
		}

		// Already root-relative — use as-is
		if (str_starts_with($url, '/')) {
			return htmlspecialchars($url);
		}

		// Bare filename — prepend page files URL
		if ($page && $page->id) {
			$filesUrl = rtrim($page->filesManager()->url(), '/') . '/';
			return htmlspecialchars($filesUrl . $url);
		}

		return htmlspecialchars($url);
	}
}
