<?php namespace ProcessWire;

/**
 * routes.php — Sistema de rutas tipo Laravel con controladores.
 * Requiere ProcessWire >= 3.0.173
 */

/*
function controller($name) {
  $path = wire('config')->paths->root . "site-saas/controllers/{$name}.php";
  if(is_file($path)) require_once $path;
}

// === Registrar rutas ===

// /home
$wire->addHook('/home/?', function($event) {
  controller('HomeController');
  $controller = new \ProcessWire\HomeController();
  echo $controller->index();
  return true; // evita 404
});

// /about
$wire->addHook('/about/?', function($event) {
  controller('HomeController');
  $controller = new \ProcessWire\HomeController();
  echo $controller->about();
  return true;
});

// Ruta con parámetro dinámico /user/{id}
$wire->addHook('/user/{id}', function($event) {
  $id = (int) $event->id;
  $files = wire('files');
  $config = wire('config');

  $content = "<div class='container py-5'>
                <h1>Perfil del usuario #{$id}</h1>
                <p>Contenido dinámico renderizado por hook.</p>
              </div>";

  echo $files->render($config->paths->templates . '_main.php', [
    'title'   => "Usuario #{$id}",
    'content' => $content,
  ]);
  return true;
});


*/


$wire->addHook('/dashboard/profile', function($event) {
  $view  = $this->config->paths->templates . 'dashboard/profile.php';
  return  $this->files->render($view);
  return true; // evita 404
});

 