<?php namespace ProcessWire;

class CmsSettings extends Process {

    protected function getTableName() {
        return 'module_settings_data';
    }

    public static function getModuleInfo() {
        return [
            'title' => 'CMS Settings',
            'version' => 1,
            'summary' => 'Pagina admin para configuracion general del sitio.',
            'singular' => true,
            'autoload' => false,
            'permission' => 'cms-settings',
            'page' => [
                'name' => 'cms-settings',
                'parent' => 'admin',
                'title' => 'CMS Settings',
            ],
        ];
    }

    public function ___install() {
        $permissions = $this->wire('permissions');
        $roles = $this->wire('roles');

        $permission = $permissions->get('cms-settings');
        if(!$permission->id) {
            $permission = new Permission();
            $permission->name = 'cms-settings';
            $permission->title = 'CMS Settings';
            $permissions->add($permission);
        }

        $superuserRole = $roles->get('superuser');
        if($superuserRole->id && !$superuserRole->hasPermission('cms-settings')) {
            $superuserRole->addPermission($permission);
            $roles->save($superuserRole);
        }

        $table = $this->getTableName();
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` TINYINT UNSIGNED NOT NULL,
            `site_name` VARCHAR(255) NOT NULL DEFAULT '',
            `site_url` VARCHAR(255) NOT NULL DEFAULT '',
            `site_description` TEXT NULL,
            `site_logo` VARCHAR(255) NOT NULL DEFAULT '',
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->wire('database')->exec($sql);
        $stmt = $this->wire('database')->prepare("INSERT IGNORE INTO `{$table}` (`id`) VALUES (1)");
        $stmt->execute();
    }

    public function ___uninstall() {
        $permission = $this->wire('permissions')->get('cms-settings');
        if($permission->id) {
            $this->wire('permissions')->delete($permission);
        }
    }

    protected function isAbsoluteUrl($url) {
        if(!is_string($url) || $url === '') return false;
        if(!filter_var($url, FILTER_VALIDATE_URL)) return false;
        $parts = parse_url($url);
        return !empty($parts['scheme']) && !empty($parts['host']);
    }

    protected function ensureSettingsDirectory() {
        $path = wire('config')->paths->assets . 'files/settings/';
        if(!is_dir($path)) wire('files')->mkdir($path, true);
        if(is_dir($path) && !is_writable($path)) @chmod($path, 0775);
        if(!is_dir($path) || !is_writable($path)) {
            throw new WireException('No se puede escribir en /site/assets/files/settings/. Verifica permisos.');
        }
        return $path;
    }

    protected function getStoredSettings() {
        $stmt = wire('database')->prepare(
            "SELECT site_name, site_url, site_description, site_logo FROM `{$this->getTableName()}` WHERE id=1 LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            'site_name' => (string) ($row['site_name'] ?? ''),
            'site_url' => (string) ($row['site_url'] ?? ''),
            'site_description' => (string) ($row['site_description'] ?? ''),
            'site_logo' => (string) ($row['site_logo'] ?? ''),
        ];
    }

    protected function saveStoredSettings(array $settings) {
        $stmt = wire('database')->prepare(
            "INSERT INTO `{$this->getTableName()}` (id, site_name, site_url, site_description, site_logo, updated_at)
             VALUES (1, :site_name, :site_url, :site_description, :site_logo, NOW())
             ON DUPLICATE KEY UPDATE
             site_name = VALUES(site_name),
             site_url = VALUES(site_url),
             site_description = VALUES(site_description),
             site_logo = VALUES(site_logo),
             updated_at = NOW()"
        );
        $stmt->execute([
            ':site_name' => (string) ($settings['site_name'] ?? ''),
            ':site_url' => (string) ($settings['site_url'] ?? ''),
            ':site_description' => (string) ($settings['site_description'] ?? ''),
            ':site_logo' => (string) ($settings['site_logo'] ?? ''),
        ]);
    }

    protected function cleanupLogoFiles($logoPath, $keepFilename = '') {
        $files = glob($logoPath . '*.{jpg,jpeg,png,webp,gif,svg,JPG,JPEG,PNG,WEBP,GIF,SVG}', GLOB_BRACE);
        if(!$files) return;
        $keep = $keepFilename !== '' ? basename($keepFilename) : '';
        foreach($files as $file) {
            if(!is_file($file)) continue;
            if($keep !== '' && basename($file) === $keep) continue;
            @unlink($file);
        }
    }

    protected function includeAssets() {
        $jqv = wire('modules')->get('JqueryValidate');
        if($jqv && method_exists($jqv, 'includeAssets')) $jqv->includeAssets();
        wire('config')->scripts->add(wire('config')->urls->siteModules . 'Settings/settings-form.js');
    }

    public function ___execute() {
        $this->includeAssets();

        $modules = wire('modules');
        $input = wire('input');
        $sanitizer = wire('sanitizer');
        $logoPath = $this->ensureSettingsDirectory();
        $stored = $this->getStoredSettings();
        $values = $stored;

        if($input->post->submit_settings) {
            $siteName = $sanitizer->text($input->post->site_name);
            $siteUrl = $sanitizer->url($input->post->site_url);
            $siteDescription = $sanitizer->textarea($input->post->site_description);
            $currentLogo = basename((string) ($stored['site_logo'] ?? ''));

            $upload = new WireUpload('site_logo_upload');
            $upload->setDestinationPath($logoPath);
            $upload->setValidExtensions(['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
            $upload->setMaxFiles(1);
            $upload->setOverwrite(true);
            $uploaded = $upload->execute();
            if(count($uploaded)) {
                $currentLogo = basename($uploaded[0]);
                $this->cleanupLogoFiles($logoPath, $currentLogo);
            }

            $values = [
                'site_name' => $siteName,
                'site_url' => $siteUrl,
                'site_description' => $siteDescription,
                'site_logo' => $currentLogo,
            ];

            if($siteName === '') {
                $this->error('El nombre del sitio es requerido.');
            } elseif(!$this->isAbsoluteUrl($siteUrl)) {
                $this->error('La URL del sitio debe ser absoluta (ej: https://dominio.com).');
            } else {
                $this->saveStoredSettings($values);
                $this->message('Configuracion guardada correctamente.');
            }
        }

        $form = $modules->get('InputfieldForm');
        $form->method = 'post';
        $form->action = './';

        $siteName = $modules->get('InputfieldText');
        $siteName->name = 'site_name';
        $siteName->label = 'Nombre del sitio web';
        $siteName->required = 1;
        $siteName->value = $values['site_name'];
        $form->add($siteName);

        $siteUrl = $modules->get('InputfieldURL');
        $siteUrl->name = 'site_url';
        $siteUrl->label = 'Url del sitio';
        $siteUrl->required = 1;
        $siteUrl->value = $values['site_url'];
        $form->add($siteUrl);

        $siteDescription = $modules->get('InputfieldTextarea');
        $siteDescription->name = 'site_description';
        $siteDescription->label = 'Descripcion del sitio';
        $siteDescription->value = $values['site_description'];
        $form->add($siteDescription);

        $logo = $modules->get('InputfieldFile');
        $logo->name = 'site_logo_upload';
        $logo->label = 'Logotipo';
        $logo->maxFiles = 1;
        $logo->extensions = 'jpg jpeg png webp gif svg';
        $logo->destinationPath = $logoPath;
        $logo->useAjax = true;
        $logo->set('ajax', true);
        $form->add($logo);

        $previewHtml = '<p style="color:#6b7280;">No hay logotipo cargado.</p>';
        $savedLogo = basename((string) ($values['site_logo'] ?? ''));
        if($savedLogo !== '' && is_file($logoPath . $savedLogo)) {
            $logoUrl = wire('config')->urls->assets . 'files/settings/' . rawurlencode($savedLogo);
            $previewHtml = "<p><a class='js-settings-logo-lightbox' href='{$logoUrl}'>{$savedLogo}</a></p><p><a class='js-settings-logo-lightbox' href='{$logoUrl}'><img src='{$logoUrl}' alt='Logotipo actual' style='max-width:220px;height:auto;cursor:zoom-in;'></a></p>";
        }

        $preview = $modules->get('InputfieldMarkup');
        $preview->label = 'Vista previa del logotipo';
        $preview->value = "<div id='settings-logo-preview'>{$previewHtml}</div>";
        $form->add($preview);

        $submit = $modules->get('InputfieldSubmit');
        $submit->name = 'submit_settings';
        $submit->value = 'Guardar configuracion';
        $form->add($submit);

        $magnific = $modules->get('JqueryMagnificPressets');
        $magnificScript = '';
        if($magnific && method_exists($magnific, 'renderInitScript')) {
            $magnificScript = $magnific->renderInitScript('.js-settings-logo-lightbox', 'lightbox');
        }

        return $form->render() . $magnificScript;
    }
}
