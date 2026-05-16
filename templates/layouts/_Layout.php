<?php namespace ProcessWire;

// Optional main output file, called after rendering page’s template file. 
// This is defined by $this->config->appendTemplateFile in /site/config.php, and
// is typically used to define and output markup common among most pages.
// 	
// When the Markup Regions feature is used, template files can prepend, append,
// replace or delete any element defined here that has an "id" attribute. 
// https://processwire.com/docs/front-end/output/markup-regions/
	
/** @var Page $page */
/** @var Pages $pages */
/** @var Config $this->config-> */
	
 
$view  ='';

 

// Render de la vista
 
 
?> 
<!DOCTYPE html>
<html lang="en">
  <head id="html-head">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <?php print $page->seo; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Barlow+Semi+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Barlow+Semi+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" /></noscript>




    <?php foreach($this->config->styles as $style): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars((string) $style, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php endforeach; ?>

      <!-- Stylesheet -->
    <?php if($config->production): ?>
      <?php $aiomClass = '\\ProcessWire\\AllInOneMinify'; ?>
      <?php $themeBase = $config->urls->assets . 'themes/' . $config->theme . '/'; ?>
      <link rel="stylesheet" href="<?= $aiomClass::CSS([
        $themeBase . 'vendor/bootstrap/css/bootstrap.min.css',
      ]); ?>" />
    <?php else: ?>
      <link rel="stylesheet" href="<?=$urls->get('tplAssets'); ?>vendor/bootstrap/css/bootstrap.min.css" />

    <?php endif; ?>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
 
    <?php if(!$config->production): ?>
      <!-- Tus estilos en Less (opcional). Escribe en styles/app.less -->
      <link
        rel="stylesheet/less"
        type="text/css"
        href="<?= $urls->get('tplAssets'); ?>styles/main.less"
      />
      <script src="https://cdn.jsdelivr.net/npm/less"></script>
      <script>less.watch();</script>
    <?php endif; ?>

  </head>
  <body>
    <!--layout-->
      <?php print $files->render(PARTIALS.'_loader.php'); ?>
    <?php print $files->render(PARTIALS.'header.view.php');?> 
     
      <?= isset($content) ? $content : ''; ?>


 
    <?php print $files->render(PARTIALS.'footer.view.php');?>
    <!-- Bootstrap 5 JS (con Popper) al final del body -->
 

      <script src="<?=$urls->get('tplAssets'); ?>vendor/jquery-4.0.0.min.js" defer></script>

    <?php $modules->get('JqueryValidate')->includeAssets(); ?>
    <?php foreach($this->config->scripts as $script): ?>
        <script src="<?= htmlspecialchars((string) $script, ENT_QUOTES, 'UTF-8'); ?>" defer></script>
    <?php endforeach; ?>
<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="<?=$urls->get('tplAssets'); ?>vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
 


 
 
  
     <!--End layout-->
  </body>
</html>
