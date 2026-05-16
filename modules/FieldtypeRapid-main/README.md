# Rapid

EditorJS block editor fieldtype for ProcessWire. Stores content as JSON, renders HTML server-side via pluggable PHP block renderers.

## Requirements

- ProcessWire >= 3.0.200
- PHP >= 8.2

## Installation

1. Copy the `Rapid/` folder to `site/modules/`.
2. In the admin go to **Modules → Refresh → install Rapid**.  
   This automatically installs `InputfieldRapid`, `ProcessFieldtypeRapid`, `ProcessRapid`, and `ProcessRapidFrontend`.
3. Add a field of type **Rapid** to any template.

No build step required — a pre-built `js/dist/editor.js` is included.

## Template usage

```php
// Render all blocks
echo $page->body;
echo $page->body->render();

// Restrict to specific block types
$r = new RapidRenderer(['allowedBlocks' => ['paragraph', 'header', 'image']]);
echo $page->body->renderWith($r);

// Single block by index
echo $page->body->renderBlock(0);

// Plain text — for meta descriptions, search indexing
$description = mb_substr($page->body->toText(), 0, 160);

// Raw JSON
$json = $page->body->toJSON();

// From a raw JSON string (static shortcut)
echo RapidRenderer::fromJSON($json, $page);

// Check if empty
if (!$page->body->isEmpty()) { ... }
```

## Supported blocks

### Block tools

| Type | JS package | Notes |
|---|---|---|
| `paragraph` | built-in | `large` variant supported |
| `header` | @editorjs/header | h1–h6, auto-generates anchor ID |
| `quote` | @editorjs/quote | Caption optional |
| `nestedList` | @editorjs/nested-list | Ordered + unordered, recursive |
| `table` | @editorjs/table | Optional header row |
| `code` | @editorjs/code | Monospace, scroll overflow |
| `delimiter` | @editorjs/delimiter | `<hr>` |
| `warning` | @editorjs/warning | Title + message box |
| `checklist` | @editorjs/checklist | Read-only checkboxes on output |
| `raw` | @editorjs/raw | HTML pass-through |
| `image` | @editorjs/image | Uploads to page files directory, optional WebP + resize |
| `attaches` | @editorjs/attaches | File download block |
| `embed` | Custom | YouTube + Vimeo via URL paste, `youtube-nocookie.com` |
| `alert` | editorjs-alert | 8 color variants |
| `toggle` | editorjs-toggle-block | Spoiler / accordion via `<details>` |
| `linkTool` | @editorjs/link | Link preview with OG metadata |

### Inline tools

| Type | JS package |
|---|---|
| `inlineCode` | @editorjs/inline-code |
| `marker` | @editorjs/marker |
| `underline` | @editorjs/underline |
| `bold` | built-in |
| `italic` | built-in |
| `link` | built-in |

### Plugins

| Plugin | Notes |
|---|---|
| editorjs-drag-drop | Drag blocks by settings handle |
| editorjs-undo | Ctrl+Z / Ctrl+Y |

## Field configuration

In **Setup → Fields → your field → Details**:

### Editor behaviour
- **Allowed block types** — restrict available blocks. Leave all unchecked to allow every type.
- **Min height (px)** — minimum height of the editor area (default: 200).
- **Placeholder text** — shown when editor is empty.
- **Editor alignment** — `Left`, `Center`, or `Full width`.
- **Toolbar position** — `Left` (default) or `Right`.
- **Autosave debounce (ms)** — delay between typing and saving to the hidden textarea (default: 300).

### Header block
- **Allowed heading levels** — restrict which h-levels appear in the toolbar.
- **Default level** — pre-selected level when inserting a new header.

### File uploads
- **Max upload size (MB)** — applies to images and file attachments.
- **Default image width / height (px)** — resize all images on render via `Pageimage::size()`. 0 = no resize.
- **Image options** — Convert to WebP, Crop to fit.
- **Allowed image types** — JPEG, PNG, GIF, WebP, SVG. Leave all unchecked to allow all types.
- **Allowed file extensions** — comma-separated list (e.g. `pdf,doc,xlsx,zip`). Leave empty to allow all.

### Output / rendering
- **Enable frontend editing** — render an inline Rapid editor on the frontend for authorized users.
- **Who can edit** — permission required to use the frontend editor (default: `page-edit`).
- **CSS framework** — `Vanilla (rapid-*)`, `Tailwind CSS`, `Bootstrap 5`, or `UIkit 3`.
- **Output wrapper CSS class** — added to the wrapping `<div>`. Useful for scoped CSS (e.g. `prose`).

### Inline toolbar
- **Visible inline tools** — restrict which inline tools appear in the text selection toolbar.

## Frontend editing

Enable per-field in **Output / rendering**. Requires `ProcessRapidFrontend` module (auto-installed with Rapid).

```php
$editor = $modules->get('ProcessRapidFrontend');

// Auto: editor for authorized users, plain HTML for others
echo $editor->renderField($page, 'body');

// Manual control
if ($editor->canEdit($page, 'body')) {
    echo $editor->editorFor($page, 'body');
} else {
    echo $page->body;
}
```

- Save requests POST to `/rapid-save/` (frontend URL hook — no admin URL needed).
- Protected by HMAC nonce (no separate CSRF token required).
- JS bundle and CSS are injected automatically on first `editorFor()` call.

## CSS frameworks

```php
// Output with Tailwind classes (set in field config, applied automatically)
echo $page->body;

// Override per render call
echo $page->body->render(['outputFramework' => 'bootstrap']);
```

Class mappings are defined in `RapidFrameworks.php`.

## Frontend styles

Include `rapid.css` for default vanilla styling:

```php
$config->styles->add($config->urls->FieldtypeRapid . 'rapid.css');
```

### Dark mode

`rapid.css` and `js/editor.css` are fully integrated with the **AdminThemeUikit Design System**.  
All colors reference `--pw-*` CSS custom properties and adapt automatically via the CSS `light-dark()` function — no extra classes or `prefers-color-scheme` media queries needed.

Required: `--pw-*` variables must be defined on `:root` (provided by AdminThemeUikit's `admin-custom.css` or equivalent). Rapid-specific tokens (`--rapid-*`) are declared inside `rapid.css` and can be overridden per-project:

```css
:root {
    --rapid-accent: var(--pw-main-color);     /* quote border, checklist checkbox */
    --rapid-code-bg: light-dark(#1e1e2e, #0d0d1a);
    /* … see rapid.css :root block for full list */
}
```

## Custom block types

```php
<?php namespace ProcessWire;

class RapidBlockMyType extends RapidBlockAbstract {
    public static function render(array $block, ?Page $page = null, ?Field $field = null): string {
        $text = htmlspecialchars(strip_tags($block['data']['text'] ?? ''));
        return "<div " . self::attr($block) . ">$text</div>";
    }
    public static function toText(array $block): string {
        return strip_tags($block['data']['text'] ?? '');
    }
}
```

Auto-discovered — no registration needed. Type key = `lcfirst` of the class suffix.

## Upload endpoints

Mounted at `/[admin]/setup/rapid-upload/`:

| Route | Method | Description |
|---|---|---|
| `upload/` | POST | Image upload |
| `attach/` | POST | File upload |
| `link/?url=` | GET | Fetch OG metadata |
| `save/` | POST | Frontend save (nonce-protected) |

## Building the JS bundle

```bash
cd site/modules/Rapid/js
npm install
node esbuild.mjs
```

## Architecture

| File | Role |
|---|---|
| `FieldtypeRapid.module.php` | Fieldtype — DB schema, value lifecycle, field config UI |
| `InputfieldRapid.module.php` | Inputfield — admin editor, asset loading |
| `ProcessFieldtypeRapid.module.php` | Upload/link/save endpoints |
| `ProcessRapid.module.php` | Dashboard and block preview |
| `ProcessRapidFrontend.module.php` | Frontend editing helper + `/rapid-save/` hook |
| `RapidValue.php` | Value object returned by `$page->field` |
| `RapidRenderer.php` | Block dispatcher — iterates blocks, handles toggle nesting |
| `RapidAttr.php` | HTML attribute builder |
| `RapidFrameworks.php` | CSS class mappings for Tailwind, Bootstrap, UIkit |
| `blocks/RapidBlockAbstract.php` | Base class for block renderers |
| `blocks/RapidBlocks.php` | Built-in block renderers |
| `js/editor.js` | Editor source (ES modules) |
| `js/dist/editor.js` | Pre-built IIFE bundle loaded in admin |
| `js/editor.css` | Admin editor styles |
| `rapid.css` | Frontend styles for vanilla rendering |

## Author

Maxim Semenov — [smnv.org](https://smnv.org) — maxim@smnv.org