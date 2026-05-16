<?php namespace ProcessWire;

class CmsTest extends WireData implements Module, ConfigurableModule {

    public function init() {
        $this->addHookBefore('Modules::saveConfig', $this, 'beforeSaveConfig');
    }

    public static function getModuleInfo() {
        return [
            'title' => 'CMS Test',
            'version' => 1,
            'summary' => 'Modulo de prueba para subir una imagen y cambiar carpeta destino.',
            'autoload' => false,
            'singular' => true,
        ];
    }

    protected function sanitizeFolderName($value) {
        $value = (string) $value;
        $value = trim($value);
        $value = trim($value, '/');
        $value = wire('sanitizer')->pageName($value, true);
        return $value !== '' ? $value : 'cmstest';
    }

    protected function ensureUploadDirectory($folderName) {
        $files = wire('files');
        $base = wire('config')->paths->assets . 'files/';
        $path = $base . $folderName . '/';

        if(!is_dir($path)) {
            $files->mkdir($path, true);
        }

        clearstatcache(true, $path);

        if(is_dir($path) && !is_writable($path)) {
            @chmod($path, 0775);
            clearstatcache(true, $path);
        }

        if(!is_dir($path) || !is_writable($path)) {
            throw new WireException("No se puede escribir en {$path}. Verifica permisos.");
        }

        return $path;
    }

    public function getModuleConfigInputfields($inputfields) {
        $modules = wire('modules');

        $folderRaw = $modules->getConfig($this, 'upload_folder');
        $folder = $this->sanitizeFolderName($folderRaw ?: 'cmstest');
        $destinationPath = $this->ensureUploadDirectory($folder);

        $uploadFolder = $modules->get('InputfieldText');
        $uploadFolder->name = 'upload_folder';
        $uploadFolder->label = 'Carpeta destino';
        $uploadFolder->description = 'Subcarpeta dentro de /site/assets/files/ (ej: cmstest, logos, pruebas).';
        $uploadFolder->required = 1;
        $uploadFolder->value = $folder;
        $inputfields->add($uploadFolder);

        $image = $modules->get('InputfieldFile');
        $image->name = 'test_image_upload';
        $image->label = 'Imagen de prueba';
        $image->required = 0;
        $image->maxFiles = 1;
        $image->extensions = 'jpg jpeg png webp gif svg';
        $image->destinationPath = $destinationPath;
        $inputfields->add($image);

        $saved = (string) $modules->getConfig($this, 'test_image');
        if($saved !== '') {
            $safeFolder = $this->sanitizeFolderName($folder);
            $safeFile = wire('sanitizer')->text($saved);
            $url = wire('config')->urls->assets . 'files/' . rawurlencode($safeFolder) . '/' . rawurlencode($safeFile);
            $preview = $modules->get('InputfieldMarkup');
            $preview->label = 'Imagen actual';
            $preview->value = "<p><img src='{$url}' alt='Imagen' style='max-width:200px;height:auto;' /></p><p><a href='{$url}' target='_blank' rel='noopener'>{$safeFile}</a></p>";
            $inputfields->add($preview);
        }

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

        $folder = $this->sanitizeFolderName($data['upload_folder'] ?? 'cmstest');
        $this->ensureUploadDirectory($folder);

        $currentImage = '';
        if(isset($data['test_image']) && is_string($data['test_image'])) {
            $currentImage = $sanitizer->text($data['test_image']);
        }

        $uploadedFromInputfield = '';
        if(isset($data['test_image_upload'])) {
            $uploadValue = $data['test_image_upload'];
            if(is_string($uploadValue) && $uploadValue !== '') {
                $uploadedFromInputfield = basename($uploadValue);
            } elseif(is_array($uploadValue) && !empty($uploadValue)) {
                $first = reset($uploadValue);
                if(is_string($first) && $first !== '') {
                    $uploadedFromInputfield = basename($first);
                }
            }
        }

        if($uploadedFromInputfield !== '') {
            $currentImage = $uploadedFromInputfield;
        }

        $data['upload_folder'] = $folder;
        $data['test_image'] = $sanitizer->text($currentImage);
        unset($data['test_image_upload']);

        $event->arguments(1, $data);
    }
}
