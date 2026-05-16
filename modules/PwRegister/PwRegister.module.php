<?php namespace ProcessWire;

use ProcessWire\HookEvent;
use ProcessWire\InputfieldForm;
use ProcessWire\InputfieldText;
use ProcessWire\InputfieldEmail;
use ProcessWire\InputfieldCheckbox;
use ProcessWire\InputfieldSubmit;
use ProcessWire\InputfieldHidden;

class PwRegister extends WireData implements Module, ConfigurableModule {

  public static function getModuleInfo() {
    return [
      'title'       => 'PwRegister',
      'version'     => 2,
      'summary'     => 'Frontend register + activate con plantilla pública y Bootstrap 5. (scripts cargados en la vista)',
      'autoload'    => true,
      'singular'    => true,
      'author'      => 'Daniel',
      'icon'        => 'user-plus',
      'requires'    => 'ProcessWire>=3.0.173',
    ];
  }

  protected function defaults(): array {
    return [
      'registerPath'       => '/register',
      'activatePath'       => '/activate',
      'loginPath'          => '/login',
      'successRedirect'    => '/dashboard/',
      'templateFile'       => 'auth-register.php',
      'assignRoles'        => 'guest',
      'userTemplate'       => 'user',
      'usernameMin'        => 3,
      'passwordMin'        => 8,
      'tokenField'         => 'activation_token',
      'expiresField'       => 'activation_expires',
      'expiryHours'        => 48,
        'fromEmail'          => '',
    'fromName'           => 'Registro',
    'replyToEmail'       => '',
    'subjectActivation'  => 'Activa tu cuenta',
    'publicBaseUrl'      => '', // ej. https://midominio.com/
    ];
  }

  public function init() {
    $cfg = array_merge($this->defaults(), (array) $this->wire('modules')->getConfig($this));
    foreach($cfg as $k => $v) $this->$k = $v;
    $this->addHookBefore('ProcessPageView::execute', $this, 'routeEndpoints');
  }

public function routeEndpoints(HookEvent $event) {
  $url = $this->currentPath();
  if($this->isAdminUrl($url)) return;

  if($this->pathEquals($url, $this->registerPath)) {
    // === GUARD: si ya está logueado, no mostrar register; manda al dashboard ===
    if($this->user->isLoggedin()) {
      $this->session->redirect($this->successUrl());
      $event->replace = true;
      return;
    }
    $event->return  = $this->renderRegisterTemplate();
    $event->replace = true;
    return;
  }

  if($this->pathEquals($url, $this->activatePath)) {
    $event->return  = $this->renderActivateTemplate();
    $event->replace = true;
    return;
  }
}

  protected function renderRegisterTemplate(): string {
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) return "<p style='color:#b00'>Template not found: {$path}</p>";

    /** @var InputfieldForm $form */
    $form = $this->modules->get('InputfieldForm');

    // Markup/clases 100% Bootstrap
    [$formMarkup, $formClasses] = $this->bootstrapMarkupClasses();
    $form->setMarkup($formMarkup);
    $form->setClasses($formClasses);

    // Atributos form
    $form->attr('id', 'register-form');
    $form->attr('method', 'post');
    $form->attr('action', $this->urlRoot() . ltrim($this->registerPathNormalized(), '/'));
    $form->addClass('needs-validation');
    $form->attr('novalidate', 'novalidate');

    // Username
    /** @var InputfieldText $username */
    $username = $this->modules->get('InputfieldText');
    $username->name  = 'username';
    $username->label = 'Usuario';
    $username->attr('class', 'form-control');
    $username->attr('autocomplete', 'username');
    $username->attr('placeholder', 'tu_usuario');
    $username->required = true;
    $username->attr('required', 'required');
    if($this->input->post->username) $username->value = $this->sanitizer->text($this->input->post->username);
    $username->description = 'Mínimo ' . (int)$this->usernameMin . ' caracteres. Solo letras, números y guion bajo.';
    $form->add($username);

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
    $email->description = 'Usaremos este correo para activar tu cuenta.';
    $form->add($email);

    // Password (CUSTOM) => InputfieldText con type=password
    /** @var InputfieldText $pass */
    $pass = $this->modules->get('InputfieldText');
    $pass->name  = 'password';
    $pass->label = 'Contraseña';
    $pass->attr('type', 'password');                 // <-- clave
    $pass->attr('class', 'form-control');
    $pass->attr('autocomplete', 'new-password');
    $pass->attr('minlength', (int)$this->passwordMin);
    // opcional: patrón de complejidad; comenta si no lo quieres
    // $pass->attr('pattern', '^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d\W]{8,}$');
    $pass->required = true;
    $pass->attr('required', 'required');
    $pass->description = 'Mínimo ' . (int)$this->passwordMin . ' caracteres.';
    // agrega un feedback inline
    $pass->appendMarkup = '<div class="invalid-feedback">Contraseña demasiado corta.</div>';
    $form->add($pass);

    // Confirm (CUSTOM) => InputfieldText con type=password
    /** @var InputfieldText $pass2 */
    $pass2 = $this->modules->get('InputfieldText');
    $pass2->name  = 'password_confirm';
    $pass2->label = 'Confirmar contraseña';
    $pass2->attr('type', 'password');                // <-- clave
    $pass2->attr('class', 'form-control');
    $pass2->attr('autocomplete', 'new-password');
    $pass2->required = true;
    $pass2->attr('required', 'required');
    $pass2->appendMarkup = '<div class="invalid-feedback">Las contraseñas no coinciden.</div>';
    $form->add($pass2);

    // Terms
    /** @var InputfieldCheckbox $terms */
    $terms = $this->modules->get('InputfieldCheckbox');
    $terms->name  = 'terms';
    $terms->label = 'Acepto términos y condiciones';
    $terms->attr('class', 'form-check-input');
    $terms->appendMarkup = '<div class="invalid-feedback">Debes aceptar los terminos</div>';
    $terms->required = true;
    $form->add($terms);

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
    $submit->value = 'Crear cuenta';
    $submit->attr('class', 'btn btn-primary w-100');
    $form->add($submit);

    // ---------- Validación + creación ----------
    $isPost = $this->input->requestMethod('POST');
    $created = false;
    $activationInfo = [];
    $errors = [];

    if($isPost) {
      $form->processInput($this->input->post);

      $nameRaw = (string) $this->input->post->username;
      $name    = $this->sanitizer->pageName($nameRaw, true);
      $emailV  = $this->sanitizer->email($this->input->post->email);
      $pwd1    = (string) $this->input->post->password;
      $pwd2    = (string) $this->input->post->password_confirm;
      $termsOk = (bool) $this->input->post->terms;

      if(strlen($name) < (int)$this->usernameMin) {
        $username->error("El usuario debe tener al menos {$this->usernameMin} caracteres.");
        $username->attr('class+', ' is-invalid');
      }
      if(!$emailV) {
        $email->error('Email inválido.');
        $email->attr('class+', ' is-invalid');
      }
      if(strlen($pwd1) < (int)$this->passwordMin) {
        $pass->error("La contraseña debe tener al menos {$this->passwordMin} caracteres.");
        $pass->attr('class+', ' is-invalid');
      }
      if($pwd1 !== $pwd2) {
        $pass2->error('Las contraseñas no coinciden.');
        $pass2->attr('class+', ' is-invalid');
      }
      if(!$termsOk) {
        $terms->error('Debes aceptar los términos.');
      }

      if($name && $this->users->get("name=$name")->id) {
        $username->error('Este usuario ya existe.');
        $username->attr('class+', ' is-invalid');
      }
      if($emailV && $this->users->get("email=$emailV, include=all")->id) {
        $email->error('Este email ya está registrado.');
        $email->attr('class+', ' is-invalid');
      }

      if($form->getErrors()) {
        $form->addClass('was-validated');
      } else {
        $user = new User();
        $user->template = $this->userTemplate ?: 'user';
        $user->name = $name;
        $user->email = $emailV;
        $user->pass = $pwd1; // ProcessWire lo hashea al guardar

        foreach($this->parseCSV($this->assignRoles) as $roleName) {
          $role = $this->roles->get($roleName);
          if($role && $role->id) $user->addRole($role);
        }

        $tokenField   = (string) $this->tokenField;
        $expiresField = (string) $this->expiresField;
        $token  = $this->randomToken();
        $expiry = time() + ((int)$this->expiryHours * 3600);

        if($tokenField)   $user->set($tokenField, $token);
        if($expiresField) $user->set($expiresField, $expiry);

        $user->save();
        $this->sendActivationEmail($user, $token);

        $created = true;
        $activationInfo = ['email' => $emailV];
      }
    }

    // Render (sin scripts desde el módulo)
    $data = [
      'title'          => 'Registro',
      'form'           => $form,
      'created'        => $created,
      'activationInfo' => $activationInfo,
      'loginUrl'       => $this->urlRoot() . ltrim($this->loginPathNormalized(), '/'),
      'templatesUrl'   => $this->templatesUrl(),
      'rootUrl'        => $this->urlRoot(),
      'view'           => 'register',
    ];

    return $this->files->render($path, $data);
  }

  protected function renderActivateTemplate(): string {
    $path = $this->templatesPath() . $this->templateFile;
    if(!is_file($path)) return "<p style='color:#b00'>Template not found: {$path}</p>";

    $code = (string) $this->input->get->code;
    $u    = (string) $this->input->get->u;

    $ok = false;
    $message = 'Código de activación inválido.';
    $loginUrl = $this->urlRoot() . ltrim($this->loginPathNormalized(), '/');

    if($code && $u) {
      $user = $this->users->get((int)$u);
      if($user && $user->id) {
        $tokenField   = (string) $this->tokenField;
        $expiresField = (string) $this->expiresField;

        $tokenOK = $tokenField ? (string)$user->get($tokenField) === $code : false;
        $notExpired = true;

        if($expiresField) {
          $exp = (int)$user->get($expiresField);
          $notExpired = ($exp <= 0) ? true : (time() <= $exp);
        }

        if($tokenOK && $notExpired) {
          if($tokenField)   $user->set($tokenField, '');
          if($expiresField) $user->set($expiresField, 0);
          $user->save();

          $ok = true;
          $message = 'Tu cuenta ha sido activada. Ya puedes iniciar sesión.';
        } else if($tokenOK && !$notExpired) {
          $message = 'Tu enlace de activación ha expirado. Solicita uno nuevo.';
        }
      }
    }

    $data = [
      'title'       => 'Activación de cuenta',
      'ok'          => $ok,
      'message'     => $message,
      'loginUrl'    => $loginUrl,
      'templatesUrl'=> $this->templatesUrl(),
      'rootUrl'     => $this->urlRoot(),
      'view'        => 'activate',
    ];

    return $this->files->render($path, $data);
  }

  protected function baseUrl(): string {
  $custom = trim((string) $this->publicBaseUrl);
  if($custom) return rtrim($custom, '/') . '/';
  // Fallback: URL absoluta de la home
  return $this->pages->get(1)->httpUrl; // incluye esquema y dominio correctos
}

protected function successUrl(): string {
  $path = (string) $this->successRedirect;
  if(preg_match('~^https?://~i', $path)) return $path; // soporta absolutas
  return $this->urlRoot() . ltrim($path, '/');        // relativas al root
}


protected function sendActivationEmail(User $user, string $token): void {
  if(!$token || !$user->id) return;

  $u    = $user->id;
  $base = $this->baseUrl(); // <--
  $link = $base . ltrim($this->activatePathNormalized(), '/') . '?code=' . rawurlencode($token) . '&u=' . $u;

  $mail = wireMail();
  $fromEmail = trim((string)$this->fromEmail) ?: $this->config->adminEmail;
  $fromName  = trim((string)$this->fromName)  ?: 'Registro';
  $subject   = trim((string)$this->subjectActivation) ?: 'Activa tu cuenta';
  $replyTo   = trim((string)$this->replyToEmail);

  $mail->from($fromEmail, $fromName);
  if($replyTo) $mail->replyTo($replyTo);
  $mail->to($user->email);
  $mail->subject($subject);

  $html  = "<p>Hola {$this->sanitizer->entities($user->name)},</p>";
  $html .= "<p>Para activar tu cuenta, haz clic en el siguiente enlace:</p>";
  $html .= "<p><a href=\"{$link}\">Activar cuenta</a></p>";
  $html .= "<p>Si no has solicitado esta cuenta, puedes ignorar este mensaje.</p>";

  $mail->bodyHTML($html);
  $mail->send();
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

  public static function getModuleConfigInputfields(array $data) {
  $modules = wire('modules');
  $cfg     = (new self())->defaults();
  $data    = array_merge($cfg, $data);

  $wrap = $modules->get('InputfieldWrapper');

  // Rutas
  $f = $modules->get('InputfieldText');
  $f->name = 'registerPath';
  $f->label = 'Ruta de registro';
  $f->value = $data['registerPath'];
  $f->description = 'Ej.: /register';
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'activatePath';
  $f->label = 'Ruta de activación';
  $f->value = $data['activatePath'];
  $f->description = 'Ej.: /activate';
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'loginPath';
  $f->label = 'Ruta de login';
  $f->value = $data['loginPath'];
  $f->description = 'Ej.: /login';
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'successRedirect';
  $f->label = 'Redirect después de registro';
  $f->value = $data['successRedirect'];
  $f->description = 'Ej.: /dashboard/';
  $wrap->add($f);

  // Plantilla
  $f = $modules->get('InputfieldText');
  $f->name = 'templateFile';
  $f->label = 'Template a renderizar';
  $f->value = $data['templateFile'];
  $f->description = 'Ruta relativa en /site/templates (ej.: auth-register.php)';
  $wrap->add($f);

  // Campos/roles
  $f = $modules->get('InputfieldText');
  $f->name = 'assignRoles';
  $f->label = 'Roles a asignar';
  $f->value = $data['assignRoles'];
  $f->description = 'CSV o líneas. Ej.: guest,member';
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'userTemplate';
  $f->label = 'Template de usuario';
  $f->value = $data['userTemplate'];
  $wrap->add($f);

  $f = $modules->get('InputfieldInteger');
  $f->name = 'usernameMin';
  $f->label = 'Mínimo de caracteres (usuario)';
  $f->value = (int) $data['usernameMin'];
  $wrap->add($f);

  $f = $modules->get('InputfieldInteger');
  $f->name = 'passwordMin';
  $f->label = 'Mínimo de caracteres (contraseña)';
  $f->value = (int) $data['passwordMin'];
  $wrap->add($f);

  // Activación
  $f = $modules->get('InputfieldText');
  $f->name = 'tokenField';
  $f->label = 'Campo token de activación (user)';
  $f->value = $data['tokenField'];
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'expiresField';
  $f->label = 'Campo expiración (timestamp) (user)';
  $f->value = $data['expiresField'];
  $wrap->add($f);

  $f = $modules->get('InputfieldInteger');
  $f->name = 'expiryHours';
  $f->label = 'Horas de validez del enlace';
  $f->value = (int) $data['expiryHours'];
  $wrap->add($f);

  // Correo (remitente / dominio / asunto)
  $f = $modules->get('InputfieldEmail');
  $f->name = 'fromEmail';
  $f->label = 'Remitente (From email)';
  $f->value = $data['fromEmail'];
  $f->description = 'Si se deja vacío, se usará $config->adminEmail.';
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'fromName';
  $f->label = 'Nombre del remitente';
  $f->value = $data['fromName'];
  $wrap->add($f);

  $f = $modules->get('InputfieldEmail');
  $f->name = 'replyToEmail';
  $f->label = 'Reply-To (opcional)';
  $f->value = $data['replyToEmail'];
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'publicBaseUrl';
  $f->label = 'Dominio base público (URL absoluta)';
  $f->value = $data['publicBaseUrl'];
  $f->description = 'Ej.: https://midominio.com/ (si se deja vacío, se infiere del sitio).';
  $wrap->add($f);

  $f = $modules->get('InputfieldText');
  $f->name = 'subjectActivation';
  $f->label = 'Asunto del email de activación';
  $f->value = $data['subjectActivation'];
  $wrap->add($f);

  return $wrap;
}


  protected function parseCSV($value): array {
    $value = trim((string)$value);
    if($value === '') return [];
    $value = str_replace([PHP_EOL, ';'], ',', $value);
    return array_filter(array_map('trim', explode(',', $value)));
  }

  protected function randomToken(int $len = 32): string {
    return bin2hex(random_bytes((int) max(16, $len/2)));
  }

  protected function currentPath(): string { return '/' . trim($this->input->url(), '/'); }
  protected function isAdminUrl(string $url): bool { return strpos($url, $this->config->urls->admin) === 0; }
  protected function pathEquals(string $a, string $b): bool { return rtrim($a, '/') === rtrim($b, '/'); }

  protected function urlRoot(): string { return $this->config->urls->root; }
  protected function templatesPath(): string { return $this->config->paths->templates; }
  protected function templatesUrl(): string { return $this->config->urls->templates; }

  protected function registerPathNormalized(): string { return '/' . ltrim((string)$this->registerPath, '/'); }
  protected function activatePathNormalized(): string { return '/' . ltrim((string)$this->activatePath, '/'); }
  protected function loginPathNormalized(): string { return '/' . ltrim((string)$this->loginPath, '/'); }
}
