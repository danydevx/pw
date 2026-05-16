<?php namespace ProcessWire;

/**
 * Class CmsHelpers
 *
 * Helper general para administración en ProcessWire.
 * Incluye utilidades para breadcrumbs, botones, acciones, thumbnails y cifrado.
 */
class CmsHelpers extends Process {

    protected $key; // Clave para cifrado

    public function __construct(){
        parent::__construct();
    }

    /**
     * Init del módulo
     * Activa soporte para modales de jQueryUI
     */
    public function init(){
        parent::init();
        $this->modules->get("JqueryUI")->use("modal");
    }

    /**
     * Crear breadcrumbs dinámicos
     * @param array $breads [['url' => '', 'title' => '']]
     */
    public function createBreadcrumbs($breads = []){
        $this->wire('breadcrumbs')->removeAll();
        $breadcrumbs = new Breadcrumbs(); 

        if(!empty($breads)){
            foreach ($breads as $b){
                if(isset($b['url'], $b['title'])){
                    $breadcrumbs->add(new Breadcrumb($b['url'], $b['title']));
                }
            }
        }

        $this->wire('breadcrumbs', $breadcrumbs);  
    }

    /**
     * Define el título de la página en admin
     */
    public function createTitlePage($pageTitle = ''){
        $this->wire('processBrowserTitle', $pageTitle);
        $this->process->headline($pageTitle);
    }

    /**
     * Genera thumbnail de imagen
     */
    public function createThumbnail($image = '', $width = 100, $height = 100){
        if($image && method_exists($image, 'size')) {
            $thumb = $image->size($width, $height);
            return "<img src='{$thumb->url}' alt=''>";
        }

        return "<img src='https://via.placeholder.com/{$width}x{$height}' alt=''>";
    }

    /**
     * Mostrar errores en admin
     */
    public function showErrors($error = 'You dont have permission to this action'){
        $this->error($error, Notice::log);
        $this->error($error . " (admin)", Notice::superuser);
        return $error;
    }

    /**
     * Renderizar navbar de acciones
     */
    public function getActionsNavbar($params){
        if(empty($params['viewPath'])) return '';
        return $this->files->render($params['viewPath'] . 'navbar.view.php', $params);
    }

    /**
     * Crear botón modal
     */
    public function createButtonModal($btn){
        return sprintf(
            '<a href="%s" class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2 pw-modal"
            data-barba-prevent data-buttons="button.ui-button[type=submit]" data-autoclose data-reload="true">%s</a>',
            $btn['href'] ?? '#',
            $btn['label'] ?? 'Button'
        );
    }

    /**
     * Crear botón link normal
     */
    public function createButtonLink($btn){
        return sprintf(
            '<a href="%s" class="uk-button uk-button-primary uk-button-small ui-corner-all mx-2"
            data-barba-prevent>%s</a>',
            $btn['href'] ?? '#',
            $btn['label'] ?? 'Button'
        );
    }

    /**
     * Crear botón tipo panel lateral
     */
    public function createButtonPanel($btn, $width = '90%'){
        return sprintf(
            '<a href="%s" class="pw-panel pw-panel-right uk-button-primary pw-panel-reload uk-button uk-button-small ui-corner-all mx-2"
            data-panel-width="%s" data-tab-text="Edit" data-tab-offset="200" data-tab-icon="close"
            data-reload="true" data-barba-prevent data-buttons="button.ui-button[type=submit]" data-autoclose>%s</a>',
            $btn['href'] ?? '#',
            $width,
            $btn['label'] ?? 'Button'
        );
    }

    /**
     * Generar acciones (edit, delete, extras)
     */
    public function createActions($params, $id){

        $actions = '';

        // ===== DELETE
        if(!empty($params['showButtonDelete'])){
            if ($this->user->hasPermission($params['permission'] . '-delete')) {
                $actions .= sprintf(
                    '<a href="%sid=%s" class="delete-item uk-button uk-button-danger uk-button-small mx-2">Delete</a>',
                    $params['urlDelete'] ?? '#',
                    $id
                );
            }
        }

        // ===== EDIT
        if(!empty($params['showButtonEdit'])){
            if ($this->user->hasPermission($params['permission'] . '-edit')) {

                $btn = [
                    'href' => ($params['urlEdit'] ?? '#') . 'id=' . $id,
                    'label' => 'Edit'
                ];

                $type = $params['buttonEditType'] ?? 'link';

                if($type === 'panel'){
                    $actions .= $this->createButtonPanel($btn);
                } elseif($type === 'modal'){
                    $actions .= $this->createButtonModal($btn);
                } else {
                    $actions .= $this->createButtonLink($btn);
                }
            }
        }

        // ===== EXTRA ACTIONS
        if (!empty($params['actionsExtra']) && is_array($params['actionsExtra'])) {
            foreach ($params['actionsExtra'] as $act) {

                $extra = !empty($act['itemID']) ? $act['itemID'] . '=' . $id : '';

                $attributes = '';
                if (!empty($act['class'])) $attributes .= 'class="' . $act['class'] . '" ';
                if (!empty($act['data'])) $attributes .= $act['data'] . ' ';
                if (!empty($act['id'])) $attributes .= 'id="' . $act['id'] . '" ';
                if (!empty($act['click'])) $attributes .= $act['click'] . ' ';

                $actions .= sprintf(
                    '<a href="%s%s" %s>%s</a>',
                    $act['href'] ?? '#',
                    $extra,
                    $attributes,
                    $act['label'] ?? 'Action'
                );
            }
        }

        return $actions;
    }

    /**
     * Estado activo/inactivo en tablas
     */
    public function tableFieldActive($item){
        return ($item->active == 1) ? 'Actived' : 'No Actived';
    }

    /**
     * Setear clave de cifrado (32 chars)
     */
    public function setKey($key){
        if (strlen($key) !== 32) {
            throw new \Exception("La clave debe tener 32 caracteres.");
        }
        $this->key = $key;
    }

    /**
     * Encriptar array
     */
    public function encrypt(array $data){
        $iv = random_bytes(16);

        $encryptedData = openssl_encrypt(
            json_encode($data),
            'aes-256-cbc',
            $this->key,
            0,
            $iv
        );

        return [
            'iv' => base64_encode($iv),
            'data' => $encryptedData,
        ];
    }

    /**
     * Desencriptar array
     */
    public function decrypt(array $encryptedData){
        $iv = base64_decode($encryptedData['iv']);

        $decryptedData = openssl_decrypt(
            $encryptedData['data'],
            'aes-256-cbc',
            $this->key,
            0,
            $iv
        );

        return json_decode($decryptedData, true);
    }

    public function ___install() {
        parent::___install();
    }

    public function ___uninstall() {
        parent::___uninstall();
    }
}