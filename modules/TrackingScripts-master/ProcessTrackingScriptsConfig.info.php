<?php namespace ProcessWire;

$info = [
    'title'      => 'Tracking Scripts Config',
    'version'    => '0.1.0',
    'summary'    => 'Permite configurar el módulo TrackingScripts sin acceso a module-admin.',
    'author'     => 'Lex Sanchez',
    'href'       => "http://www.lexsanchez.com/",
    'permission' => 'tracking-scripts-config',
    'permissions' => [
        'tracking-scripts-config' => 'Acceso a la configuración de Tracking Scripts',
    ],
    'page' => [
        'name'   => 'tracking-scripts-config',
        'parent' => 'setup',
        'title'  => 'Tracking Scripts',
    ],
    'requires'  => ['TrackingScripts'],
    'icon'      => 'line-chart',
];
