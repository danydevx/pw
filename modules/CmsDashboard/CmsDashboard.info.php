<?php namespace ProcessWire;

/**
 * Module info: CmsDashboard
 *
 * Define la información básica del módulo, página de administración,
 * permisos requeridos y dependencias.
 */
$info = [
    // Información general del módulo
    'title' => 'CMS Dashboard',
    'summary' => 'Dashboard para administradores y miembros.',
    'version' => 1,
    'author' => 'Daniel L.',
    'href' => '',
    'icon' => 'dashboard',

    // Página que se creará dentro del admin
    'page' => [
        'name' => 'dashboard',
        'parent' => '/admin/',
        'title' => 'Dashboard',
    ],

    // Permiso requerido para acceder al módulo
    'permission' => 'admin-dashboard',

    // Permisos que se instalarán con el módulo
    'permissions' => [
        'admin-dashboard' => 'Acceso al Dashboard',
    ],

    // Dependencias requeridas
    'requires' => [
        'CmsHelpers',
    ],

    // Configuración del módulo
    'configurable' => false,
];