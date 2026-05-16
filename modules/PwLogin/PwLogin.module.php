<?php namespace ProcessWire;

use ProcessWire\HookEvent;
use ProcessWire\InputfieldForm;
use ProcessWire\InputfieldText;
use ProcessWire\InputfieldSubmit;
use ProcessWire\InputfieldHidden;

/**
 * PwLogin — Frontend login/logout con template auth-login.php y validación en backend.
 * - Endpoints (configurables): /login y /logout
 * - Protege prefijos (configurable): /dashboard
 * - Sin notifications; los errores se muestran en el propio form
 */
class PwLogin extends WireData implements Module, ConfigurableModule {

  /** ===================== Module meta ===================== */
  public static function getModuleInfo() {
    return [
      'title'       => 'PwLogin',
      'version'     => 31,
      'summary'     => 'Frontend login/logout con configuración y validación robusta.',
      'autoload'    => true,
      'singular'    => true,
      'author'      => 'Daniel',
      'icon'        => 'sign-in',
      'requires'    => 'ProcessWire>=3.0.173',
    ];
  }

  /** ===================== Defaults (config) ===================== */
  protected function defaults(): array {
    return [
      'loginPath'         => '/login',
      'logoutPath'        => '/logout',
      'successRedirect'   => '/dashboard/',
      'protectedPrefixes' => "/dashboard",     // CSV o líneas
      'redirectParam'     => 'redirect',
      'templateFile'      => 'auth-login.php',      // /site/templates/auth-login.php
    ];
  }

  /** ===================== Lifecycle ===================== */
  public function init() {
    // Mezcla config de usuario sobre defaults
    $cfg = array_merge($this->defaults(), (array) $this->wire('modules')->getConfig($this));
    foreach($cfg as $k => $v) $this->$k = $v;

    // Hooks
    $this->addHookBefore('ProcessPageView::execute', $this, 'protectPrefixes');
    $this->addHookBefore('ProcessPageView::execute', $this, 'routeEndpoints');
  }

  /** ===================== Routing ===================== */
public function routeEndpoints(HookEvent $event) {
  $url = $this->currentPath();
  if($this->isAdminUrl($url)) return;

  // === GUARD: si ya está logueado y visita /login, redirige al dashboard (o ?redirect) ===
  if($this->pathEquals($url, $this->loginPath)) {
    if($this->user->isLoggedin()) {
      $to = (string) ($this->input->get($this->redirectParam) ?: $this->successUrl());
      $this->session->redirect($to);
      $event->replace = true;
      return;
    }
    // no logueado → renderizar el login
    $event->return  = $this->renderLoginTemplate();
    $event->replace = true;
    return;
  }

  if($this->pathEquals($url, $this->logoutPath)) {
    $this->session->logout();
    $this->session->redirect($this->urlRoot());
    $event->replace = true;
    return;
  }
}


  /** ===================== Protect prefixes ===================== */
  public function protectPrefixes(HookEvent $event) {
    $url = $this->currentPath();
    if($this->isAdminUrl($url)) return;

    $prefixes = $this->parsePrefixes($this->protectedPrefixes);
    if(!$prefixes) return;

    foreach($prefixes as $prefix) {
      if($this->startsWith($url, $prefix)) {
        if(!$this->user->isLoggedin()) {
          $redirect = urlencode($url);
          $this->session->redirect(
            $this->urlRoot() . ltrim($this->loginPathNormalized(), '/') . '?' . $this->redirectParam . '=' . $redirect
          );
        }
        return;
      }
    }
  }

  /** ===================== Render template (form + validación inline) ===================== */
  protected function renderLoginTemplate(): string {
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) {
      return "<p style='color:#b00'>Template not found: {$path}</p>";
    }

    /** @var InputfieldForm $form */
    $form = $this->modules->get('InputfieldForm');

    // ---------- Markup + clases Bootstrap 5 ----------
$formMarkup = [
  // Contenedores genéricos
  'list'               => "<div {attrs}>{out}</div>",
  'item'               => "<div {attrs}>{out}</div>",

  // Label
  'item_label'         => "<label class='form-label' for='{for}'>{out}</label>",
  'item_label_hidden'  => "<label class='form-label visually-hidden'><span>{out}</span></label>",

  // Contenido del campo
  // Orden recomendado BS: input -> help -> error -> notes
  'item_content'       => "<div class='{class}'>{out}{description}{error}{notes}</div>",

  // Estados/mensajes
  'item_error'         => "<div class='invalid-feedback d-block'>{out}</div>",
  'item_description'   => "<div class='form-text'>{out}</div>",
  'item_notes'         => "<div class='form-text'>{out}</div>",
  'success'            => "<div class='alert alert-success' role='alert'>{out}</div>",
  'error'              => "<div class='alert alert-danger' role='alert'>{out}</div>",

  // Hidden: sin envolturas extras
  'InputfieldHidden' => [
    'item'         => "{out}",
    'item_label'   => "",
    'item_content' => "{out}",
    'item_notes'   => "",
  ],

  // Fieldset/legend
  'InputfieldFieldset' => [
    'item'              => "<fieldset {attrs} class='mb-3'>{out}</fieldset>",
    'item_label'        => "<legend class='h6 mb-2'>{out}</legend>",
    'item_label_hidden' => "<legend class='visually-hidden'>{out}</legend>",
    'item_content'      => "<div>{out}</div>",
    'item_description'  => "<div class='form-text'>{out}</div>",
    'item_notes'        => "<div class='form-text'>{out}</div>",
  ],

  // Checkbox (envoltura tipo BS)
  'InputfieldCheckbox' => [
    'item'              => "<div {attrs} class='form-check mb-3'>{out}{error}{notes}</div>",
    'item_label'        => "<label class='form-check-label' for='{for}'>{out}</label>",
    'item_label_hidden' => "<label class='form-check-label visually-hidden' for='{for}'>{out}</label>",
    'item_content'      => "<div>{out}{description}</div>",
    'item_description'  => "<div class='form-text ms-4'>{out}</div>",
  ],

  // Radios (grupo estilo BS)
  'InputfieldRadios' => [
    'item'              => "<div {attrs} class='mb-3'>{out}{error}{notes}</div>",
    'item_label'        => "<div class='form-label'>{out}</div>",
    'item_content'      => "<div class='d-grid gap-1'>{out}{description}</div>",
    'item_description'  => "<div class='form-text'>{out}</div>",
  ],
];

$formClasses = [
  // Nada “propio” de PW; solo utilidades BS
  'form'                     => '',        // puedes poner 'row g-3' si usaras grid
  'list'                     => '',
  'list_clearfix'            => '',

  // Cada item con margen inferior BS
  'item'                     => 'mb-3',

  // Estados: deja vacío; Bootstrap pinta con .is-invalid en el input
  'item_required'            => '',
  'item_error'               => '',
  'item_collapsed'           => '',

  // Columnas (si no usas grid, vacías)
  'item_column_width'        => '',
  'item_column_width_first'  => '',

  // Fieldset sin clases de PW
  'InputfieldFieldset'       => [ 'item' => 'mb-3' ],
];


$form->setMarkup($formMarkup);
$form->setClasses($formClasses);
    // ---------- Atributos form ----------
    $form->attr('id', 'login-form');
    $form->attr('method', 'post');
    $form->attr('action', $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'));
    $form->addClass('needs-validation');
    $form->attr('novalidate', 'novalidate');

    // ---------- Hidden redirect ----------
    $redir = (string) $this->input->get($this->redirectParam);
    if($redir !== '') {
      /** @var InputfieldHidden $hidden */
      $hidden = $this->modules->get('InputfieldHidden');
      $hidden->name = $this->redirectParam;
      $hidden->attr('value', $redir);
      $form->add($hidden);
    }

    // ---------- Email/Username ----------
    /** @var InputfieldText $login */
    $login = $this->modules->get('InputfieldText');
    $login->name  = 'login';
    $login->label = 'Email or Username';
    $login->attr('class', 'form-control');
    $login->attr('placeholder', 'you@example.com or username');
    $login->attr('autocomplete', 'username');
    $login->attr('inputmode', 'email');
    $login->attr('spellcheck', 'false');
    $login->attr('autofocus', 'autofocus');
    $login->required = true;
    $login->attr('required', 'required');
    if($this->input->post->login) $login->value = $this->sanitizer->text($this->input->post->login);
    $login->description = 'Ingresa tu email o usuario.';
    $login->appendMarkup = '<div class="invalid-feedback">Este campo es obligatorio.</div>';
    $form->add($login);

    // ---------- Password ----------
    /** @var InputfieldText $pwd */
    $pwd = $this->modules->get('InputfieldText');
    $pwd->name  = 'pass';
    $pwd->label = 'Password';
    $pwd->attr('type', 'password');
    $pwd->attr('class', 'form-control');
    $pwd->attr('placeholder', '');
    $pwd->attr('autocomplete', 'current-password');
    $pwd->attr('data-password', 'true');
    $pwd->required = true;
    $pwd->attr('required', 'required');
    
    $pwd->description = 'Ingresa tu contraseña.';
    $form->add($pwd);

    // ---------- CSRF ----------
    /** @var InputfieldHidden $csrf */
    $csrf = $this->modules->get('InputfieldHidden');
    $csrf->name = $this->session->CSRF->getTokenName();
    $csrf->attr('value', $this->session->CSRF->getTokenValue());
    $form->add($csrf);

    // ---------- Submit ----------
    /** @var InputfieldSubmit $submit */
    $submit = $this->modules->get('InputfieldSubmit');
    $submit->name  = 'submit';
    $submit->value = 'Sign In';
    $submit->attr('class', 'btn btn-primary w-100');
    $form->add($submit);

    // ================== VALIDACIÓN + LOGIN (inline) ==================
    $errors = [];
    $isPost = $this->input->requestMethod('POST'); // robusto contra JS cambiando el botón
    if($isPost) {

      // Procesa reglas de los Inputfields
      $form->processInput($this->input->post);

      // Refuerzo defensivo
      $loginVal = trim((string) $this->input->post->login);
      $passVal  = (string) $this->input->post->pass;

      if($loginVal === '') {
        $login->error('Este campo es obligatorio.');

        $login->addClass('class', ' is-invalid');
        $login->attr('aria-invalid', 'true');
      }
      if($passVal === '') {
        $pwd->error('Este campo es obligatorio.');
        $pwd->attr('class+', ' is-invalid');
        $pwd->attr('aria-invalid', 'true');
      }

      if($form->getErrors()) {
        $form->addClass('was-validated'); // pinta invalid-feedback
      } else {
        // Email -> username si aplica
        $loginName = $loginVal;
        if(strpos($loginVal, '@') !== false) {
          if($email = $this->sanitizer->email($loginVal)) {
            $u = $this->users->get("email=$email, include=all");
            if($u && $u->id) $loginName = $u->name;
          }
        }

        // Intento de login
        if($this->session->login($loginName, $passVal)) {
          $redirectUrl = (string) (
            $this->input->post->{$this->redirectParam}
            ?: $this->input->get($this->redirectParam)
            ?: $this->successRedirect
          );
          $this->session->redirect($redirectUrl);
        } else {
          // Credenciales inválidas: marcar ambos campos
          $msg = 'Email/usuario o contraseña no válidos.';
          $login->error($msg);
          $login->attr('class', ' form-control is-invalid');
          $login->attr('aria-invalid', 'true');

          $pwd->error($msg);
         $login->attr('class', ' form-control is-invalid');
          $pwd->attr('aria-invalid', 'true');

          $form->addClass('was-validated');
          $errors[] = $msg;
        }
      }
    }

    // Render final del form
     

    // Variables para la vista
    $data = [
      'title'        => 'Login',
      'form'         => $form,
      
      'errors'       => $errors, // por si quieres mostrar alert global arriba
      'logoutUrl'    => $this->logoutUrl(),
      'redirect'     => (string) $this->input->get($this->redirectParam),
      'action'       => $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'),
      'rootUrl'      => $this->urlRoot(),
      'templatesUrl' => $this->templatesUrl(),
    ];

    return $this->files->render($path, $data);
  }

  /** ===================== Helpers de routing/paths ===================== */
  protected function parsePrefixes($value): array {
    $value = trim((string) $value);
    if($value === '') return [];
    $value = str_replace([PHP_EOL, ';'], ',', $value);
    $parts = array_filter(array_map('trim', explode(',', $value)));
    return array_map(function($p) {
      if($p === '/') return '/';
      return '/' . ltrim($p, '/');
    }, $parts);
  }

  protected function successUrl(): string {
  $path = (string) $this->successRedirect;
  // permite rutas absolutas (http...) o relativas al root
  if(preg_match('~^https?://~i', $path)) return $path;
  return $this->urlRoot() . ltrim($path, '/');
}

  protected function startsWith(string $url, string $prefix): bool {
    return strpos($url, rtrim($prefix, '/')) === 0;
  }

  protected function pathEquals(string $a, string $b): bool {
    return rtrim($a, '/') === rtrim($b, '/');
  }

  protected function currentPath(): string {
    return '/' . trim($this->input->url(), '/');
  }

  protected function isAdminUrl(string $url): bool {
    return strpos($url, $this->config->urls->admin) === 0;
  }

  protected function urlRoot(): string {
    return $this->config->urls->root;
  }
  protected function templatesPath(): string {
    return $this->config->paths->templates;
  }
  protected function templatesUrl(): string {
    return $this->config->urls->templates;
  }

  protected function loginPathNormalized(): string {
    return '/' . ltrim((string)$this->loginPath, '/');
  }
  protected function logoutPathNormalized(): string {
    return '/' . ltrim((string)$this->logoutPath, '/');
  }

  public function logoutUrl(): string {
    return $this->urlRoot() . ltrim($this->logoutPathNormalized(), '/');
  }

  /** ===================== Module Config UI ===================== */
  public static function getModuleConfigInputfields(array $data) {
    /** @var \ProcessWire\Modules $modules */
    $modules = wire('modules');
    $cfg     = (new self())->defaults();
    $data    = array_merge($cfg, $data);

    $wrap = $modules->get('InputfieldWrapper');

    $f = $modules->get('InputfieldText');
    $f->name  = 'loginPath';
    $f->label = 'Login path';
    $f->value = $data['loginPath'];
    $f->description = 'Ruta pública del endpoint de login (ej. /login)';
    $wrap->add($f);

    $f = $modules->get('InputfieldText');
    $f->name  = 'logoutPath';
    $f->label = 'Logout path';
    $f->value = $data['logoutPath'];
    $f->description = 'Ruta pública del endpoint de logout (ej. /logout)';
    $wrap->add($f);

    $f = $modules->get('InputfieldText');
    $f->name  = 'successRedirect';
    $f->label = 'Redirect después de login exitoso';
    $f->value = $data['successRedirect'];
    $f->description = 'URL a la que se redirige tras login (ej. /dashboard/)';
    $wrap->add($f);

    $f = $modules->get('InputfieldTextarea');
    $f->name  = 'protectedPrefixes';
    $f->label = 'Prefijos protegidos (uno por línea o separados por coma)';
    $f->value = $data['protectedPrefixes'];
    $f->description = "Ej.: /dashboard\n/admin-frontend";
    $wrap->add($f);

    $f = $modules->get('InputfieldText');
    $f->name  = 'redirectParam';
    $f->label = 'Nombre del parámetro de redirect';
    $f->value = $data['redirectParam'];
    $f->description = 'Se usa para recordar a dónde volver tras login (ej. ?redirect=/ruta)';
    $wrap->add($f);

    $f = $modules->get('InputfieldText');
    $f->name  = 'templateFile';
    $f->label = 'Template de login a renderizar';
    $f->value = $data['templateFile'];
    $f->description = 'Ruta relativa en /site/templates (ej. auth-login.php)';
    $wrap->add($f);

    return $wrap;
  }
}
