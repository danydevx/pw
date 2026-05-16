<?php namespace ProcessWire;

/**
 * Template: home.php
 * Este archivo genera el contenido principal de la página de inicio.
 * El resultado se inyecta dentro de _main.php (usando $content)
 */

$title = $page->title; // opcional, para el <title> del layout
require_once __DIR__ . '/_seo.php';
$seo = buildSeoData($page, [
  'title' => $title,
  'description' => 'Respuestas a preguntas frecuentes sobre tiempos, procesos y servicios para stands y exhibiciones.',
]);
$view  = VIEWS.  'pages/page-faqs.view.php';

// Renderiza el HTML de la vista dentro de $content
$content = $files->render($view, [
  'page' => $page,
  'title' => $title,
  // puedes pasar variables adicionales aquí
]);

include(LAYOUTS.'_Layout.php');
 
 


?>
 
