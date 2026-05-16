<?php namespace ProcessWire;

/**
 * Template: home.php
 * Este archivo genera el contenido principal de la página de inicio.
 * El resultado se inyecta dentro de _main.php (usando $content)
 */

$title = $page->title; // opcional, para el <title> del layout
$fld_template  = $page->fld_template;
if(!empty($fld_template)){
  print $files->render($config->paths->templates.'pages/'.$fld_template);
}else{
   print $files->render($config->paths->templates.'pages/pageDefault.php');
}


?>
 