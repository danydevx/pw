<?php

use ProcessWire\PageArray;
use ProcessWire\Wire;

class HtmlExporter extends \ProcessWire\Wire {

    protected $selector;
    protected $outputPath;
    protected $compressHtml;
    protected $compressCss;
    protected $compressJs;
    protected $siteUrl;
    protected $allowedModuleAssets = [];
    protected $siteRootPath;
    protected $assetsOutputPath;
    protected $copiedAssets = [];

    public function __construct(
        string $selector,
        string $outputPath,
        bool $compressHtml = false,
        bool $compressCss = false,
        bool $compressJs = false,
        string $siteUrl = '',
        array $allowedModuleAssets = []
    ) {
        $this->selector = $selector;
        $this->outputPath = rtrim($outputPath, '/') . '/';
        $this->compressHtml = $compressHtml;
        $this->compressCss = $compressCss;
        $this->compressJs = $compressJs;
        $this->siteUrl = $this->normalizeSiteUrl($siteUrl);
        $this->allowedModuleAssets = $this->normalizeAllowedModuleAssets($allowedModuleAssets);
        $this->siteRootPath = rtrim($this->config->paths->root, '/') . '/';
        $this->assetsOutputPath = $this->outputPath . 'assets/';

        if(!is_dir($this->assetsOutputPath)) {
            mkdir($this->assetsOutputPath, 0755, true);
        }
    }

    public function run()
    {
        if($this->languages !== null) {
            foreach($this->languages as $language) {
                $this->languages->setLanguage($language);
                $this->convertPages($this->pages->find($this->selector));
            }
            return;
        }

        $this->convertPages($this->pages->find($this->selector));
    }

    protected function convertPages(\ProcessWire\PageArray $pages)
    {
        foreach($pages as $page) {
            $this->convertPage($page);
        }
    }

    protected function convertPage($page)
    {
        if(!$page->template->filenameExists()) {
            return;
        }

        $this->clearRuntimeAssets();

        $filePath = $this->getPageFilePath($page->url);
        $fileDir = dirname($filePath);
        if(!is_dir($fileDir)) {
            mkdir($fileDir, 0755, true);
        }

        $html = $page->render();
        $this->clearRuntimeAssets();

        $html = $this->rewriteHtml($html);
        if($this->compressHtml) {
            $html = $this->compressHtmlOutput($html);
        }

        file_put_contents($filePath, $html);
        chmod($filePath, 0644);

        if($this->config->cli) {
            echo $page->url . "\n";
        }
    }

    protected function clearRuntimeAssets(): void
    {
        $this->config->scripts = $this->newEmptyFilenameArray($this->config->scripts);
        $this->config->styles = $this->newEmptyFilenameArray($this->config->styles);
    }

    protected function newEmptyFilenameArray($current)
    {
        if(is_object($current)) {
            $class = get_class($current);
            if(class_exists($class)) {
                return new $class();
            }
        }

        return new \ProcessWire\FilenameArray();
    }

    protected function getPageFilePath(string $pageUrl): string
    {
        $trimmed = trim($pageUrl, '/');
        if($trimmed === '') {
            return $this->outputPath . 'index.html';
        }

        $parts = explode('/', $trimmed);
        $slug = array_pop($parts);
        if(empty($parts)) {
            return $this->outputPath . $slug . '.html';
        }

        return $this->outputPath . implode('/', $parts) . '/' . $slug . '.html';
    }

    protected function rewriteHtml(string $html): string
    {
        $html = $this->removeOmittedAssetTags($html);

        $html = preg_replace_callback(
            '/\b(src|href|poster)\s*=\s*(?:(["\'])(.*?)\2|([^\s>]+))/i',
            function(array $m) {
                $attr = strtolower($m[1]);
                $url = $m[3] !== '' ? $m[3] : ($m[4] ?? '');

                $rewritten = $this->rewriteAssetUrl($url);
                if($attr === 'href') {
                    $rewritten = $this->rewriteInternalPageUrl($rewritten);
                }

                if(isset($m[2]) && $m[2] !== '') {
                    return $m[1] . '=' . $m[2] . $rewritten . $m[2];
                }

                return $m[1] . '=' . $rewritten;
            },
            $html
        );

        $html = preg_replace_callback(
            '/\bsrcset\s*=\s*(["\'])(.*?)\1/i',
            function(array $m) {
                $items = array_map('trim', explode(',', $m[2]));
                $out = [];
                foreach($items as $item) {
                    if($item === '') continue;
                    $parts = preg_split('/\s+/', $item, 2);
                    $url = $parts[0] ?? '';
                    $desc = $parts[1] ?? '';
                    $newUrl = $this->rewriteAssetUrl($url);
                    $out[] = trim($newUrl . ' ' . $desc);
                }
                return 'srcset=' . $m[1] . implode(', ', $out) . $m[1];
            },
            $html
        );

        return $html;
    }

    protected function removeOmittedAssetTags(string $html): string
    {
        $html = preg_replace_callback('/<script\b[^>]*\bsrc\s*=\s*(?:(["\'])(.*?)\1|([^\s>]+))[^>]*>\s*<\/script>/is', function(array $m) {
            $url = $m[2] !== '' ? $m[2] : ($m[3] ?? '');
            return $this->shouldSkipAssetUrl($url) ? '' : $m[0];
        }, $html);

        $html = preg_replace_callback('/<link\b[^>]*\bhref\s*=\s*(?:(["\'])(.*?)\1|([^\s>]+))[^>]*>/is', function(array $m) {
            $url = $m[2] !== '' ? $m[2] : ($m[3] ?? '');
            return $this->shouldSkipAssetUrl($url) ? '' : $m[0];
        }, $html);

        return $html;
    }

    protected function shouldSkipAssetUrl(string $url): bool
    {
        $url = trim($url);
        if($url === '') return false;

        $normalized = $this->normalizeIncomingUrl($url);
        $parts = parse_url($normalized);
        $path = strtolower($parts['path'] ?? $normalized);

        $adminPatterns = [
            '/wire/modules/jquery/',
            '/wire/templates-admin/',
            '/site/modules/admintheme',
            '/site/assets/cache/admintheme',
        ];

        foreach($adminPatterns as $pattern) {
            if(strpos($path, $pattern) !== false) {
                return true;
            }
        }

        return $this->shouldSkipModuleAssetUrl($normalized);
    }

    protected function shouldSkipModuleAssetUrl(string $url): bool
    {
        $parts = parse_url($url);
        if(!$parts || empty($parts['path'])) {
            return false;
        }

        $path = '/' . ltrim((string) $parts['path'], '/');
        $lowerPath = strtolower($path);
        if(strpos($lowerPath, '/site/modules/') !== 0 && strpos($lowerPath, '/wire/modules/') !== 0) {
            return false;
        }

        foreach($this->allowedModuleAssets as $prefix) {
            if(strpos(strtolower($path), strtolower($prefix)) === 0) {
                return false;
            }
        }

        return true;
    }

    protected function rewriteAssetUrl(string $url): string
    {
        $url = trim($url);
        if($url === '') return $url;

        $url = $this->normalizeIncomingUrl($url);
        if($this->isExternalOrSpecialUrl($url)) {
            return $url;
        }

        $parts = parse_url($url);
        if(!$parts || empty($parts['path'])) {
            return $url;
        }

        $path = $parts['path'];
        if(strpos($path, '/') !== 0) {
            return $url;
        }

        $sourceFile = $this->siteRootPath . ltrim($path, '/');
        if(!is_file($sourceFile)) {
            return $url;
        }

        $this->copyAssetToOutput($sourceFile, $path);

        $newUrl = '/assets/' . ltrim($path, '/');
        if(isset($parts['query'])) $newUrl .= '?' . $parts['query'];
        if(isset($parts['fragment'])) $newUrl .= '#' . $parts['fragment'];

        return $this->applySiteUrl($newUrl);
    }

    protected function rewriteInternalPageUrl(string $url): string
    {
        $original = $url;
        $url = trim($url);
        if($url === '') return $url;

        $url = $this->normalizeIncomingUrl($url);
        if($this->isExternalOrSpecialUrl($url) || strpos($url, '/assets/') === 0) {
            return $url;
        }

        $parts = parse_url($url);
        if(!$parts || empty($parts['path'])) {
            return $url;
        }

        $path = $parts['path'];
        if(strpos($path, '/') !== 0) {
            return $url;
        }

        if($path === '/') {
            $rewritten = '/index.html';
        } else {
            $base = basename($path);
            if(strpos($base, '.') !== false) {
                return $url;
            }

            $page = $this->pages->get($path);
            if(!$page || !$page->id || !$page->template->filenameExists()) {
                return $url;
            }
            $rewritten = rtrim($path, '/') . '.html';
        }

        if(isset($parts['query'])) $rewritten .= '?' . $parts['query'];
        if(isset($parts['fragment'])) $rewritten .= '#' . $parts['fragment'];

        $rewritten = $this->applySiteUrl($rewritten);
        if($this->config->cli && $rewritten !== $original) {
            echo '  link: ' . $original . ' -> ' . $rewritten . "\n";
        }

        return $rewritten;
    }

    protected function copyAssetToOutput(string $sourceFile, string $originalPath): void
    {
        $cacheKey = md5($sourceFile);
        if(isset($this->copiedAssets[$cacheKey])) return;

        $targetFile = $this->assetsOutputPath . ltrim($originalPath, '/');
        $targetDir = dirname($targetFile);
        if(!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if($this->shouldProcessTextAsset($sourceFile)) {
            $content = @file_get_contents($sourceFile);
            if($content !== false) {
                file_put_contents($targetFile, $this->processAssetContent($content, $sourceFile));
            } else {
                copy($sourceFile, $targetFile);
            }
        } else {
            copy($sourceFile, $targetFile);
        }

        chmod($targetFile, 0644);
        if($this->config->cli) {
            echo '  asset: ' . $originalPath . "\n";
        }

        if($this->isCssFile($sourceFile)) {
            $this->copyCssDependencies($sourceFile, $originalPath);
        }

        $this->copiedAssets[$cacheKey] = true;
    }

    protected function copyCssDependencies(string $cssSourceFile, string $cssOriginalPath): void
    {
        $content = @file_get_contents($cssSourceFile);
        if($content === false) return;

        $matches = [];
        preg_match_all('/@import\s+(?:url\()?["\']?([^"\'\)\s;]+)["\']?\)?/i', $content, $matches);
        foreach($matches[1] ?? [] as $url) {
            $this->copyCssDependencyFromUrl($url, $cssOriginalPath);
        }

        $matches = [];
        preg_match_all('/url\(\s*["\']?([^"\'\)]+)["\']?\s*\)/i', $content, $matches);
        foreach($matches[1] ?? [] as $url) {
            $this->copyCssDependencyFromUrl($url, $cssOriginalPath);
        }
    }

    protected function copyCssDependencyFromUrl(string $depUrl, string $cssOriginalPath): void
    {
        $depUrl = trim($depUrl);
        if($depUrl === '' || $this->isExternalOrSpecialUrl($depUrl)) return;

        $parts = parse_url($depUrl);
        if(!$parts || empty($parts['path'])) return;

        $depPath = $parts['path'];
        if(strpos($depPath, '/') === 0) {
            $resolved = $depPath;
        } else {
            $resolved = $this->normalizePath(dirname($cssOriginalPath) . '/' . $depPath);
        }

        $sourceFile = $this->siteRootPath . ltrim($resolved, '/');
        if(!is_file($sourceFile)) return;

        $this->copyAssetToOutput($sourceFile, $resolved);
    }

    protected function normalizeIncomingUrl(string $url): string
    {
        $parts = parse_url($url);
        if(!$parts || empty($parts['host'])) {
            return $url;
        }

        $candidateHost = strtolower($parts['host']);
        foreach($this->getInternalHosts() as $host) {
            if($this->isSameHost($candidateHost, $host)) {
                $path = $parts['path'] ?? '/';
                if($path === '') $path = '/';
                $normalized = $this->stripKnownBasePath($path);
                if(isset($parts['query'])) $normalized .= '?' . $parts['query'];
                if(isset($parts['fragment'])) $normalized .= '#' . $parts['fragment'];
                return $normalized;
            }
        }

        return $url;
    }

    protected function getInternalHosts(): array
    {
        $hosts = [];
        $configured = $this->parseSiteUrl($this->siteUrl);
        if($configured && !empty($configured['host'])) {
            $hosts[] = strtolower($configured['host']);
        }

        $httpHost = $this->extractHost((string) ($this->config->httpHost ?? ''));
        if($httpHost !== '') $hosts[] = $httpHost;

        $httpHosts = $this->config->httpHosts ?? [];
        if(is_array($httpHosts)) {
            foreach($httpHosts as $h) {
                $v = $this->extractHost((string) $h);
                if($v !== '') $hosts[] = $v;
            }
        }

        return array_values(array_unique($hosts));
    }

    protected function applySiteUrl(string $url): string
    {
        if($this->siteUrl === '' || strpos($url, '/') !== 0) {
            return $url;
        }
        return $this->siteUrl . $url;
    }

    protected function normalizeSiteUrl(string $siteUrl): string
    {
        $siteUrl = trim($siteUrl);
        if($siteUrl === '') {
            $siteUrl = (string) ($this->config->httpHost ?? '') . (string) ($this->config->urls->httpRoot ?? '/');
        }
        if(stripos($siteUrl, 'http://') !== 0 && stripos($siteUrl, 'https://') !== 0) {
            $siteUrl = 'https://' . ltrim($siteUrl, '/');
        }
        return rtrim($siteUrl, '/');
    }

    protected function parseSiteUrl(string $siteUrl): ?array
    {
        $parts = parse_url($siteUrl);
        if(!$parts || empty($parts['host'])) return null;
        return $parts;
    }

    protected function extractHost(string $value): string
    {
        $value = trim($value);
        if($value === '') return '';
        if(stripos($value, 'http://') !== 0 && stripos($value, 'https://') !== 0) {
            $value = 'https://' . $value;
        }
        $host = parse_url($value, PHP_URL_HOST);
        return $host ? strtolower($host) : '';
    }

    protected function stripKnownBasePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $bases = [];
        $parsed = $this->parseSiteUrl($this->siteUrl);
        if($parsed && !empty($parsed['path'])) {
            $bases[] = '/' . trim((string) $parsed['path'], '/');
        }
        $bases[] = '/' . trim((string) ($this->config->urls->httpRoot ?? '/'), '/');

        foreach(array_unique($bases) as $base) {
            if($base === '/' || $base === '') continue;
            if($path === $base) return '/';
            if(strpos($path, $base . '/') === 0) return substr($path, strlen($base));
        }

        return $path;
    }

    protected function normalizeAllowedModuleAssets(array $allowed): array
    {
        $out = [];
        foreach($allowed as $prefix) {
            $prefix = trim((string) $prefix);
            if($prefix === '') continue;
            $out[] = '/' . trim($prefix, '/') . '/';
        }
        return array_values(array_unique($out));
    }

    protected function isExternalOrSpecialUrl(string $url): bool
    {
        $lower = strtolower($url);
        return $url === ''
            || strpos($url, '#') === 0
            || strpos($lower, 'data:') === 0
            || strpos($lower, 'mailto:') === 0
            || strpos($lower, 'tel:') === 0
            || strpos($lower, 'javascript:') === 0
            || strpos($url, '//') === 0
            || preg_match('/^https?:\/\//i', $url);
    }

    protected function normalizePath(string $path): string
    {
        $segments = explode('/', str_replace('\\', '/', $path));
        $clean = [];
        foreach($segments as $segment) {
            if($segment === '' || $segment === '.') continue;
            if($segment === '..') {
                array_pop($clean);
                continue;
            }
            $clean[] = $segment;
        }
        return '/' . implode('/', $clean);
    }

    protected function isCssFile(string $filePath): bool
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'css';
    }

    protected function isJsFile(string $filePath): bool
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'js';
    }

    protected function shouldProcessTextAsset(string $filePath): bool
    {
        return ($this->compressCss && $this->isCssFile($filePath)) || ($this->compressJs && $this->isJsFile($filePath));
    }

    protected function processAssetContent(string $content, string $filePath): string
    {
        if($this->compressCss && $this->isCssFile($filePath)) {
            return $this->compressCssOutput($content);
        }
        if($this->compressJs && $this->isJsFile($filePath)) {
            return $this->compressJsOutput($content);
        }
        return $content;
    }

    protected function compressHtmlOutput(string $html): string
    {
        $html = preg_replace('/<!--(?!\[if)(.*?)-->/is', '', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        $html = preg_replace('/\n\s*\n+/', "\n", $html);
        return trim($html);
    }

    protected function compressCssOutput(string $css): string
    {
        $css = preg_replace('!\/\*[^!][\s\S]*?\*\/!', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css);
        $css = str_replace(';}', '}', $css);
        return trim($css);
    }

    protected function compressJsOutput(string $js): string
    {
        $js = preg_replace('!\/\*[\s\S]*?\*\/!', '', $js);
        $js = preg_replace('/\n\s*\n+/', "\n", $js);
        $js = preg_replace('/\s+/', ' ', $js);
        return trim($js);
    }

    protected function isSameHost(string $a, string $b): bool
    {
        if($a === $b) return true;
        return preg_replace('/^www\./i', '', $a) === preg_replace('/^www\./i', '', $b);
    }
}
