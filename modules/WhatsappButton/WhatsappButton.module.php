<?php namespace ProcessWire;

class WhatsappButton extends WireData implements Module, ConfigurableModule {


  public static function getModuleInfo() {
    return [
      'title' => 'WhatsappButton',
      'version' => 1,
      'summary' => 'Boton flotante de WhatsApp configurable.',
      'author' => 'ASE',
      'autoload' => true,
      'singular' => true,
      'icon' => 'whatsapp',
      'configurable' => true,
    ];
  }

  protected function defaults(): array {
    return [
      'enabled' => 1,
      'includeBootstrapIcons' => 0,
      'phone' => '5213321845739',
      'message' => 'Hola, me gustaria mas informacion.',
      'label' => 'WhatsApp',
      'position' => 'right',
      'displayMode' => 'floating', // floating | inline
      'buttonType' => 'full',      // full | icon
      'size' => 'md',              // sm | md | lg
    ];
  }

  protected function cfg(): array {
    $cfg = (array) $this->modules->getConfig($this);
    $defaults = $this->defaults();
    $merged = array_replace($defaults, $cfg);

    foreach (['enabled', 'includeBootstrapIcons'] as $key) {
      $raw = array_key_exists($key, $cfg) ? $cfg[$key] : ($defaults[$key] ?? 0);
      $merged[$key] = is_array($raw) ? (int) !empty($raw) : (int) ((bool) $raw);
    }

    if (!in_array($merged['displayMode'], ['floating', 'inline'], true)) $merged['displayMode'] = 'floating';
    if (!in_array($merged['buttonType'], ['full', 'icon'], true)) $merged['buttonType'] = 'full';
    if (!in_array($merged['size'], ['sm', 'md', 'lg'], true)) $merged['size'] = 'md';

    return $merged;
  }

  public function init() {
    $cfg = $this->cfg();
    if (!$cfg['enabled']) return;

    $user = $this->wire('user');
    if (!$user || !$user->isGuest()) return;

    $process = $this->wire('process');
    $page = $this->wire('page');
    if ($process instanceof ProcessPageEdit || ($page && $page->template && $page->template->name === 'admin')) {
      return;
    }

    $config = $this->wire('config');
    $config->styles->add($config->urls->siteModules . 'WhatsappButton/WhatsappButton.css');

    // Bootstrap Icons no longer needed: templates now use Font Awesome icons.

    $this->addHookAfter('Page::render', $this, 'injectButton');
  }

  public function injectButton(HookEvent $event) {
    $cfg = $this->cfg();
    if (!$cfg['enabled']) return;

    $out = (string) $event->return;
    if (stripos($out, 'id="WhatsappButton"') !== false) return;

    $button = $this->renderButton();
    if ($button === '') return;

    if (false !== ($p = strripos($out, '</body>'))) {
      $out = substr($out, 0, $p) . $button . "\n" . substr($out, $p);
    } else {
      $out .= $button;
    }

    $event->return = $out;
  }

  public function renderButton(): string {
    $cfg = $this->cfg();
    if (!$cfg['enabled']) return '';

    $user = $this->wire('user');
    if (!$user || !$user->isGuest()) return '';

    $phone = preg_replace('/\D+/', '', (string) $cfg['phone']);
    if ($phone === '') return '';

    $message = trim((string) $cfg['message']);
    $waUrl = 'https://wa.me/' . $phone;
    if ($message !== '') {
      $waUrl .= '?text=' . rawurlencode($message);
    }

    $viewFile = __DIR__ . '/views/button.tpl.php';
    if (!is_file($viewFile)) return '';

    return $this->files->render($viewFile, [
      'waUrl' => $waUrl,
      'label' => (string) $cfg['label'],
      'position' => ($cfg['position'] === 'left') ? 'left' : 'right',
      'displayMode' => (string) $cfg['displayMode'],
      'buttonType' => (string) $cfg['buttonType'],
      'size' => (string) $cfg['size'],
    ]);
  }

  public function getModuleConfigInputfields($f = null) {
    $modules = $this->wire('modules');
    $data = (array) $modules->getConfig($this);
    if (!$f) $f = $modules->get('InputfieldWrapper');

    $d = $this->defaults();
    $val = function($key) use($data,$d){ return $data[$key] ?? $d[$key]; };
    $boolVal = function($key) use($data,$d){
      $raw = array_key_exists($key, $data) ? $data[$key] : ($d[$key] ?? 0);
      return is_array($raw) ? (int) !empty($raw) : (int) ((bool) $raw);
    };

    $en = $modules->get('InputfieldCheckboxes');
    $en->name = 'enabled';
    $en->label = 'Activar boton';
    $en->addOptions(['1' => 'Si']);
    $en->value = (string) $boolVal('enabled');
    $f->add($en);

    $bi = $modules->get('InputfieldCheckboxes');
    $bi->name = 'includeBootstrapIcons';
    $bi->label = 'Incluir Bootstrap Icons';
    $bi->addOptions(['1' => 'Si']);
    $bi->value = (string) $boolVal('includeBootstrapIcons');
    $f->add($bi);

    $ph = $modules->get('InputfieldText');
    $ph->name = 'phone';
    $ph->label = 'Numero WhatsApp (E.164 sin simbolos)';
    $ph->attr('value', $val('phone'));
    $f->add($ph);

    $msg = $modules->get('InputfieldText');
    $msg->name = 'message';
    $msg->label = 'Mensaje por defecto';
    $msg->attr('value', $val('message'));
    $f->add($msg);

    $label = $modules->get('InputfieldText');
    $label->name = 'label';
    $label->label = 'Etiqueta del boton';
    $label->attr('value', $val('label'));
    $f->add($label);

    $pos = $modules->get('InputfieldSelect');
    $pos->name = 'position';
    $pos->label = 'Posicion';
    $pos->addOptions(['right' => 'Derecha', 'left' => 'Izquierda']);
    $pos->value = $val('position');
    $f->add($pos);

    $mode = $modules->get('InputfieldSelect');
    $mode->name = 'displayMode';
    $mode->label = 'Modo del boton';
    $mode->addOptions(['floating' => 'Flotante', 'inline' => 'Fijo (normal)']);
    $mode->value = $val('displayMode');
    $f->add($mode);

    $type = $modules->get('InputfieldSelect');
    $type->name = 'buttonType';
    $type->label = 'Tipo de boton';
    $type->addOptions(['full' => 'Icono + texto', 'icon' => 'Solo icono (redondo)']);
    $type->value = $val('buttonType');
    $f->add($type);

    $size = $modules->get('InputfieldSelect');
    $size->name = 'size';
    $size->label = 'Tamano';
    $size->addOptions(['sm' => 'Chico', 'md' => 'Mediano', 'lg' => 'Grande']);
    $size->value = $val('size');
    $f->add($size);

    $usage = $modules->get('InputfieldMarkup');
    $usage->label = 'Snippet de uso';
    $usage->value = "<p>Si prefieres render manual en template:</p>\n"
      . "<pre><code>&lt;?php namespace ProcessWire;\n"
      . "\$wa = wire('modules')-&gt;get('WhatsappButton');\n"
      . "if(\$wa) echo \$wa-&gt;renderButton();\n"
      . "?&gt;</code></pre>";
    $f->add($usage);

    return $f;
  }
}
