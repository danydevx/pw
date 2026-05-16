<?php namespace ProcessWire;

require_once __DIR__ . '/ProcessHelpersImages.php';
require_once __DIR__ . '/ProcessHelpersText.php';

if(!function_exists(__NAMESPACE__ . '\renderStaticPicture')) {
    function renderStaticPicture($source, array $options = []): string {
        return ProcessHelpersImages::renderStaticPicture($source, $options);
    }
}

if(!function_exists(__NAMESPACE__ . '\getStaticImageUrl')) {
    function getStaticImageUrl(string $basename, array $options = []): string {
        return ProcessHelpersImages::getStaticImageUrl($basename, $options);
    }
}

if(!function_exists(__NAMESPACE__ . '\phText')) {
    function phText($value, int $maxLength = 0): string {
        return ProcessHelpersText::text($value, $maxLength);
    }
}

if(!function_exists(__NAMESPACE__ . '\phExcerpt')) {
    function phExcerpt(string $html, int $length = 160, string $suffix = '...'): string {
        return ProcessHelpersText::excerpt($html, $length, $suffix);
    }
}

if(!function_exists(__NAMESPACE__ . '\phPageName')) {
    function phPageName($value, bool $utf8 = true): string {
        return ProcessHelpersText::pageName($value, $utf8);
    }
}
