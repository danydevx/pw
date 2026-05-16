<?php namespace ProcessWire;

/**
 * WhatsappWidget.module (Vanilla ready)
 */
class WhatsappWidget extends WireData implements Module, ConfigurableModule {

  protected string $assetsUrl;
  protected string $modulePath;

  public static function getModuleInfo() {
    return [
      'title'         => 'WhatsappWidget',
      'version'       => 2_10,
      'summary'       => 'Widget de chat de WhatsApp configurable (Vanilla JS)',
      'author'        => 'Daniel + ChatGPT',
      'href'          => '',
      'autoload'      => true,
      'singular'      => true,
      'permanent'     => false,
      'icon'          => 'commenting-o',
      'requires'      => ['PHP>=8.0'],
      'config'        => true,
      'configurable'  => true,
    ];
  }

  protected function defaults(): array {
    return [
      'enabled'            => 1,
      'widget'             => 'agent',        // agent | basic
      'position'           => 'right',        // right | left
      'openDefault'        => 0,
      'showOn'             => 'all',          // all | desktop | mobile
      'label'              => '¿Tiene una duda? Estamos en WhatsApp',
      'header'             => 'Comencemos a chatear',
      'cta'                => 'Click para chatear con nosotros',
      'phone'              => '5213321845739',
      'message'            => 'Empecemos a chatear',
      'agent_name'         => 'Juan Perez',
      'agent_status'       => 'En línea',
      'agent_description'  => 'Diseño web',
      'agent_image'        => '',
      // dependencias
      'includeMoment'      => 1,
      'includeBootstrapIcons' => 0,
      // nuevas (Vanilla)
      'timezone'           => 'America/Mexico_City',
      'now'                => '',             // 'YYYY-MM-DD HH:mm:ss' (opcional)
      'popupFx'            => '1',            // '0'..'14'
      'debug'              => 0,
    ];
  }

  protected function cfg(): array {
    $cfg = (array) $this->modules->getConfig($this);
    $defaults = $this->defaults();
    $merged = array_replace($defaults, $cfg);

    // Flags booleanos configurados con InputfieldCheckboxes
    $boolKeys = ['enabled', 'openDefault', 'includeMoment', 'includeBootstrapIcons', 'debug'];
    $hasSavedConfig = !empty($cfg);
    foreach ($boolKeys as $key) {
      $raw = $hasSavedConfig ? ($cfg[$key] ?? 0) : ($defaults[$key] ?? 0);
      $merged[$key] = is_array($raw) ? (int) !empty($raw) : (int) ((bool) $raw);
    }

    // Compat: migrar clave antigua si existiera en config guardada
    if (!array_key_exists('includeBootstrapIcons', $cfg) && array_key_exists('includeFontAwesome', $cfg)) {
      $raw = $cfg['includeFontAwesome'];
      $merged['includeBootstrapIcons'] = is_array($raw) ? (int) !empty($raw) : (int) ((bool) $raw);
    }

    return $merged;
  }

  public function init() {
    $modules   = $this->wire('modules');
    $config    = $this->wire('config');

    $moduleFile = $modules->getModuleFile(get_class($this));
    $moduleDir  = dirname($moduleFile);
    $moduleFolder = basename($moduleDir);

    $this->modulePath = rtrim($moduleDir, '/').'/';
    $this->assetsUrl  = rtrim($config->urls('siteModules'), '/')."/{$moduleFolder}/assets/";

    $cfg = $this->cfg();
    if (!$cfg['enabled']) return;

    // Solo visitantes (guest)
    $user = $this->wire('user');
    if (!$user || !$user->isGuest()) {
      return;
    }

    // Solo frontend
    $process = $this->wire('process');
    $page = $this->wire('page');
    if ($process instanceof ProcessPageEdit || ($page && $page->template && $page->template->name === 'admin')) {
      return;
    }

    // styles
    $this->config->styles->add($this->assetsUrl.'plugin/whatsapp-chat-support.css');
    // Bootstrap Icons no longer needed: templates now use Font Awesome icons.

    // scripts
    if (!empty($cfg['includeMoment'])) {
      $this->config->scripts->add($this->assetsUrl.'plugin/components/moment/moment.min.js');
      $this->config->scripts->add($this->assetsUrl.'plugin/components/moment/moment-timezone-with-data.min.js');
    }

 
    // Versión Vanilla (sin jQuery). Asegúrate de este filename:
    $this->config->scripts->add($this->assetsUrl.'plugin/whatsapp-chat-support.vanilla.js');

    // Hook para inyectar markup + init
    $this->addHookAfter('Page::render', $this, 'injectInitScript');
  }

  public function injectInitScript(HookEvent $event) {
    $cfg = $this->cfg();
    if (!$cfg['enabled']) return;

    $out = (string) $event->return;

    // Si la plantilla no incluyó el widget, lo inyectamos automáticamente.
    if (strpos($out, 'id="WhatsappWidget"') === false) {
      $widgetHtml = $this->renderPlugin((string) ($cfg['widget'] ?? 'agent'));
      if ($widgetHtml !== '') {
        if (false !== ($pWidget = strripos($out, '</body>'))) {
          $out = substr($out, 0, $pWidget) . $widgetHtml . "\n" . substr($out, $pWidget);
        } else {
          $out .= $widgetHtml;
        }
      }
    }

    if (strpos($out, 'id="WhatsappWidget"') === false) {
      $event->return = $out;
      return;
    }

    $opts = [
      'position'    => in_array($cfg['position'], ['left','right'], true) ? $cfg['position'] : 'right',
      'openDefault' => (int) $cfg['openDefault'],
      'showOn'      => in_array($cfg['showOn'], ['all','desktop','mobile'], true) ? $cfg['showOn'] : 'all',
      'label'       => $this->sanitizer->entities1($cfg['label']),
      'header'      => $this->sanitizer->entities1($cfg['header']),
      'cta'         => $this->sanitizer->entities1($cfg['cta']),
      'phone'       => preg_replace('/\D+/', '', (string) $cfg['phone']),
      'message'     => $this->sanitizer->entities1($cfg['message']),
      'agent'       => [
        'name'        => $this->sanitizer->entities1($cfg['agent_name']),
        'status'      => $this->sanitizer->entities1($cfg['agent_status']),
        'description' => $this->sanitizer->entities1($cfg['agent_description']),
        'image'       => $cfg['agent_image'] !== '' ? $this->sanitizer->url($cfg['agent_image']) : ($this->assetsUrl.'img/person_default.jpg'),
      ],
      // Opciones Vanilla
      'timezone'    => $this->sanitizer->entities1($cfg['timezone'] ?: ''),
      'now'         => $this->sanitizer->entities1($cfg['now'] ?: ''),
      'popupFx'     => (string) $cfg['popupFx'],
      'debug'       => (int) $cfg['debug'],
    ];

    $json = json_encode($opts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $script  = "";
    $script .= "<script>(function(){\n";
    $script .= "  function initWhatsappWidget(){\n";
    $script .= "    try {\n";
    $script .= "      var el = document.getElementById('WhatsappWidget');\n";
    $script .= "      if(!el) return true;\n";
    $script .= "      var opts = ".$json.";\n";
    $script .= "      var isMobile = /Mobi|Android/i.test(navigator.userAgent);\n";
    $script .= "      if((opts.showOn==='mobile' && !isMobile) || (opts.showOn==='desktop' && isMobile)) return true;\n";
    $script .= "      if(opts.position==='left'){ el.classList.add('is-left'); }\n";
    $script .= "      if(opts.popupFx){ el.classList.add('wcs-effect-'+opts.popupFx); }\n";
    $script .= "      if(typeof window.WhatsappChatSupport === 'function'){\n";
    $script .= "        new window.WhatsappChatSupport(el, {\n";
    $script .= "          defaultMsg: opts.message,\n";
    $script .= "          timezone: opts.timezone || 'America/Mexico_City',\n";
    $script .= "          now: opts.now || '',\n";
    $script .= "          popupFx: String(opts.popupFx||'1'),\n";
    $script .= "          debug: !!opts.debug\n";
    $script .= "        });\n";
    $script .= "        if(opts.openDefault){ setTimeout(function(){ el.classList.add('wcs-show'); }, 10); }\n";
    $script .= "        return true;\n";
    $script .= "      }\n";
    $script .= "      if (window.jQuery && window.jQuery.fn && typeof jQuery.fn.whatsappChatSupport==='function') {\n";
    $script .= "        jQuery(el).whatsappChatSupport({ defaultMsg: opts.message });\n";
    $script .= "        if(opts.openDefault){ setTimeout(function(){ el.classList.add('wcs-show'); }, 10); }\n";
    $script .= "        return true;\n";
    $script .= "      }\n";
    $script .= "      return false;\n";
    $script .= "    } catch(e) {\n";
    $script .= "      console && console.warn && console.warn('WhatsappWidget init error', e);\n";
    $script .= "      return true;\n";
    $script .= "    }\n";
    $script .= "  }\n";
    $script .= "  var tries = 0;\n";
    $script .= "  var maxTries = 30;\n";
    $script .= "  function boot(){\n";
    $script .= "    if (initWhatsappWidget()) return;\n";
    $script .= "    tries++;\n";
    $script .= "    if (tries >= maxTries) {\n";
    $script .= "      console && console.warn && console.warn('WhatsappWidget: plugin no encontrado (Vanilla/jQuery)');\n";
    $script .= "      return;\n";
    $script .= "    }\n";
    $script .= "    setTimeout(boot, 100);\n";
    $script .= "  }\n";
    $script .= "  if (document.readyState === 'loading') {\n";
    $script .= "    document.addEventListener('DOMContentLoaded', boot, { once: true });\n";
    $script .= "  } else {\n";
    $script .= "    boot();\n";
    $script .= "  }\n";
    $script .= "})();</script>\n";
    $script .= "<!-- /WhatsappWidget:init -->\n";

    if (false !== ($p = strripos($out, '</body>'))) {
      $out = substr($out, 0, $p) . $script . substr($out, $p);
    } else {
      $out .= $script;
    }

    $event->return = $out;
  }

  public function renderPlugin(string $widget = 'agent'): string {
    $cfg = $this->cfg();
    if (!$cfg['enabled']) return '';

    // Evitar render para usuarios autenticados (admin/editor/etc.)
    $user = $this->wire('user');
    if (!$user || !$user->isGuest()) return '';

    $params = [
      'label'             => $cfg['label'],
      'header'            => $cfg['header'],
      'cta'               => $cfg['cta'],
      'phone'             => $cfg['phone'],
      'message'           => $cfg['message'],
      'agent_name'        => $cfg['agent_name'],
      'agent_status'      => $cfg['agent_status'],
      'agent_description' => $cfg['agent_description'],
      'agent_image'       => $cfg['agent_image'] ?: ($this->assetsUrl.'img/person_default.jpg'),
      'position'          => $cfg['position'],
      'openDefault'       => (int) $cfg['openDefault'],
      'assetsUrl'         => $this->assetsUrl,
    ];

    $requestedWidget = $this->sanitizer->name($widget);
    $widgetAliases = [
      'basic' => 'button',
      'inline' => 'chat',
      'single' => 'chat',
      'multi' => 'agents',
    ];
    $viewWidget = $widgetAliases[$requestedWidget] ?? $requestedWidget;

    $viewFile = $this->modulePath . 'views/widget-' . $viewWidget . '.tpl.php';
    if (!is_file($viewFile)) {
      return "<!-- WhatsappWidget: vista no encontrada ($viewFile) -->";
    }
    return $this->files->render($viewFile, $params);
  }

  /** Compat (si todavía los llamas manualmente en plantillas) */
  public function getStyles(): string {
    $cfg = $this->cfg();
    $out = [];
    $out[] = '<link rel="stylesheet" href="'.$this->assetsUrl.'plugin/whatsapp-chat-support.css">';
    if ($cfg['includeBootstrapIcons']) {
      $out[] = '<link rel="stylesheet" href="'.$this->bootstrapIconsCdn.'">';
    }
    return implode("\n", $out);
  }

  public function getScripts(): string {
    $cfg = $this->cfg();
    $out = [];
    if ($cfg['includeMoment']) {
      $out[] = '<script src="'.$this->assetsUrl.'plugin/components/moment/moment.min.js"></script>';
      $out[] = '<script src="'.$this->assetsUrl.'plugin/components/moment/moment-timezone-with-data.min.js"></script>';
    }
    $out[] = '<script src="'.$this->assetsUrl.'plugin/whatsapp-chat-support.vanilla.js"></script>';
    return implode("\n", $out);
  }

  public function Initialize(): string {
    // Compatibilidad legacy: si existe Vanilla, úsalo; sino intenta jQuery.
    return <<<HTML
<script>
(function(){
  var el = document.getElementById('WhatsappWidget');
  if(!el) return;
  if (typeof window.WhatsappChatSupport === 'function') {
    new window.WhatsappChatSupport(el, {});
  } else if (window.jQuery && window.jQuery.fn && typeof jQuery.fn.whatsappChatSupport==='function') {
    jQuery(el).whatsappChatSupport({});
  }
})();
</script>
HTML;
  }

  /** Config */
  public function getModuleConfigInputfields($f = null) {
    $modules = $this->wire('modules');
    $data = (array) $modules->getConfig($this);
    if (!$f) {
      $f = $modules->get('InputfieldWrapper');
    }

    $usage = $modules->get('InputfieldMarkup');
    $usage->label = 'Uso en frontend';
    $usage->value = "<p>El módulo puede renderizarse automáticamente (autoload) o insertarse manualmente en tu template.</p>\n"
      . "<p><strong>Render manual recomendado:</strong></p>\n"
      . "<pre><code>&lt;?php namespace ProcessWire;\n"
      . "\$widget = wire('modules')-&gt;get('WhatsappWidget');\n"
      . "if(\$widget) echo \$widget-&gt;renderPlugin();\n"
      . "?&gt;</code></pre>\n"
      . "<p><strong>Elegir vista:</strong> <code>renderPlugin('agent')</code>, <code>renderPlugin('chat')</code>, <code>renderPlugin('button')</code>.</p>\n"
      . "<p><strong>Alias compatibles:</strong> <code>basic</code>→<code>button</code>, <code>inline</code>→<code>chat</code>, <code>multi</code>→<code>agents</code>.</p>";
    $f->add($usage);

    $d = $this->defaults();
    $val = function($key) use($data,$d){ return $data[$key] ?? $d[$key]; };
    $boolVal = function($key) use($data,$d){
      $raw = array_key_exists($key, $data) ? $data[$key] : ($d[$key] ?? 0);
      return is_array($raw) ? (int) !empty($raw) : (int) ((bool) $raw);
    };

    // Activado
    $en = $modules->get('InputfieldCheckboxes');
    $en->name = 'enabled';
    $en->label = 'Activar widget';
    $en->description = 'Muestra el widget en el front.';
    $en->addOptions(['1' => 'Si']);
    $en->value = (string) $boolVal('enabled');
    $f->add($en);

    // Widget
    $w = $modules->get('InputfieldSelect');
    $w->name = 'widget';
    $w->label = 'Tipo de widget (vista)';
    $w->addOptions(['agent'=>'Agente','basic'=>'Básico']);
    $w->value = $val('widget');
    $f->add($w);

    // Posición
    $p = $modules->get('InputfieldSelect');
    $p->name = 'position';
    $p->label = 'Posición';
    $p->addOptions(['right'=>'Derecha','left'=>'Izquierda']);
    $p->value = $val('position');
    $f->add($p);

    // Efecto popup
    $fx = $modules->get('InputfieldSelect');
    $fx->name = 'popupFx';
    $fx->label = 'Efecto del popup';
    $optsFx = [];
    for($i=0;$i<=14;$i++){ $optsFx[(string)$i] = "Efecto {$i}"; }
    $fx->addOptions($optsFx);
    $fx->value = (string) $val('popupFx');
    $f->add($fx);

    // Apertura por defecto
    $od = $modules->get('InputfieldCheckboxes');
    $od->name = 'openDefault';
    $od->label = 'Abrir por defecto';
    $od->addOptions(['1' => 'Si']);
    $od->value = (string) $boolVal('openDefault');
    $f->add($od);

    // Mostrar en
    $so = $modules->get('InputfieldSelect');
    $so->name = 'showOn';
    $so->label = 'Mostrar en';
    $so->addOptions(['all'=>'Todos','desktop'=>'Solo desktop','mobile'=>'Solo móvil']);
    $so->value = $val('showOn');
    $f->add($so);

    // Textos
    foreach ([
      ['label','Etiqueta del botón flotante'],
      ['header','Encabezado del cuadro'],
      ['cta','Texto del botón CTA'],
      ['message','Mensaje por defecto (se envía al abrir)']
    ] as [$name,$label]) {
      $t = $modules->get('InputfieldText');
      $t->name = $name;
      $t->label = $label;
      $t->value = $val($name);
      $f->add($t);
    }

    // Teléfono
    $ph = $modules->get('InputfieldText');
    $ph->name = 'phone';
    $ph->label = 'Teléfono (E.164 sin símbolos, ej. 5213321845734)';
    $ph->value = $val('phone');
    $f->add($ph);

    // Agente
    foreach ([
      ['agent_name','Nombre del agente'],
      ['agent_status','Estado del agente (ej. En línea)'],
      ['agent_description','Descripción del agente (ej. Área)'],
    ] as [$name,$label]) {
      $t = $modules->get('InputfieldText');
      $t->name = $name;
      $t->label = $label;
      $t->value = $val($name);
      $f->add($t);
    }

    $img = $modules->get('InputfieldText');
    $img->name = 'agent_image';
    $img->label = 'URL de imagen del agente (opcional)';
    $img->value = $val('agent_image');
    $img->description = 'Si lo dejas vacío se usa la imagen por defecto del módulo.';
    $f->add($img);

    // Dependencias
    $im = $modules->get('InputfieldCheckboxes');
    $im->name = 'includeMoment';
    $im->label = 'Incluir Moment.js';
    $im->addOptions(['1' => 'Si']);
    $im->value = (string) $boolVal('includeMoment');
    $f->add($im);

    $bi = $modules->get('InputfieldCheckboxes');
    $bi->name = 'includeBootstrapIcons';
    $bi->label = 'Incluir Bootstrap Icons (para iconos del plugin)';
    $bi->addOptions(['1' => 'Si']);
    $bi->value = (string) $boolVal('includeBootstrapIcons');
    $f->add($bi);

    // TZ/now
    $tz = $modules->get('InputfieldText');
    $tz->name = 'timezone';
    $tz->label = 'Zona horaria (IANA, ej. America/Mexico_City)';
    $tz->value = $val('timezone');
    $f->add($tz);

    $nw = $modules->get('InputfieldText');
    $nw->name = 'now';
    $nw->label = 'Fecha/hora manual (YYYY-MM-DD HH:mm:ss, opcional)';
    $nw->value = $val('now');
    $f->add($nw);

    // Depuración
    $dbg = $modules->get('InputfieldCheckboxes');
    $dbg->name = 'debug';
    $dbg->label = 'Depuración (muestra caja .wcs_debug)';
    $dbg->addOptions(['1' => 'Si']);
    $dbg->value = (string) $boolVal('debug');
    $f->add($dbg);

    return $f;
  }
}
