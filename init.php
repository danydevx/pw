<?php namespace ProcessWire;

define('VIEWS',$this->config->paths->templates.'views/');
define('PARTIALS',VIEWS.'partials/');
define('SECTIONS',VIEWS.'sections/');
define('PAGES',VIEWS.'pages/');
define('THEMES',$this->config->paths->assets . 'themes/');
define('LAYOUTS',$this->config->paths->templates.'layouts/');
define('HELPERS',$this->config->paths->templates.'helpers/');

$theme = $this->config->theme ?: 'default';
$themePath = 'themes/' . $theme . '/';
$themeUrl = $this->config->urls->assets . $themePath;

$urls->set('theme', $themeUrl);
$urls->set('tplAssets', $themeUrl);
$urls->set('images', $themeUrl . 'images/');

$config->paths->set('theme', $this->config->paths->assets . $themePath);
$config->paths->set('tplAssets', $this->config->paths->assets . $themePath);
$config->paths->set('images', $this->config->paths->assets . $themePath . 'images/');

require_once HELPERS . 'ProcessHelpers/ProcessHelpers.php';

require_once 'routes.php';

 
