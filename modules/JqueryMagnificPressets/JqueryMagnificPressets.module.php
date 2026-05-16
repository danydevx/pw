<?php namespace ProcessWire;

class JqueryMagnificPressets extends WireData implements Module {

    protected $assetsLoaded = false;

    public static function getModuleInfo() {
        return [
            'title' => 'JqueryMagnificPressets',
            'version' => 1,
            'summary' => 'Carga Magnific Popup y expone presets reutilizables.',
            'autoload' => true,
            'singular' => true,
        ];
    }

    public function includeAssets() {
        if($this->assetsLoaded) return;

        $config = wire('config');
        $config->styles->add('https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css');
        $config->scripts->add('https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js');

        $this->assetsLoaded = true;
    }

    public function getPresets() {
        return [
            'lightbox' => [
                'type' => 'image',
                'closeOnContentClick' => true,
                'mainClass' => 'mfp-img-mobile',
                'image' => ['verticalFit' => true],
            ],
            'gallery' => [
                'type' => 'image',
                'gallery' => ['enabled' => true],
                'closeOnContentClick' => false,
            ],
            'iframe' => [
                'type' => 'iframe',
                'preloader' => true,
                'fixedContentPos' => false,
            ],
            'inline' => [
                'type' => 'inline',
                'midClick' => true,
                'closeBtnInside' => true,
            ],
            'confirm' => [
                'type' => 'inline',
                'midClick' => true,
                'showCloseBtn' => false,
                'closeOnBgClick' => false,
                'enableEscapeKey' => true,
            ],
        ];
    }

    public function getPreset($name = 'lightbox') {
        $presets = $this->getPresets();
        if(isset($presets[$name])) return $presets[$name];
        return $presets['lightbox'];
    }

    public function renderInitScript($selector, $preset = 'lightbox', array $overrides = []) {
        $this->includeAssets();

        $safeSelector = trim((string) $selector);
        if($safeSelector === '') return '';

        $config = array_replace_recursive($this->getPreset($preset), $overrides);
        $jsonConfig = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $jsonSelector = json_encode($safeSelector, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if(!$jsonConfig || !$jsonSelector) return '';

        return '<script>(function($){$(function(){var s=' . $jsonSelector . ';if(!window.jQuery||!$.fn.magnificPopup)return;var el=$(s);if(!el.length)return;el.magnificPopup(' . $jsonConfig . ');});})(jQuery);</script>';
    }
}
