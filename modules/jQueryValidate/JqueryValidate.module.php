<?php namespace ProcessWire;

class JqueryValidate extends WireData implements Module {

    protected $assetsLoaded = false;

    public static function getModuleInfo() {
        return [
            'title' => 'JqueryValidate',
            'version' => 1,
            'summary' => 'Carga librerías jQuery Validate en admin y frontend.',
            'autoload' => true,
            'singular' => true,
        ];
    }

    public function init() {
        $this->addHookBefore('Page::render', $this, 'includeAssets');
    }

    public function includeAssets() {
        if($this->assetsLoaded) return;

        $url = $this->config->urls->JqueryValidate . 'js/';

        $this->config->scripts->add($url . 'jquery.validate.min.js');
        $this->config->scripts->add($url . 'additional-methods.min.js');

        $this->assetsLoaded = true;
    }
}