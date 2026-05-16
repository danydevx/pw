# ProcessWire Site Agent Instructions

## Environment

- **Framework**: ProcessWire CMS (PHP)
- **Debug mode**: Enabled (`$config->debug = true`)
- **Host**: asedisplay.local
- **Database**: ase_pw (MySQL)

## Key Directories

- `/site-ase/modules/` — Custom and third-party ProcessWire modules
- `/site-ase/templates/` — Template files (PHP)
- `/site-ase/assets/` — Site assets (files, cache, etc.)
- `/wire/` — **DO NOT MODIFY** — ProcessWire core

## ProcessWire API Access

Use the `wire()` helper function to access API variables:

```php
wire('pages');
wire('input');
wire('sanitizer');
wire('user');
wire('modules');
wire('config');
```

## Common Patterns

### Getting Input
```php
$input = wire('input');
$value = $input->post->fieldname;      // POST
$value = $input->get->fieldname;       // GET
$value = $input->urlSegment(n);        // URL segments
```

### Sanitizing
```php
$sanitizer = wire('sanitizer');
$text = $sanitizer->text($input->post->name);
$email = $sanitizer->email($input->post->email);
$int = $sanitizer->int($input->post->num);
```

### Module Config Arrays
Module `getModuleConfigInputfields()` receives `$data` array. Always use null coalescing for optional keys:
```php
$field->attr('value', $data['optional_key'] ?? '');
```

## Important Paths

- **FileCompiler cache**: `assets/cache/FileCompiler/` — Compiled PHP modules cached here
- **Module editable source**: `modules/ProcessGeneralSettings/GeneralSettings.module`
- **Config**: `config.php` (database credentials — do not commit changes)

## Debugging

- `bd($variable);` — ProcessWire debug dump (works when debug=true)
- Errors visible to superuser only when debug=false

## Module Development

ProcessWire modules live in `/modules/` with naming pattern `Process*` for Process modules. When editing:
1. Edit source in `/site-ase/modules/[ModuleName]/`
2. FileCompiler caches compiled versions in `/assets/cache/FileCompiler/`
3. Clear cache or disable compile: `$config->templateCompile = false`

# AGENTS.md — ProcessWire Developer Agent

## 🎯 Objetivo

Este agente está especializado en desarrollo con ProcessWire CMS.
Debe generar código limpio, simple, funcional y listo para copiar/pegar.

El enfoque es:
- Backend con ProcessWire (PHP)
- Templates simples
- Módulos reutilizables
- Sin sobreingeniería

---

## ⚙️ Reglas generales

- NO usar frameworks externos innecesarios
- NO usar arquitectura compleja (DDD, repositorios, etc.)
- Código claro, directo y mantenible
- Siempre listo para copiar/pegar
- Evitar dependencias innecesarias
- Usar funciones nativas de ProcessWire

---

## 📁 Estructura esperada

- /site/templates/
- /site/modules/
- /site/assets/
- /wire/ (NO tocar)

---

## 🧠 Convenciones obligatorias

### Acceso a API

wire('pages');
wire('input');
wire('sanitizer');
wire('user');

---

### Sanitización

$name = $sanitizer->text($input->post->name);
$email = $sanitizer->email($input->post->email);

---

### Validaciones

if(!$name) return "Nombre requerido";

---

## 🧩 Módulos

<?php namespace ProcessWire;

class MyModule extends Process {

    public static function getModuleInfo() {
        return [
            'title' => 'My Module',
            'version' => 1,
            'summary' => 'Descripción',
            'autoload' => true
        ];
    }

    public function init() {}

}

---

## 📄 Templates

echo "<h1>{$page->title}</h1>";
echo $page->body;

---

## 📬 Formularios

if($input->post->submit) {

    $name = $sanitizer->text($input->post->name);

    if(!$name) {
        echo "Error";
    } else {
        echo "OK";
    }
}

---

## 🔐 Permisos

if(!$user->hasPermission('edit-pages')) {
    throw new WireException("Sin permisos");
}

---

## ⚠️ NO hacer

- No frameworks externos
- No sobreingeniería
- No lógica compleja en templates

---

## 🧪 Debug

bd($variable);

---

## 📌 Prioridad

1. Simplicidad
2. Funcionalidad
3. Reutilización
4. Claridad

## Tipos de campos del modulos InputfieldForm class
InputfieldText
InputfieldTextarea
InputfieldEmail
InputfieldURL
InputfieldInteger
InputfieldFloat
InputfieldNumber
InputfieldPassword
## Selección y opciones
InputfieldSelect
InputfieldSelectMultiple
InputfieldAsmSelect (multiselect con UI avanzada)
InputfieldCheckbox
InputfieldCheckboxes
InputfieldRadios
## Fecha y tiempo
InputfieldDatetime
InputfieldTime
InputfieldDate (según config / módulos)
## Archivos e imágenes
InputfieldFile
InputfieldImage
## Campos estructurados
InputfieldPage (selector de páginas)
InputfieldPageListSelect
InputfieldPageListSelectMultiple
InputfieldPageAutocomplete
InputfieldPageTable
InputfieldRepeater
InputfieldRepeaterMatrix
## Campos especiales / UI
InputfieldFieldset
InputfieldFieldsetOpen
InputfieldFieldsetClose
InputfieldMarkup (solo mostrar HTML)
InputfieldHidden
InputfieldSubmit
InputfieldButton
InputfieldForm
InputfieldWrapper
## Texto avanzado
InputfieldCKEditor
InputfieldTinyMCE (si usas ese módulo)
InputfieldTextTags (tags estilo input)
## Otros útiles
InputfieldSelector (para queries tipo selector PW)
InputfieldModule (selección de módulos)
InputfieldIcon (selector de íconos)
InputfieldColor (si tienes módulo instalado)
InputfieldToggle (switch on/off)