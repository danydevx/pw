<?php namespace ProcessWire;

class SwiperLoader extends WireData implements Module, ConfigurableModule {

    public static function getModuleInfo() {
        return [
            'title' => 'Swiper Loader',
            'version' => 1,
            'summary' => 'Carga Swiper CSS/JS automaticamente en templates del frontend.',
            'author' => 'ASE',
            'autoload' => true,
            'singular' => true,
        ];
    }

    public function init() {
        if(!$this->shouldLoadOnCurrentRequest()) return;

        $config = $this->wire('config');
        $moduleConfig = $this->wire('modules')->getConfig($this);
        $version = $this->wire('sanitizer')->text($moduleConfig['version'] ?? '11');

        $cssUrl = $moduleConfig['css_url'] ?? 'https://cdn.jsdelivr.net/npm/swiper@{version}/swiper-bundle.min.css';
        $jsUrl = $moduleConfig['js_url'] ?? 'https://cdn.jsdelivr.net/npm/swiper@{version}/swiper-bundle.min.js';

        $cssUrl = str_replace('{version}', $version, (string) $cssUrl);
        $jsUrl = str_replace('{version}', $version, (string) $jsUrl);

        if(($moduleConfig['load_css'] ?? 1) && filter_var($cssUrl, FILTER_VALIDATE_URL)) {
            $config->styles->add($cssUrl);
        }

        if(($moduleConfig['load_js'] ?? 1) && filter_var($jsUrl, FILTER_VALIDATE_URL)) {
            $config->scripts->add($jsUrl);
        }
    }

    protected function shouldLoadOnCurrentRequest() {
        $page = $this->wire('page');
        if(!$page || !$page->id) return false;

        if($page->template && $page->template->name === 'admin') return false;

        $moduleConfig = $this->wire('modules')->getConfig($this);
        if(!($moduleConfig['enabled'] ?? 1)) return false;

        $templates = trim((string) ($moduleConfig['templates'] ?? ''));
        if($templates === '') return true;

        $allowedTemplates = array_filter(array_map('trim', explode(',', $templates)));
        if(!count($allowedTemplates)) return true;

        return in_array($page->template->name, $allowedTemplates, true);
    }

    public static function getModuleConfigInputfields(array $data) {
        $modules = wire('modules');

        $fields = $modules->get('InputfieldWrapper');

        $enabled = $modules->get('InputfieldCheckbox');
        $enabled->name = 'enabled';
        $enabled->label = 'Habilitar Swiper Loader';
        $enabled->checked = (int) ($data['enabled'] ?? 1);
        $enabled->value = 1;
        $fields->add($enabled);

        $version = $modules->get('InputfieldText');
        $version->name = 'version';
        $version->label = 'Version de Swiper';
        $version->description = 'Se reemplaza en las URLs con {version}. Ejemplo: 11';
        $version->attr('value', $data['version'] ?? '11');
        $fields->add($version);

        $cssUrl = $modules->get('InputfieldURL');
        $cssUrl->name = 'css_url';
        $cssUrl->label = 'URL CSS';
        $cssUrl->attr('value', $data['css_url'] ?? 'https://cdn.jsdelivr.net/npm/swiper@{version}/swiper-bundle.min.css');
        $fields->add($cssUrl);

        $jsUrl = $modules->get('InputfieldURL');
        $jsUrl->name = 'js_url';
        $jsUrl->label = 'URL JS';
        $jsUrl->attr('value', $data['js_url'] ?? 'https://cdn.jsdelivr.net/npm/swiper@{version}/swiper-bundle.min.js');
        $fields->add($jsUrl);

        $loadCss = $modules->get('InputfieldCheckbox');
        $loadCss->name = 'load_css';
        $loadCss->label = 'Cargar CSS';
        $loadCss->checked = (int) ($data['load_css'] ?? 1);
        $loadCss->value = 1;
        $fields->add($loadCss);

        $loadJs = $modules->get('InputfieldCheckbox');
        $loadJs->name = 'load_js';
        $loadJs->label = 'Cargar JS';
        $loadJs->checked = (int) ($data['load_js'] ?? 1);
        $loadJs->value = 1;
        $fields->add($loadJs);

        $templates = $modules->get('InputfieldText');
        $templates->name = 'templates';
        $templates->label = 'Templates permitidos (opcional)';
        $templates->description = 'Separados por coma. Vacio = todos los templates del frontend.';
        $templates->attr('value', $data['templates'] ?? '');
        $fields->add($templates);

        $help = $modules->get('InputfieldMarkup');
        $help->label = 'Ejemplo de uso e instrucciones';
        $help->value = '<p><strong>1)</strong> Guarda la configuracion del modulo.</p>'
            . '<p><strong>2)</strong> En tu template o vista agrega este snippet:</p>'
            . '<pre><code>&lt;div class="swiper js-swiper" data-loop="true" data-speed="500" data-space="20" data-slides="1"&gt;'
            . '\n  &lt;div class="swiper-wrapper"&gt;'
            . '\n    &lt;div class="swiper-slide"&gt;Slide 1&lt;/div&gt;'
            . '\n    &lt;div class="swiper-slide"&gt;Slide 2&lt;/div&gt;'
            . '\n    &lt;div class="swiper-slide"&gt;Slide 3&lt;/div&gt;'
            . '\n  &lt;/div&gt;'
            . '\n  &lt;div class="swiper-pagination"&gt;&lt;/div&gt;'
            . '\n  &lt;div class="swiper-button-prev"&gt;&lt;/div&gt;'
            . '\n  &lt;div class="swiper-button-next"&gt;&lt;/div&gt;'
            . '\n&lt;/div&gt;</code></pre>'
            . '<p><strong>3)</strong> Verifica que tu layout imprima <code>$config-&gt;styles</code> y <code>$config-&gt;scripts</code>.</p>';
        $fields->add($help);

        return $fields;
    }
}
