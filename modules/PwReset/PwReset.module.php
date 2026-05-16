<?php namespace ProcessWire;

use ProcessWire\HookEvent;
use ProcessWire\InputfieldForm;
use ProcessWire\InputfieldText;
use ProcessWire\InputfieldEmail;
use ProcessWire\InputfieldSubmit;
use ProcessWire\InputfieldHidden;
use ProcessWire\InputfieldCheckbox;

/**
 * PwReset
 * - Endpoints sin página:
 *   - /forgot-password   (solicitud por email)
 *   - /reset-password    (cambio con token)
 *   - /forgot-sent       (pantalla de confirmación de envío)
 *   - /reset-success     (pantalla de confirmación de reseteo)
 * - Renderiza template real /site/templates/auth-reset.php (elige la vista por $view)
 * - No revela existencia de email por defecto (configurable).
 */
class PwReset extends WireData implements Module, ConfigurableModule {

  /** ============== Meta ============== */
  public static function getModuleInfo() {
    return [
      'title'       => 'PwReset',
      'version'     => 4,
      'summary'     => 'Restablecimiento de contraseña (solicitud + reset) con plantilla pública y Bootstrap 5.',
      'autoload'    => true,
      'singular'    => true,
      'author'      => 'Daniel',
      'icon'        => 'refresh',
      'requires'    => 'ProcessWire>=3.0.173',
    ];
  }

  /** ============== Defaults Config ============== */
  protected function defaults(): array {
    return [
      // Rutas públicas
      'forgotPath'        => '/forgot-password',
      'resetPath'         => '/reset-password',
      'sentPath'          => '/forgot-sent',
      'donePath'          => '/reset-success',
      'loginPath'         => '/login',

      // Template y vistas
      'templateFile'      => 'auth-reset.php',

      // Token/expiración
      'tokenField'        => 'reset_token',
      'expiresField'      => 'reset_expires',
      'expiryHours'       => 2,

      // Política de privacidad de email
      'revealEmail'       => 0, // 0: nunca revelar si existe; 1: sí

      // Seguridad/contraseña
      'passwordMin'       => 8,

      // Correo (From, Reply-To, asunto, dominio público)
      'fromEmail'         => '',
      'fromName'          => 'Soporte',
      'replyToEmail'      => '',
      'subjectReset'      => 'Restablece tu contraseña',
      'publicBaseUrl'     => '', // ej. https://midominio.com/
      'successRedirect' => '/dashboard/',
      // Textos
      'sentTitle'         => 'Revisa tu correo',
      'sentMessage'       => 'Si el correo existe en nuestra base, recibirás un enlace para restablecer tu contraseña.',
      'doneTitle'         => 'Contraseña actualizada',
      'doneMessage'       => 'Tu contraseña fue cambiada. Ya puedes iniciar sesión.',
    ];
  }

  /** ============== INIT ============== */
  public function init() {
    $cfg = array_merge($this->defaults(), (array) $this->wire('modules')->getConfig($this));
    foreach($cfg as $k => $v) $this->$k = $v;

    $this->addHookBefore('ProcessPageView::execute', $this, 'routeEndpoints');
  }

  /** ============== Routing ============== */
public function routeEndpoints(HookEvent $event) {
  $url = $this->currentPath();
  if($this->isAdminUrl($url)) return;

  // === GUARD: usuario autenticado → fuera de flujo de reset ===
  $isAuth = $this->user->isLoggedin();
  $isForgot = $this->pathEquals($url, $this->forgotPathNormalized());
  $isReset  = $this->pathEquals($url, $this->resetPathNormalized());
  $isSent   = $this->pathEquals($url, $this->sentPathNormalized());
  $isDone   = $this->pathEquals($url, $this->donePathNormalized());

  if($isForgot || $isReset || $isSent || $isDone) {
    if($isAuth) {
      $this->session->redirect($this->successUrl());
      $event->replace = true;
      return;
    }
  }

  if($isForgot) {
    $event->return  = $this->renderForgotTemplate();
    $event->replace = true;
    return;
  }
  if($isReset) {
    $event->return  = $this->renderResetTemplate();
    $event->replace = true;
    return;
  }
  if($isSent) {
    $event->return  = $this->renderSentTemplate();
    $event->replace = true;
    return;
  }
  if($isDone) {
    $event->return  = $this->renderDoneTemplate();
    $event->replace = true;
    return;
  }
}

  /** ============== FORGOT (request email) ============== */
  protected function renderForgotTemplate(): string {
     if($this->user->isLoggedin()) { $this->session->redirect($this->successUrl()); return ''; }
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) return "<p style='color:#b00'>Template not found: {$path}</p>";

    /** @var InputfieldForm $form */
    $form = $this->modules->get('InputfieldForm');
    [$formMarkup, $formClasses] = $this->bootstrapMarkupClasses();
    $form->setMarkup($formMarkup);
    $form->setClasses($formClasses);

    $form->attr('id', 'forgot-form');
    $form->attr('method', 'post');
    $form->attr('action', $this->urlRoot() . ltrim($this->forgotPathNormalized(), '/'));
    $form->addClass('needs-validation');
    $form->attr('novalidate', 'novalidate');

    // Email
    /** @var InputfieldEmail $email */
    $email = $this->modules->get('InputfieldEmail');
    $email->name  = 'email';
    $email->label = 'Email';
    $email->attr('class', 'form-control');
    $email->attr('autocomplete', 'email');
    $email->attr('placeholder', 'you@example.com');
    $email->required = true;
    $email->attr('required', 'required');
    if($this->input->post->email) $email->value = $this->sanitizer->email($this->input->post->email);
    $email->description = 'Ingresa el correo de tu cuenta.';
    $form->add($email);

    // CSRF
    /** @var InputfieldHidden $csrf */
    $csrf = $this->modules->get('InputfieldHidden');
    $csrf->name = $this->session->CSRF->getTokenName();
    $csrf->attr('value', $this->session->CSRF->getTokenValue());
    $form->add($csrf);

    // Submit
    /** @var InputfieldSubmit $submit */
    $submit = $this->modules->get('InputfieldSubmit');
    $submit->name  = 'submit';
    $submit->value = 'Enviar enlace';
    $submit->attr('class', 'btn btn-primary w-100');
    $form->add($submit);

    // ---- Procesamiento ----
    $isPost = $this->input->requestMethod('POST');
    $errors = [];

    if($isPost) {
      $form->processInput($this->input->post);

      $emailV = $this->sanitizer->email($this->input->post->email);
      if(!$emailV) {
        $email->error('Email inválido.');
        $email->addAttr('class', ' is-invalid');
      }

      if($form->getErrors()) {
        $form->addClass('was-validated');
      } else {
        // No revelar existencia por defecto
        $user = $emailV ? $this->users->get("email=$emailV, include=all") : null;

        if($user && $user->id) {
          $tokenField   = (string) $this->tokenField;
          $expiresField = (string) $this->expiresField;

          $token  = $this->randomToken();
          $expiry = time() + ((int)$this->expiryHours * 3600);

          if($tokenField)   $user->set($tokenField, $token);
          if($expiresField) $user->set($expiresField, $expiry);
          $user->save();

          $this->sendResetEmail($user, $token);
        }

        // Redirige SIEMPRE a la página de "enviado"
        $this->session->redirect($this->urlRoot() . ltrim($this->sentPathNormalized(), '/'));
      }
    }

    $data = [
      'title'          => 'Olvidé mi contraseña',
      'view'           => 'forgot',
      'form'           => $form,
      'templatesUrl'   => $this->templatesUrl(),
      'rootUrl'        => $this->urlRoot(),
      'loginUrl'       => $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'),
    ];

    return $this->files->render($path, $data);
  }

  /** ============== RESET (set new password) ============== */
  protected function renderResetTemplate(): string {
     if($this->user->isLoggedin()) { $this->session->redirect($this->successUrl()); return ''; }
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) return "<p style='color:#b00'>Template not found: {$path}</p>";

    $code = (string) $this->input->get->code;
    $u    = (string) $this->input->get->u;

    $user = null;
    $tokenOK = false;
    $notExpired = false;

    if($code && $u) {
      $user = $this->users->get((int)$u);
      if($user && $user->id) {
        $tokenField   = (string) $this->tokenField;
        $expiresField = (string) $this->expiresField;

        $stored = $tokenField ? (string)$user->get($tokenField) : '';
        $tokenOK = $stored && hash_equals($stored, $code);

        $notExpired = true;
        if($expiresField) {
          $exp = (int)$user->get($expiresField);
          $notExpired = ($exp <= 0) ? true : (time() <= $exp);
        }
      }
    }

    // Si no hay token válido, muestra error directo en la vista
    $tokenValid = ($user && $tokenOK && $notExpired);

    /** @var InputfieldForm $form */
    $form = $this->modules->get('InputfieldForm');
    [$formMarkup, $formClasses] = $this->bootstrapMarkupClasses();
    $form->setMarkup($formMarkup);
    $form->setClasses($formClasses);

    $form->attr('id', 'reset-form');
    $form->attr('method', 'post');
    $form->attr('action', $this->urlRoot() . ltrim($this->resetPathNormalized(), '/')
      . '?code=' . rawurlencode($code) . '&u=' . rawurlencode((string)$u));
    $form->addClass('needs-validation');
    $form->attr('novalidate', 'novalidate');

    // New password
    /** @var InputfieldText $pass */
    $pass = $this->modules->get('InputfieldText');
    $pass->name  = 'password';
    $pass->label = 'Nueva contraseña';
    $pass->attr('type', 'password');
    $pass->attr('class', 'form-control');
    $pass->attr('autocomplete', 'new-password');
    $pass->attr('minlength', (int)$this->passwordMin);
    $pass->required = true;
    $pass->attr('required', 'required');
    $pass->description = 'Mínimo ' . (int)$this->passwordMin . ' caracteres.';
    $form->add($pass);

    // Confirm
    /** @var InputfieldText $pass2 */
    $pass2 = $this->modules->get('InputfieldText');
    $pass2->name  = 'password_confirm';
    $pass2->label = 'Confirmar contraseña';
    $pass2->attr('type', 'password');
    $pass2->attr('class', 'form-control');
    $pass2->attr('autocomplete', 'new-password');
    $pass2->required = true;
    $pass2->attr('required', 'required');
    $form->add($pass2);

    // CSRF
    /** @var InputfieldHidden $csrf */
    $csrf = $this->modules->get('InputfieldHidden');
    $csrf->name = $this->session->CSRF->getTokenName();
    $csrf->attr('value', $this->session->CSRF->getTokenValue());
    $form->add($csrf);

    // Submit
    /** @var InputfieldSubmit $submit */
    $submit = $this->modules->get('InputfieldSubmit');
    $submit->name  = 'submit';
    $submit->value = 'Cambiar contraseña';
    $submit->attr('class', 'btn btn-primary w-100');
    $form->add($submit);

    // ---- Procesamiento ----
    $isPost = $this->input->requestMethod('POST');
    if($isPost && $tokenValid) {
      $form->processInput($this->input->post);

      $pwd1 = (string) $this->input->post->password;
      $pwd2 = (string) $this->input->post->password_confirm;

      if(strlen($pwd1) < (int)$this->passwordMin) {
        $pass->error("La contraseña debe tener al menos {$this->passwordMin} caracteres.");
        $pass->addAttr('class', ' is-invalid');
      }
      if($pwd1 !== $pwd2) {
        $pass2->error('Las contraseñas no coinciden.');
        $pass2->addAttr('class', ' is-invalid');
      }

      if($form->getErrors()) {
        $form->addClass('was-validated');
      } else {
        // Guardar nueva pass y limpiar token
        $tokenField   = (string) $this->tokenField;
        $expiresField = (string) $this->expiresField;

        $user->pass = $pwd1;
        if($tokenField)   $user->set($tokenField, '');
        if($expiresField) $user->set($expiresField, 0);
        $user->save();

        // Redirige a "listo"
        $this->session->redirect($this->urlRoot() . ltrim($this->donePathNormalized(), '/'));
      }
    }

    $data = [
      'title'          => 'Restablecer contraseña',
      'view'           => 'reset',
      'form'           => $form,
      'tokenValid'     => $tokenValid,
      'tokenExpired'   => ($user && $tokenOK && !$notExpired),
      'templatesUrl'   => $this->templatesUrl(),
      'rootUrl'        => $this->urlRoot(),
      'loginUrl'       => $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'),
      'forgotUrl'      => $this->urlRoot() . ltrim($this->forgotPathNormalized(), '/'),
    ];

    return $this->files->render($path, $data);
  }

  /** ============== SENT (after email submit) ============== */
  protected function renderSentTemplate(): string {
     if($this->user->isLoggedin()) { $this->session->redirect($this->successUrl()); return ''; }
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) return "<p style='color:#b00'>Template not found: {$path}</p>";

    $title   = (string) $this->sentTitle ?: 'Revisa tu correo';
    $message = (string) $this->sentMessage ?: 'Si el correo existe, recibirás un enlace.';

    $data = [
      'title'          => $title,
      'view'           => 'sent',
      'message'        => $message,
      'templatesUrl'   => $this->templatesUrl(),
      'rootUrl'        => $this->urlRoot(),
      'loginUrl'       => $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'),
      'forgotUrl'      => $this->urlRoot() . ltrim($this->forgotPathNormalized(), '/'),
    ];

    return $this->files->render($path, $data);
  }

  /** ============== DONE (after reset ok) ============== */
  protected function renderDoneTemplate(): string {
     if($this->user->isLoggedin()) { $this->session->redirect($this->successUrl()); return ''; }
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) return "<p style='color:#b00'>Template not found: {$path}</p>";

    $title   = (string) $this->doneTitle ?: 'Contraseña actualizada';
    $message = (string) $this->doneMessage ?: 'Tu contraseña ha sido cambiada. Ya puedes iniciar sesión.';

    $data = [
      'title'          => $title,
      'view'           => 'done',
      'message'        => $message,
      'templatesUrl'   => $this->templatesUrl(),
      'rootUrl'        => $this->urlRoot(),
      'loginUrl'       => $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'),
    ];

    return $this->files->render($path, $data);
  }

  protected function successUrl(): string {
  $path = (string) $this->successRedirect;
  if(preg_match('~^https?://~i', $path)) return $path; // soporta absolutas
  return $this->urlRoot() . ltrim($path, '/');        // relativas al root
}

  /** ============== Email helper ============== */
  protected function sendResetEmail(User $user, string $token): void {
    if(!$token || !$user->id) return;

    $u    = $user->id;
    $base = $this->baseUrl();
    $link = $base . ltrim($this->resetPathNormalized(), '/') . '?code=' . rawurlencode($token) . '&u=' . $u;

    $mail = wireMail();
    $fromEmail = trim((string)$this->fromEmail) ?: $this->config->adminEmail;
    $fromName  = trim((string)$this->fromName)  ?: 'Soporte';
    $subject   = trim((string)$this->subjectReset) ?: 'Restablece tu contraseña';
    $replyTo   = trim((string)$this->replyToEmail);

    $mail->from($fromEmail, $fromName);
    if($replyTo) $mail->replyTo($replyTo);
    $mail->to($user->email);
    $mail->subject($subject);

    $html  = "<p>Hola {$this->sanitizer->entities($user->name)},</p>";
    $html .= "<p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace:</p>";
    $html .= "<p><a href=\"{$link}\">Restablecer contraseña</a></p>";
    $html .= "<p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>";

    $mail->bodyHTML($html);
    $mail->send();
  }

  /** ============== Open helpers ============== */
  protected function baseUrl(): string {
    $custom = trim((string) $this->publicBaseUrl);
    if($custom) return rtrim($custom, '/') . '/';
    return $this->pages->get(1)->httpUrl;
  }

  protected function bootstrapMarkupClasses(): array {
    $formMarkup = [
      'list'               => "<div {attrs}>{out}</div>",
      'item'               => "<div {attrs}>{out}</div>",
      'item_label'         => "<label class='form-label' for='{for}'>{out}</label>",
      'item_label_hidden'  => "<label class='form-label visually-hidden'><span>{out}</span></label>",
      'item_content'       => "<div class='{class}'>{out}{description}{error}{notes}</div>",
      'item_error'         => "<div class='invalid-feedback d-block'>{out}</div>",
      'item_description'   => "<div class='form-text'>{out}</div>",
      'item_notes'         => "<div class='form-text'>{out}</div>",
      'success'            => "<div class='alert alert-success' role='alert'>{out}</div>",
      'error'              => "<div class='alert alert-danger' role='alert'>{out}</div>",
      'InputfieldHidden' => [
        'item'         => "{out}",
        'item_label'   => "",
        'item_content' => "{out}",
        'item_notes'   => "",
      ],
      'InputfieldFieldset' => [
        'item'              => "<fieldset {attrs} class='mb-3'>{out}</fieldset>",
        'item_label'        => "<legend class='h6 mb-2'>{out}</legend>",
        'item_label_hidden' => "<legend class='visually-hidden'>{out}</legend>",
        'item_content'      => "<div>{out}</div>",
        'item_description'  => "<div class='form-text'>{out}</div>",
        'item_notes'        => "<div class='form-text'>{out}</div>",
      ],
      'InputfieldCheckbox' => [
        'item'              => "<div {attrs} class='form-check mb-3'>{out}{error}{notes}</div>",
        'item_label'        => "<label class='form-check-label' for='{for}'>{out}</label>",
        'item_label_hidden' => "<label class='form-check-label visually-hidden' for='{for}'>{out}</label>",
        'item_content'      => "<div>{out}{description}</div>",
        'item_description'  => "<div class='form-text ms-4'>{out}</div>",
      ],
    ];

    $formClasses = [
      'form'                     => '',
      'list'                     => '',
      'list_clearfix'            => '',
      'item'                     => 'mb-3',
      'item_required'            => '',
      'item_error'               => '',
      'item_collapsed'           => '',
      'item_column_width'        => '',
      'item_column_width_first'  => '',
      'InputfieldFieldset'       => [ 'item' => 'mb-3' ],
    ];
    return [$formMarkup, $formClasses];
  }

  /** ============== Config screen ============== */
  public static function getModuleConfigInputfields(array $data) {
    $modules = wire('modules');
    $cfg     = (new self())->defaults();
    $data    = array_merge($cfg, $data);

    $wrap = $modules->get('InputfieldWrapper');

    // Rutas
    foreach([
      ['forgotPath', 'Ruta: solicitar reset (forgot)', '/forgot-password'],
      ['resetPath',  'Ruta: establecer nueva contraseña (reset)', '/reset-password'],
      ['sentPath',   'Ruta: confirmación de envío', '/forgot-sent'],
      ['donePath',   'Ruta: confirmación de cambio', '/reset-success'],
      ['loginPath',  'Ruta: login', '/login'],
    ] as [$name,$label,$desc]) {
      $f = $modules->get('InputfieldText');
      $f->name = $name;
      $f->label = $label;
      $f->value = $data[$name];
      $f->description = "Ej.: {$desc}";
      $wrap->add($f);
    }

    // Template
    $f = $modules->get('InputfieldText');
    $f->name = 'templateFile';
    $f->label = 'Template a renderizar';
    $f->value = $data['templateFile'];
    $f->description = 'Ruta relativa en /site/templates (ej.: auth-reset.php)';
    $wrap->add($f);

    // Token
    foreach([
      ['tokenField','Campo token (user)', $data['tokenField']],
      ['expiresField','Campo expiración (timestamp) (user)', $data['expiresField']],
    ] as [$name,$label,$value]) {
      $f = $modules->get('InputfieldText');
      $f->name = $name;
      $f->label = $label;
      $f->value = $value;
      $wrap->add($f);
    }

    $f = $modules->get('InputfieldInteger');
    $f->name = 'expiryHours';
    $f->label = 'Horas de validez del enlace';
    $f->value = (int)$data['expiryHours'];
    $wrap->add($f);

    // Password
    $f = $modules->get('InputfieldInteger');
    $f->name = 'passwordMin';
    $f->label = 'Mínimo de caracteres (contraseña nueva)';
    $f->value = (int)$data['passwordMin'];
    $wrap->add($f);

    // Privacidad email
    /** @var InputfieldCheckbox $cb */
    $cb = $modules->get('InputfieldCheckbox');
    $cb->name  = 'revealEmail';
    $cb->label = 'Revelar si el email existe';
    $cb->value = (int)$data['revealEmail'];
    $cb->description = 'Por seguridad se recomienda desactivar (0).';
    $wrap->add($cb);

    // Correo
    foreach([
      ['fromEmail','Remitente (From email)', $data['fromEmail'], 'Si se deja vacío, se usará $config->adminEmail.'],
      ['fromName','Nombre del remitente', $data['fromName'], ''],
      ['replyToEmail','Reply-To (opcional)', $data['replyToEmail'], ''],
      ['publicBaseUrl','Dominio base público (URL absoluta)', $data['publicBaseUrl'], 'Ej.: https://midominio.com/ (si se deja vacío, se infiere).'],
      ['subjectReset','Asunto del email de reseteo', $data['subjectReset'], ''],
    ] as [$name,$label,$value,$desc]) {
      $f = $modules->get('InputfieldText');
      $f->name = $name;
      $f->label = $label;
      $f->value = $value;
      if($desc) $f->description = $desc;
      $wrap->add($f);
    }

    // Textos confirmaciones
    foreach([
      ['sentTitle','Título página enviado', $data['sentTitle']],
      ['sentMessage','Mensaje página enviado', $data['sentMessage']],
      ['doneTitle','Título página listo', $data['doneTitle']],
      ['doneMessage','Mensaje página listo', $data['doneMessage']],
    ] as [$name,$label,$value]) {
      $f = $modules->get('InputfieldText');
      $f->name = $name;
      $f->label = $label;
      $f->value = $value;
      $wrap->add($f);
    }

    return $wrap;
  }

  /** ============== Utils ============== */
  protected function randomToken(int $len = 32): string {
    return bin2hex(random_bytes((int) max(16, $len/2)));
  }

  protected function currentPath(): string { return '/' . trim($this->input->url(), '/'); }
  protected function isAdminUrl(string $url): bool { return strpos($url, $this->config->urls->admin) === 0; }
  protected function pathEquals(string $a, string $b): bool { return rtrim($a, '/') === rtrim($b, '/'); }

  protected function urlRoot(): string { return $this->config->urls->root; }
  protected function templatesPath(): string { return $this->config->paths->templates; }
  protected function templatesUrl(): string { return $this->config->urls->templates; }

  protected function forgotPathNormalized(): string { return '/' . ltrim((string)$this->forgotPath, '/'); }
  protected function resetPathNormalized(): string  { return '/' . ltrim((string)$this->resetPath, '/'); }
  protected function sentPathNormalized(): string   { return '/' . ltrim((string)$this->sentPath, '/'); }
  protected function donePathNormalized(): string   { return '/' . ltrim((string)$this->donePath, '/'); }
  protected function loginPathNormalized(): string  { return '/' . ltrim((string)$this->loginPath, '/'); }
}
