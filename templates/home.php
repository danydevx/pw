<?php namespace ProcessWire;

/**
 * Template: home.php
 * Este archivo genera el contenido principal de la página de inicio.
 * El resultado se inyecta dentro de _Layout.php (usando $content)
 * 
6 horas 21600
12 horas 43200
1 día 86400
1 semana 604800
1 mes (30 días aprox) 2592000
1 año (365 días) 31536000
 */

$title = $page->title; // opcional, para el <title> del layout
 
$view  = VIEWS.  'pages/page-front.view.php';

// Renderiza el HTML de la vista dentro de $content
$content = $files->render($view, [
  'page' => $page,
  'title' => $title,
  'cache' => 86400
  // puedes pasar variables adicionales aquí
]);

 


include(LAYOUTS.'_Layout.php');
 
?>
 