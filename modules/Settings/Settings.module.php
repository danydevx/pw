<?php namespace ProcessWire;

class Settings extends WireData implements Module, ConfigurableModule {

    protected function includeSettingsScripts() {
        $config = wire('config');
        $settingsJs = $config->urls->siteModules . 'Settings/settings-form.js';
        $config->scripts->add($settingsJs);
    }

    protected function isAbsoluteUrl($url) {
        if(!is_string($url) || $url === '') return false;
        if(!filter_var($url, FILTER_VALIDATE_URL)) return false;
        $parts = parse_url($url);
        return !empty($parts['scheme']) && !empty($parts['host']);
    }

    protected function getTableName() {
        return 'module_settings_data';
    }

    protected function ensureTableSchema() {
        $table = $this->getTableName();
        $db = $this->wire('database');

        $columns = [
            'id' => "TINYINT UNSIGNED NOT NULL",
            'site_name' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'site_url' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'site_description' => "TEXT NULL",
            'site_logo' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'street' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'num_int' => "VARCHAR(60) NOT NULL DEFAULT ''",
            'num_ext' => "VARCHAR(60) NOT NULL DEFAULT ''",
            'colony' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'municipality' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'state' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'cp' => "VARCHAR(20) NOT NULL DEFAULT ''",
            'company' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'phone' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'whatsapp' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'email' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'facebook' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'instagram' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'x_url' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'tiktok' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'updated_at' => "DATETIME NULL",
        ];

        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` TINYINT UNSIGNED NOT NULL,
            `site_name` VARCHAR(255) NOT NULL DEFAULT '',
            `site_url` VARCHAR(255) NOT NULL DEFAULT '',
            `site_description` TEXT NULL,
            `site_logo` VARCHAR(255) NOT NULL DEFAULT '',
            `street` VARCHAR(255) NOT NULL DEFAULT '',
            `num_int` VARCHAR(60) NOT NULL DEFAULT '',
            `num_ext` VARCHAR(60) NOT NULL DEFAULT '',
            `colony` VARCHAR(255) NOT NULL DEFAULT '',
            `municipality` VARCHAR(255) NOT NULL DEFAULT '',
            `state` VARCHAR(255) NOT NULL DEFAULT '',
            `cp` VARCHAR(20) NOT NULL DEFAULT '',
            `company` VARCHAR(255) NOT NULL DEFAULT '',
            `phone` VARCHAR(80) NOT NULL DEFAULT '',
            `whatsapp` VARCHAR(80) NOT NULL DEFAULT '',
            `email` VARCHAR(255) NOT NULL DEFAULT '',
            `facebook` VARCHAR(255) NOT NULL DEFAULT '',
            `instagram` VARCHAR(255) NOT NULL DEFAULT '',
            `x_url` VARCHAR(255) NOT NULL DEFAULT '',
            `tiktok` VARCHAR(255) NOT NULL DEFAULT '',
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $db->exec($sql);

        $existing = [];
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}`");
        if($stmt) {
            foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $existing[$row['Field']] = true;
            }
        }

        foreach($columns as $name => $definition) {
            if(isset($existing[$name])) continue;
            $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$definition}");
        }

        if(isset($existing['site_description'])) {
            $db->exec("ALTER TABLE `{$table}` MODIFY COLUMN `site_description` TEXT NULL");
        }
    }

    public function ___install() {
        $this->ensureTableSchema();

        $table = $this->getTableName();
        $stmt = $this->wire('database')->prepare("INSERT IGNORE INTO `{$table}` (`id`) VALUES (1)");
        $stmt->execute();
    }

    protected function getStoredSettings() {
        $this->ensureTableSchema();
        $table = $this->getTableName();
        $stmt = $this->wire('database')->prepare("SELECT site_name, site_url, site_description, site_logo, street, num_int, num_ext, colony, municipality, state, cp, company, phone, whatsapp, email, facebook, instagram, x_url, tiktok FROM `{$table}` WHERE id=1 LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row) {
            return [
                'site_name' => '',
                'site_url' => '',
                'site_description' => '',
                'site_logo' => '',
                'street' => '',
                'num_int' => '',
                'num_ext' => '',
                'colony' => '',
                'municipality' => '',
                'state' => '',
                'cp' => '',
                'company' => '',
                'phone' => '',
                'whatsapp' => '',
                'email' => '',
                'facebook' => '',
                'instagram' => '',
                'x_url' => '',
                'tiktok' => '',
            ];
        }

        return [
            'site_name' => (string) ($row['site_name'] ?? ''),
            'site_url' => (string) ($row['site_url'] ?? ''),
            'site_description' => (string) ($row['site_description'] ?? ''),
            'site_logo' => (string) ($row['site_logo'] ?? ''),
            'street' => (string) ($row['street'] ?? ''),
            'num_int' => (string) ($row['num_int'] ?? ''),
            'num_ext' => (string) ($row['num_ext'] ?? ''),
            'colony' => (string) ($row['colony'] ?? ''),
            'municipality' => (string) ($row['municipality'] ?? ''),
            'state' => (string) ($row['state'] ?? ''),
            'cp' => (string) ($row['cp'] ?? ''),
            'company' => (string) ($row['company'] ?? ''),
            'phone' => (string) ($row['phone'] ?? ''),
            'whatsapp' => (string) ($row['whatsapp'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'facebook' => (string) ($row['facebook'] ?? ''),
            'instagram' => (string) ($row['instagram'] ?? ''),
            'x_url' => (string) ($row['x_url'] ?? ''),
            'tiktok' => (string) ($row['tiktok'] ?? ''),
        ];
    }

    protected function saveStoredSettings(array $settings) {
        $this->ensureTableSchema();
        $table = $this->getTableName();
        $stmt = $this->wire('database')->prepare(
            "INSERT INTO `{$table}` (id, site_name, site_url, site_description, site_logo, street, num_int, num_ext, colony, municipality, state, cp, company, phone, whatsapp, email, facebook, instagram, x_url, tiktok, updated_at)
             VALUES (1, :site_name, :site_url, :site_description, :site_logo, :street, :num_int, :num_ext, :colony, :municipality, :state, :cp, :company, :phone, :whatsapp, :email, :facebook, :instagram, :x_url, :tiktok, NOW())
             ON DUPLICATE KEY UPDATE
             site_name = VALUES(site_name),
             site_url = VALUES(site_url),
             site_description = VALUES(site_description),
             site_logo = VALUES(site_logo),
             street = VALUES(street),
             num_int = VALUES(num_int),
             num_ext = VALUES(num_ext),
             colony = VALUES(colony),
             municipality = VALUES(municipality),
             state = VALUES(state),
             cp = VALUES(cp),
             company = VALUES(company),
             phone = VALUES(phone),
             whatsapp = VALUES(whatsapp),
             email = VALUES(email),
             facebook = VALUES(facebook),
             instagram = VALUES(instagram),
             x_url = VALUES(x_url),
             tiktok = VALUES(tiktok),
             updated_at = NOW()"
        );
        $stmt->execute([
            ':site_name' => (string) ($settings['site_name'] ?? ''),
            ':site_url' => (string) ($settings['site_url'] ?? ''),
            ':site_description' => (string) ($settings['site_description'] ?? ''),
            ':site_logo' => (string) ($settings['site_logo'] ?? ''),
            ':street' => (string) ($settings['street'] ?? ''),
            ':num_int' => (string) ($settings['num_int'] ?? ''),
            ':num_ext' => (string) ($settings['num_ext'] ?? ''),
            ':colony' => (string) ($settings['colony'] ?? ''),
            ':municipality' => (string) ($settings['municipality'] ?? ''),
            ':state' => (string) ($settings['state'] ?? ''),
            ':cp' => (string) ($settings['cp'] ?? ''),
            ':company' => (string) ($settings['company'] ?? ''),
            ':phone' => (string) ($settings['phone'] ?? ''),
            ':whatsapp' => (string) ($settings['whatsapp'] ?? ''),
            ':email' => (string) ($settings['email'] ?? ''),
            ':facebook' => (string) ($settings['facebook'] ?? ''),
            ':instagram' => (string) ($settings['instagram'] ?? ''),
            ':x_url' => (string) ($settings['x_url'] ?? ''),
            ':tiktok' => (string) ($settings['tiktok'] ?? ''),
        ]);
    }

    protected function extractFilename($value) {
        if(is_object($value)) {
            if(method_exists($value, 'first')) {
                $first = $value->first();
                $filename = $this->extractFilename($first);
                if($filename !== '') return $filename;
            }

            foreach(['basename', 'filename', 'name'] as $prop) {
                if(isset($value->$prop) && is_string($value->$prop) && $value->$prop !== '') {
                    return basename($value->$prop);
                }
            }

            if(method_exists($value, '__toString')) {
                $stringValue = (string) $value;
                if($stringValue !== '') return basename($stringValue);
            }

            return '';
        }

        if(is_array($value)) {
            foreach($value as $item) {
                $filename = $this->extractFilename($item);
                if($filename !== '') return $filename;
            }
            return '';
        }

        if(is_string($value) && $value !== '') {
            return basename($value);
        }

        return '';
    }

    protected function extractLastFilename($value) {
        $filenames = [];

        $walker = function($item) use (&$walker, &$filenames) {
            if(is_object($item)) {
                if(method_exists($item, 'first')) {
                    $walker($item->first());
                }

                foreach(['basename', 'filename', 'name'] as $prop) {
                    if(isset($item->$prop) && is_string($item->$prop) && $item->$prop !== '') {
                        $filenames[] = basename($item->$prop);
                    }
                }

                if(method_exists($item, '__toString')) {
                    $asString = (string) $item;
                    if($asString !== '') $filenames[] = basename($asString);
                }

                return;
            }

            if(is_array($item)) {
                foreach($item as $child) {
                    $walker($child);
                }
                return;
            }

            if(is_string($item) && $item !== '') {
                $filenames[] = basename($item);
            }
        };

        $walker($value);

        if(!count($filenames)) return '';
        $filenames = array_values(array_filter($filenames));
        return count($filenames) ? $filenames[count($filenames) - 1] : '';
    }

    protected function getLatestLogoFilename($logoPath) {
        $files = glob($logoPath . '*.{jpg,jpeg,png,webp,gif,svg,JPG,JPEG,PNG,WEBP,GIF,SVG}', GLOB_BRACE);
        if(!$files || !count($files)) return '';

        usort($files, function($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        return basename($files[0]);
    }

    protected function getCurrentLogoFilename($logoPath) {
        $saved = $this->extractFilename(wire('modules')->getConfig($this, 'site_logo'));
        if($saved !== '' && is_file($logoPath . $saved)) {
            return $saved;
        }

        $savedFromObject = $this->extractFilename($this->site_logo ?? '');
        if($savedFromObject !== '' && is_file($logoPath . $savedFromObject)) {
            return $savedFromObject;
        }

        $allImages = glob($logoPath . '*.{jpg,jpeg,png,webp,gif,svg,JPG,JPEG,PNG,WEBP,GIF,SVG}', GLOB_BRACE);
        if($allImages && count($allImages) === 1) {
            return basename($allImages[0]);
        }

        return '';
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

    protected function processLogoUpload($logoPath) {
        $input = wire('input');
        if(!$input->files || !isset($input->files->site_logo_upload)) {
            return '';
        }

        $upload = new WireUpload('site_logo_upload');
        $upload->setValidExtensions(['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
        $upload->setMaxFiles(1);
        $upload->setOverwrite(false);
        $upload->setLowercase(false);
        $upload->setDestinationPath($logoPath);

        $uploaded = $upload->execute();
        if(!is_array($uploaded) || !count($uploaded)) {
            return '';
        }

        return basename((string) end($uploaded));
    }

    protected function hasLogoUploadRequest() {
        if(!isset($_FILES['site_logo_upload'])) return false;
        $file = $_FILES['site_logo_upload'];

        if(isset($file['name']) && is_array($file['name'])) {
            foreach($file['name'] as $name) {
                if(is_string($name) && trim($name) !== '') return true;
            }
            return false;
        }

        return isset($file['name']) && is_string($file['name']) && trim($file['name']) !== '';
    }

    protected function getLatestLogoFilenameExcept($logoPath, $exceptFilename = '') {
        $files = glob($logoPath . '*.{jpg,jpeg,png,webp,gif,svg,JPG,JPEG,PNG,WEBP,GIF,SVG}', GLOB_BRACE);
        if(!$files || !count($files)) return '';

        $except = basename((string) $exceptFilename);
        usort($files, function($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        foreach($files as $file) {
            $base = basename($file);
            if($except !== '' && $base === $except) continue;
            return $base;
        }

        return '';
    }

    protected function ensureSettingsDirectory() {
        $files = wire('files');
        $config = wire('config');

        $settingsPath = $config->paths->assets . 'files/settings/';

        if(!is_dir($settingsPath)) {
            $files->mkdir($settingsPath, true, 0775);
        }

        clearstatcache(true, $settingsPath);

        if(is_dir($settingsPath) && method_exists($files, 'chmod')) {
            $files->chmod($settingsPath, false, '0775');
        }

        clearstatcache(true, $settingsPath);

        if(!is_dir($settingsPath) || !is_writable($settingsPath)) {
            throw new WireException(
                'No se puede escribir en /site/assets/files/settings/. Revisa permisos y propietario del directorio.'
            );
        }

        return $settingsPath;
    }

    public function init() {
        $this->addHookBefore('Modules::saveConfig', $this, 'beforeSaveConfig');
    }

    public static function getModuleInfo() {
        return [
            'title' => 'Settings',
            'version' => 1,
            'summary' => 'Configuracion general del sitio.',
            'autoload' => false,
            'singular' => true,
        ];
    }

    public function getModuleConfigInputfields($inputfields) {
        $modules = wire('modules');
        $input = wire('input');
        $isPost = strtoupper($input->requestMethod()) === 'POST';
        $logoPath = $this->ensureSettingsDirectory();
        $sanitizer = wire('sanitizer');
        $stored = $this->getStoredSettings();

        $postedSiteName = $sanitizer->text((string) $input->post->site_name);
        $postedSiteUrl = $sanitizer->url((string) $input->post->site_url);

        $siteFieldset = $modules->get('InputfieldFieldset');
        $siteFieldset->label = 'Datos del sitio';

        $siteName = $modules->get('InputfieldText');
        $siteName->name = 'site_name';
        $siteName->label = 'Titulo del sitio';
        $siteName->required = 1;
        $siteName->columnWidth = 100;
        $siteName->value = $isPost ? $postedSiteName : $stored['site_name'];
        if($isPost && $postedSiteName === '') {
            $siteName->error('El nombre del sitio es requerido.');
            $siteName->addClass('InputfieldStateError');
        }
        $siteFieldset->add($siteName);

        $siteUrl = $modules->get('InputfieldURL');
        $siteUrl->name = 'site_url';
        $siteUrl->label = 'Url del sitio';
        $siteUrl->required = 1;
        $siteUrl->columnWidth = 100;
        $siteUrl->value = $isPost ? $postedSiteUrl : $stored['site_url'];

        if($isPost && !$this->isAbsoluteUrl($postedSiteUrl)) {
            $siteUrl->error('Debe ser una URL absoluta (ej: https://dominio.com).');
            $siteUrl->addClass('InputfieldStateError');
        }

        $siteFieldset->add($siteUrl);

        $siteDescription = $modules->get('InputfieldTextarea');
        $siteDescription->name = 'site_description';
        $siteDescription->label = 'Descripcion';
        $siteDescription->required = 1;
        $siteDescription->columnWidth = 100;
        $siteDescription->value = $isPost ? $sanitizer->textarea((string) $input->post->site_description) : $stored['site_description'];
        if($isPost && trim((string) $siteDescription->value) === '') {
            $siteDescription->error('Este campo es requerido.');
            $siteDescription->addClass('InputfieldStateError');
        }
        $siteFieldset->add($siteDescription);

        $logo = $modules->get('InputfieldFile');
        $logo->name = 'site_logo_upload';
        $logo->label = 'Logotipo';
        $logo->required = 0;
        $logo->maxFiles = 1;
        $logo->extensions = 'jpg jpeg png webp gif svg';
        $logo->description = 'Sube el logotipo del sitio.';
        $logo->destinationPath = $logoPath;
        $logo->useAjax = false;
        $logo->set('ajax', false);

        $savedLogo = $this->extractFilename($stored['site_logo']);
        $previewHtml = '<p style="color:#6b7280;">No hay logotipo cargado.</p>';

        if($savedLogo !== '' && is_file($logoPath . $savedLogo)) {
            $safeFile = $sanitizer->filename($savedLogo);
            $logoUrl = wire('config')->urls->assets . 'files/settings/' . rawurlencode($safeFile);
            $previewHtml = "<p><a class='js-settings-logo-lightbox' href='{$logoUrl}'>{$safeFile}</a></p><p><a class='js-settings-logo-lightbox' href='{$logoUrl}'><img src='{$logoUrl}' alt='Logotipo actual' style='max-width:220px;height:auto;cursor:zoom-in;'></a></p>";
        }

        $siteFieldset->add($logo);

        $preview = $modules->get('InputfieldMarkup');
        $preview->name = 'site_logo_preview';
        $preview->label = 'Vista previa del logotipo';
        $preview->value = "<div id='settings-logo-preview'>{$previewHtml}</div>";
        $siteFieldset->add($preview);
        $inputfields->add($siteFieldset);

        $locationFieldset = $modules->get('InputfieldFieldset');
        $locationFieldset->label = 'Datos de Ubicacion';

        foreach([
            ['street', 'Calle', 'InputfieldText', 70],
            ['num_int', 'Num Int', 'InputfieldText', 15],
            ['num_ext', 'Num Ext', 'InputfieldText', 15],
            ['colony', 'Colonia', 'InputfieldText', 30],
            ['municipality', 'Municipio', 'InputfieldText', 30],
            ['state', 'Estado', 'InputfieldText', 30],
            ['cp', 'Cp', 'InputfieldText', 10],
        ] as $item) {
            $field = $modules->get($item[2]);
            $field->name = $item[0];
            $field->label = $item[1];
            $field->columnWidth = $item[3];
            $field->value = $isPost ? $sanitizer->text((string) $input->post->{$item[0]}) : $stored[$item[0]];
            $locationFieldset->add($field);
        }
        $inputfields->add($locationFieldset);

        $contactFieldset = $modules->get('InputfieldFieldset');
        $contactFieldset->label = 'Datos de Contacto';

        $company = $modules->get('InputfieldText');
        $company->name = 'company';
        $company->label = 'Empresa';
        $company->columnWidth = 50;
        $company->value = $isPost ? $sanitizer->text((string) $input->post->company) : $stored['company'];
        $contactFieldset->add($company);

        foreach([
            ['phone', 'Telefono'],
            ['whatsapp', 'Whatsapp'],
        ] as $item) {
            $field = $modules->get('InputfieldText');
            $field->name = $item[0];
            $field->label = $item[1];
            $field->columnWidth = 50;
            $field->value = $isPost ? $sanitizer->text((string) $input->post->{$item[0]}) : $stored[$item[0]];
            $contactFieldset->add($field);
        }

        $email = $modules->get('InputfieldEmail');
        $email->name = 'email';
        $email->label = 'Email';
        $email->columnWidth = 50;
        $email->value = $isPost ? $sanitizer->email((string) $input->post->email) : $stored['email'];
        $contactFieldset->add($email);

        $inputfields->add($contactFieldset);

        $socialFieldset = $modules->get('InputfieldFieldset');
        $socialFieldset->label = 'Redes Sociales';
        foreach([
            ['facebook', 'Facebook'],
            ['instagram', 'Instagram'],
            ['x_url', 'X'],
            ['tiktok', 'Tiktok'],
        ] as $item) {
            $field = $modules->get('InputfieldURL');
            $field->name = $item[0];
            $field->label = $item[1];
            $field->columnWidth = 50;
            $field->value = $isPost ? $sanitizer->url((string) $input->post->{$item[0]}) : $stored[$item[0]];
            if($isPost && $field->value !== '' && !$this->isAbsoluteUrl((string) $field->value)) {
                $field->error('Debe ser una URL absoluta (ej: https://dominio.com).');
                $field->addClass('InputfieldStateError');
            }
            $socialFieldset->add($field);
        }
        $inputfields->add($socialFieldset);

        $jsValidator = $modules->get('JqueryValidate');
        if($jsValidator && method_exists($jsValidator, 'includeAssets')) {
            $jsValidator->includeAssets();
        }

        $magnific = $modules->get('JqueryMagnificPressets');
        if($magnific && method_exists($magnific, 'renderInitScript')) {
            $magnificMarkup = $modules->get('InputfieldMarkup');
            $magnificMarkup->value = $magnific->renderInitScript('.js-settings-logo-lightbox', 'lightbox');
            $inputfields->add($magnificMarkup);
        }

        $this->includeSettingsScripts();

        return $inputfields;
    }

    public function beforeSaveConfig(HookEvent $event) {
        $class = $event->arguments(0);

        if(is_object($class)) {
            $class = $class->className();
        }

        if($class !== $this->className()) {
            return;
        }

        $data = (array) $event->arguments(1);
        $sanitizer = wire('sanitizer');
        $logoPath = $this->ensureSettingsDirectory();

        $currentLogo = '';

        $stored = $this->getStoredSettings();
        $currentLogo = $this->extractFilename($stored['site_logo']);
        $logoUploadRequested = $this->hasLogoUploadRequest();

        $uploadedFromInputfield = '';
        $uploadedFromWireUpload = $this->processLogoUpload($logoPath);

        if($uploadedFromWireUpload !== '') {
            $uploadedFromInputfield = $uploadedFromWireUpload;
        }

        if($uploadedFromInputfield === '' && isset($data['site_logo_upload'])) {
            $uploadedFromInputfield = $this->extractLastFilename($data['site_logo_upload']);
        }

        if($logoUploadRequested && ($uploadedFromInputfield === '' || $uploadedFromInputfield === $currentLogo)) {
            $latestUploaded = $this->getLatestLogoFilenameExcept($logoPath, $currentLogo);
            if($latestUploaded !== '') {
                $uploadedFromInputfield = $latestUploaded;
            }
        }

        if($uploadedFromInputfield !== '') {
            $currentLogo = $uploadedFromInputfield;
            $this->cleanupLogoFiles($logoPath, $currentLogo);
        } elseif($currentLogo === '') {
            $currentLogo = $this->getLatestLogoFilename($logoPath);
            if($currentLogo !== '') {
                $this->cleanupLogoFiles($logoPath, $currentLogo);
            }
        }

        $files = wire('files');
        if(method_exists($files, 'chmod') && is_dir($logoPath)) {
            $files->chmod($logoPath, true, '0775');
        }

        $siteName = $sanitizer->text($data['site_name'] ?? '');
        $siteUrl = $sanitizer->url($data['site_url'] ?? '');
        $siteDescription = $sanitizer->textarea($data['site_description'] ?? '');
        $street = $sanitizer->text($data['street'] ?? '');
        $numInt = $sanitizer->text($data['num_int'] ?? '');
        $numExt = $sanitizer->text($data['num_ext'] ?? '');
        $colony = $sanitizer->text($data['colony'] ?? '');
        $municipality = $sanitizer->text($data['municipality'] ?? '');
        $state = $sanitizer->text($data['state'] ?? '');
        $cp = $sanitizer->text($data['cp'] ?? '');
        $company = $sanitizer->text($data['company'] ?? '');
        $phone = $sanitizer->text($data['phone'] ?? '');
        $whatsapp = $sanitizer->text($data['whatsapp'] ?? '');
        $email = $sanitizer->email($data['email'] ?? '');
        $facebook = $sanitizer->url($data['facebook'] ?? '');
        $instagram = $sanitizer->url($data['instagram'] ?? '');
        $xUrl = $sanitizer->url($data['x_url'] ?? '');
        $tiktok = $sanitizer->url($data['tiktok'] ?? '');

        if($siteName === '') {
            $this->error('El campo "Nombre del sitio web" es requerido.');
            return;
        }

        if(!$this->isAbsoluteUrl($siteUrl)) {
            $this->error('El campo "Url del sitio" es requerido y debe ser una URL absoluta (ej: https://dominio.com).');
            return;
        }

        if(trim($siteDescription) === '') {
            $this->error('El campo "Descripcion" es requerido.');
            return;
        }

        foreach([
            'Facebook' => $facebook,
            'Instagram' => $instagram,
            'X' => $xUrl,
            'Tiktok' => $tiktok,
        ] as $label => $value) {
            if($value !== '' && !$this->isAbsoluteUrl($value)) {
                $this->error("El campo \"{$label}\" debe ser una URL absoluta (ej: https://dominio.com).");
                return;
            }
        }

        $this->saveStoredSettings([
            'site_name' => $siteName,
            'site_url' => $siteUrl,
            'site_description' => $siteDescription,
            'site_logo' => $sanitizer->filename($currentLogo),
            'street' => $street,
            'num_int' => $numInt,
            'num_ext' => $numExt,
            'colony' => $colony,
            'municipality' => $municipality,
            'state' => $state,
            'cp' => $cp,
            'company' => $company,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'facebook' => $facebook,
            'instagram' => $instagram,
            'x_url' => $xUrl,
            'tiktok' => $tiktok,
        ]);

        $data = [
            'site_name' => $siteName,
            'site_url' => $siteUrl,
            'site_description' => $siteDescription,
            'site_logo' => $sanitizer->filename($currentLogo),
            'street' => $street,
            'num_int' => $numInt,
            'num_ext' => $numExt,
            'colony' => $colony,
            'municipality' => $municipality,
            'state' => $state,
            'cp' => $cp,
            'company' => $company,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'facebook' => $facebook,
            'instagram' => $instagram,
            'x_url' => $xUrl,
            'tiktok' => $tiktok,
        ];

        $event->arguments(1, $data);
    }
}
