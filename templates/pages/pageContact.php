<?php namespace ProcessWire;

$success = false;
$error = '';

 
 


/**
 * Template: home.php
 * Este archivo genera el contenido principal de la página de inicio.
 * El resultado se inyecta dentro de _main.php (usando $content)
 */

$title = $page->title; // opcional, para el <title> del layout
 
$view  = PAGES.  'page-contact.view.php';

// Renderiza el HTML de la vista dentro de $content
$content = $files->render($view, [
  'page' => $page,
  'title' => $title,
  'success'=> $success,
  'error'=>$error
  // puedes pasar variables adicionales aquí
]);

include(LAYOUTS.'_Layout.php');
