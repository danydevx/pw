<?php namespace ProcessWire;

class TawkToChat extends WireData implements Module, ConfigurableModule {

    public static function getModuleInfo() {
        return [
            'title' => 'Tawk.to Chat',
            'version' => 1,
            'summary' => 'Modulo configurable para insertar el script de Tawk.to.',
            'author' => 'ASE',
            'autoload' => true,
            'singular' => true,
            'icon' => 'comments',
        ];
    }

    public function render(): string {
        $config = $this->wire('modules')->getConfig($this);

        if((int) ($config['enabled'] ?? 1) !== 1) {
            return '';
        }

        $page = $this->wire('page');
        if($page && $page->template && $page->template->name === 'admin') {
            return '';
        }

        $src = trim((string) ($config['widget_src'] ?? 'https://embed.tawk.to/62fa833054f06e12d88ec57e/1gah90tcl'));
        if(!$src || !filter_var($src, FILTER_VALIDATE_URL)) {
            return '';
        }

        return '<script type="text/javascript">'
            . 'var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();'
            . '(function(){'
            . 'var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];'
            . 's1.async=true;'
            . "s1.src='" . $this->wire('sanitizer')->entities($src) . "';"
            . 's1.charset="UTF-8";'
            . 's1.setAttribute("crossorigin","*");'
            . 's0.parentNode.insertBefore(s1,s0);'
            . '})();'
            . '</script>';
    }

    public static function getModuleConfigInputfields(array $data) {
        $modules = wire('modules');
        $fields = $modules->get('InputfieldWrapper');

        $enabled = $modules->get('InputfieldSelect');
        $enabled->name = 'enabled';
        $enabled->label = 'Estado del chat';
        $enabled->description = 'Activa o desactiva el script de Tawk.to en frontend.';
        $enabled->addOptions([
            '1' => 'Habilitado',
            '0' => 'Deshabilitado',
        ]);
        $enabled->value = (string) ($data['enabled'] ?? '1');
        $fields->add($enabled);

        $widgetSrc = $modules->get('InputfieldURL');
        $widgetSrc->name = 'widget_src';
        $widgetSrc->label = 'URL del widget';
        $widgetSrc->description = 'URL embed de Tawk.to.';
        $widgetSrc->attr('value', $data['widget_src'] ?? 'https://embed.tawk.to/62fa833054f06e12d88ec57e/1gah90tcl');
        $fields->add($widgetSrc);

        $help = $modules->get('InputfieldMarkup');
        $help->label = 'Uso en templates';
        $help->value = '<p>En tu layout o template, renderiza el script con:</p>'
            . '<pre><code>&lt;?php echo wire(&quot;modules&quot;)-&gt;get(&quot;TawkToChat&quot;)-&gt;render(); ?&gt;</code></pre>';
        $fields->add($help);

        return $fields;
    }
}
