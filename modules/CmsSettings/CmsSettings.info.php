<?php namespace ProcessWire;

$info = [
    'title' => 'CMS Settings',
    'summary' => 'Pagina admin para configuracion general del sitio.',
    'version' => 1,
    'author' => 'Daniel L.',
    'icon' => 'cog',
    'singular' => true,
    'autoload' => false,
    'requires' => [
        'JqueryValidate',
        'JqueryMagnificPressets',
    ],
    'permission' => 'admin-settings',
    
    
    'page' => [
        'name' => 'settings',
        'parent' => 'admin',
        'title' => 'Settings',
    ],
    'permissions' => [
        'admin-settings' => 'Acceso al Dashboard',
    ],
      // Configuración del módulo
    'configurable' => false,

];
 