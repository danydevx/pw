<?php namespace ProcessWire;

class ProcessHelpersText {

    protected static function sanitizer(): Sanitizer {
        /** @var Sanitizer $sanitizer */
        $sanitizer = wire('sanitizer');
        return $sanitizer;
    }

    public static function escape(string $text): string {
        return self::sanitizer()->entities($text);
    }

    public static function text($value, int $maxLength = 0): string {
        $out = self::sanitizer()->text((string) $value);
        if($maxLength > 0) {
            return self::truncate($out, $maxLength);
        }
        return $out;
    }

    public static function textarea($value): string {
        return self::sanitizer()->textarea((string) $value);
    }

    public static function email($value): string {
        return self::sanitizer()->email((string) $value);
    }

    public static function url($value): string {
        return self::sanitizer()->url((string) $value);
    }

    public static function int($value): int {
        return self::sanitizer()->int($value);
    }

    public static function float($value): float {
        return self::sanitizer()->float($value);
    }

    public static function pageName($value, bool $utf8 = true): string {
        $sanitizer = self::sanitizer();
        return $utf8 ? $sanitizer->pageNameUTF8((string) $value) : $sanitizer->pageName((string) $value);
    }

    public static function truncate(string $text, int $length = 140, string $suffix = '...'): string {
        $text = trim((string) $text);
        if($length < 1 || $text === '') return '';

        $sanitizer = self::sanitizer();
        if(method_exists($sanitizer, 'truncate')) {
            return (string) $sanitizer->truncate($text, [
                'maxLength' => $length,
                'type' => 'sentence',
                'visible' => true,
                'more' => $suffix,
            ]);
        }

        if(mb_strlen($text) <= $length) return $text;
        return rtrim(mb_substr($text, 0, $length)) . $suffix;
    }

    public static function excerpt(string $html, int $length = 160, string $suffix = '...'): string {
        $sanitizer = self::sanitizer();
        $text = method_exists($sanitizer, 'markupToText')
            ? (string) $sanitizer->markupToText($html)
            : trim(strip_tags($html));
        return self::truncate($text, $length, $suffix);
    }

    public static function words(string $text, int $maxWords = 30, string $suffix = '...'): string {
        $text = trim((string) $text);
        if($text === '' || $maxWords < 1) return '';

        $parts = preg_split('/\s+/u', $text) ?: [];
        if(count($parts) <= $maxWords) return $text;
        return implode(' ', array_slice($parts, 0, $maxWords)) . $suffix;
    }
}
