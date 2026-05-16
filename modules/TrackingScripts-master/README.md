# TrackingScripts Module for ProcessWire

Manage and inject tracking scripts (Google Analytics, Google Ads, Facebook Pixel, custom code) into site pages, with optional PrivacyWire consent integration and robots.txt/llms.txt file management.

---

## Features

- **Google Analytics (GA4)** — inject gtag.js with Measurement ID
- **Google Ads** — inject gtag.js with Ads conversion ID
- **Facebook Pixel** — inject Pixel tracking code with noscript fallback
- **Custom code** — free-form textareas for any third-party scripts (head and/or body)
- **PrivacyWire integration** — when enabled, scripts are injected with `data-category` attributes and `type="text/plain"` so they only load after user consent
- **robots.txt & llms.txt** — edit and auto-generate both files from the admin; content is written to the site root on save
- **Per-service controls** — enable/disable, position (head or body), and consent category for each service independently
- **ID validation** — regex validation for GA (`G-`), Ads (`AW-`), and Pixel (numeric) IDs before injection
- **Admin-only exclusion** — scripts are never injected on admin or form-builder templates

---

## Files

```
site/modules/TrackingScripts/
├── TrackingScripts.info.php                    ← module metadata
├── TrackingScripts.module.php                  ← main module (hooks, script injection)
├── TrackingScriptsConfig.php                   ← module configuration (ModuleConfig)
├── ProcessTrackingScriptsConfig.info.php        ← Process module metadata
└── ProcessTrackingScriptsConfig.module          ← admin UI for non-superusers
```

---

## Installation

1. Copy the `TrackingScripts` folder into `/site/modules/`

2. In the admin go to **Modules → Refresh**, then install **TrackingScripts**

3. Optionally install **ProcessTrackingScriptsConfig** — this adds a **Setup → Tracking Scripts** page that allows non-superuser roles to edit the configuration. Assign the `tracking-scripts-config` permission to any role that needs access.

---

## Configuration

Go to **Modules → Configure → TrackingScripts** (superuser) or **Setup → Tracking Scripts** (any user with permission).

### Google Analytics

| Field | Description |
|-------|-------------|
| Enable | Activate/deactivate injection |
| Measurement ID | GA4 ID, e.g. `G-XXXXXXXXXX` |
| Position | Inject in `<head>` or before `</body>` |
| PrivacyWire Category | Consent category (default: Statistics) |

### Google Ads

| Field | Description |
|-------|-------------|
| Enable | Activate/deactivate injection |
| Ads ID | e.g. `AW-XXXXXXXXX` |
| Position | Inject in `<head>` or before `</body>` |
| PrivacyWire Category | Consent category (default: Marketing) |

### Facebook Pixel

| Field | Description |
|-------|-------------|
| Enable | Activate/deactivate injection |
| Pixel ID | Numeric ID, e.g. `123456789012345` |
| Position | Inject in `<head>` or before `</body>` |
| PrivacyWire Category | Consent category (default: Marketing) |

### Custom Tracking Code

Two free-form textareas for any additional third-party code:

- **Custom Code — Head**: injected before `</head>`
- **Custom Code — Body**: injected before `</body>`

### PrivacyWire Integration

When enabled, all tracking scripts are rendered with PrivacyWire-compatible attributes:

```html
<script type="text/plain" data-type="text/javascript" data-category="statistics" class="require-consent" src="..."></script>
```

This ensures scripts only execute after the user gives consent for the corresponding cookie category. Requires the [PrivacyWire](https://github.com/blaueQuelle/privacywire) module to be installed and active.

### Robots.txt & LLMs.txt

Edit the content of both files directly from the admin. On save, the files are written to (or removed from) the site root:

- `/robots.txt` — search engine crawler directives
- `/llms.txt` — LLM/AI bot directives

If a textarea is left empty, the corresponding file is deleted from the site root.

---

## How It Works

The module hooks into `Page::render` (priority 100) to inject scripts via `str_replace` on `</head>` and `</body>`. This means:

- No template modifications required
- Works on all front-end pages automatically
- Runs before PrivacyWire (priority 101), so consent attributes are in place when PrivacyWire processes the page

The `robots.txt` and `llms.txt` files are written via a hook on `Modules::saveConfig`, triggered whenever the module configuration is saved from either the module config screen or the Process admin page.

---

## ProcessTrackingScriptsConfig (Admin UI)

A Process module that mirrors the full TrackingScripts configuration under **Setup → Tracking Scripts**.

### Purpose

Allows non-superuser roles to manage tracking scripts without access to the Modules admin.

### Permission

The module registers the permission `tracking-scripts-config`. To grant access:

1. Go to **Access → Roles**
2. Edit the desired role
3. Check **tracking-scripts-config**
4. Save

### How it works

- Reads and writes the same configuration data as TrackingScripts via `$modules->getConfig()` / `$modules->saveConfig()`
- Changes from either location (Modules → Configure or Setup → Tracking Scripts) are reflected in both
- Saving triggers the same `Modules::saveConfig` hook, so robots.txt/llms.txt files are written automatically

---

## Requirements

- ProcessWire 3.0.110+
- PHP 7.2+
- [PrivacyWire](https://github.com/blaueQuelle/privacywire) (optional, for consent integration)

---

## License

Licensed under the MIT License.
