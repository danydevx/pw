<?php namespace ProcessWire;

class ProcessHelpersImages {

    protected static function resolveSource($source, array &$options): ?array {
        if($source instanceof Pageimage) {
            if(empty($options['width'])) $options['width'] = (int) $source->width;
            if(empty($options['height'])) $options['height'] = (int) $source->height;
            if(empty($options['alt'])) {
                $alt = (string) $source->description;
                if($alt === '' && $source->page instanceof Page) $alt = (string) $source->page->title;
                $options['alt'] = $alt;
            }

            return [
                'url' => (string) $source->url,
                'path' => (string) $source->filename,
            ];
        }

        if(is_string($source)) {
            $input = trim($source);
            if($input === '') return null;
            return ['url' => '', 'path' => '', 'input' => $input];
        }

        return null;
    }

    protected static function isAbsoluteSource(string $value): bool {
        return (bool) preg_match('#^(https?:)?//#i', $value) || strpos($value, '/') === 0;
    }

    protected static function urlToPath(string $url): string {
        $config = wire('config');
        $rootUrl = rtrim((string) $config->urls->root, '/');
        $rootPath = rtrim((string) $config->paths->root, '/');

        if($rootUrl && strpos($url, $rootUrl . '/') === 0) {
            $rel = substr($url, strlen($rootUrl) + 1);
            return $rootPath . '/' . ltrim((string) $rel, '/');
        }

        if(strpos($url, '/') === 0) {
            return $rootPath . $url;
        }

        return '';
    }

    public static function getStaticImageUrl(string $basename, array $options = []): string {
        $config = wire('config');
        $urls = wire('urls');

        $fallback = strtolower((string) ($options['fallback'] ?? 'jpg'));
        if(!in_array($fallback, ['jpg', 'jpeg', 'png'], true)) $fallback = 'jpg';

        $imagesUrlKey = (string) ($options['imagesUrlKey'] ?? 'images');
        $imagesPathKey = (string) ($options['imagesPathKey'] ?? 'images');
        $imagesUrl = (string) $urls->get($imagesUrlKey);
        $imagesPath = (string) $config->paths->get($imagesPathKey);
        if($imagesUrl === '' || $imagesPath === '') return '';

        $safeBase = trim($basename);
        if($safeBase === '') return '';

        $fallbackPath = rtrim($imagesPath, '/') . '/' . $safeBase . '.' . $fallback;
        $fallbackUrl = rtrim($imagesUrl, '/') . '/' . $safeBase . '.' . $fallback;
        $webpPath = rtrim($imagesPath, '/') . '/' . $safeBase . '.webp';
        $webpUrl = rtrim($imagesUrl, '/') . '/' . $safeBase . '.webp';

        if(is_file($webpPath)) return $webpUrl;
        if(is_file($fallbackPath)) return $fallbackUrl;

        if($config->debug) {
            return '';
        }
        return '';
    }

    public static function renderStaticPicture($source, array $options = []): string {
        $config = wire('config');
        $urls = wire('urls');
        $fallback = strtolower((string) ($options['fallback'] ?? 'jpg'));
        if(!in_array($fallback, ['jpg', 'jpeg', 'png'], true)) $fallback = 'jpg';

        $imagesUrlKey = (string) ($options['imagesUrlKey'] ?? 'images');
        $imagesPathKey = (string) ($options['imagesPathKey'] ?? 'images');
        $imagesUrl = (string) $urls->get($imagesUrlKey);
        $imagesPath = (string) $config->paths->get($imagesPathKey);

        $resolved = self::resolveSource($source, $options);
        if(!$resolved) return '';

        $fallbackUrl = '';
        $fallbackPath = '';

        if(!empty($resolved['url']) && !empty($resolved['path'])) {
            $fallbackUrl = (string) $resolved['url'];
            $fallbackPath = (string) $resolved['path'];
        } else {
            $input = (string) ($resolved['input'] ?? '');
            if(self::isAbsoluteSource($input)) {
                $fallbackUrl = $input;
                $fallbackPath = self::urlToPath($input);
            } else {
                if($imagesUrl === '' || $imagesPath === '') return '';
                $fallbackUrl = rtrim($imagesUrl, '/') . '/' . $input . '.' . $fallback;
                $fallbackPath = rtrim($imagesPath, '/') . '/' . $input . '.' . $fallback;
            }
        }

        if($fallbackPath !== '' && !is_file($fallbackPath)) {
            if($config->debug) {
                return '<!-- ProcessHelpersImages: fallback not found ' . htmlspecialchars($fallbackPath, ENT_QUOTES, 'UTF-8') . ' -->';
            }
            return '';
        }

        $webpUrl = preg_replace('/\.(jpe?g|png)$/i', '.webp', $fallbackUrl) ?: '';
        $webpPath = $fallbackPath !== '' ? (preg_replace('/\.(jpe?g|png)$/i', '.webp', $fallbackPath) ?: '') : '';

        $attrs = [
            'src' => $fallbackUrl,
            'alt' => (string) ($options['alt'] ?? ''),
            'loading' => (string) ($options['loading'] ?? 'lazy'),
            'decoding' => (string) ($options['decoding'] ?? 'async'),
        ];

        if(!empty($options['class'])) $attrs['class'] = (string) $options['class'];
        if(!empty($options['style'])) $attrs['style'] = (string) $options['style'];
        if(!empty($options['width'])) $attrs['width'] = (string) $options['width'];
        if(!empty($options['height'])) $attrs['height'] = (string) $options['height'];

        $imgAttrs = self::renderAttributes($attrs);
        $source = '';
        if($webpPath !== '' && is_file($webpPath) && $webpUrl !== '') {
            $source = '<source srcset="' . htmlspecialchars($webpUrl, ENT_QUOTES, 'UTF-8') . '" type="image/webp">';
        }

        return '<picture>' . $source . '<img ' . $imgAttrs . '></picture>';
    }

    protected static function renderAttributes(array $attrs): string {
        $parts = [];
        foreach($attrs as $name => $value) {
            if($value === null) continue;
            $value = trim((string) $value);
            if($value === '') continue;
            $parts[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return implode(' ', $parts);
    }
}
