<?php namespace ProcessWire;

class ProcessWebPManagerConfig extends ModuleConfig {

    public function __construct() {
        $this->add([
            [
                'name' => 'quality',
                'type' => 'integer',
                'label' => $this->_('Calidad WebP (1-100)'),
                'description' => $this->_('Valor recomendado: 80'),
                'required' => true,
                'value' => 80,
            ],
            [
                'name' => 'mode',
                'type' => 'select',
                'label' => $this->_('Modo de generacion'),
                'options' => [
                    'missing' => $this->_('Solo faltantes'),
                    'force' => $this->_('Forzar regeneracion'),
                ],
                'required' => true,
                'value' => 'missing',
            ],
            [
                'name' => 'batch_limit',
                'type' => 'integer',
                'label' => $this->_('Limite por lote'),
                'description' => $this->_('Cantidad de archivos a procesar por ejecucion.'),
                'required' => true,
                'value' => 200,
            ],
            [
                'name' => 'scan_paths',
                'type' => 'textarea',
                'label' => $this->_('Rutas a escanear (obligatorio)'),
                'description' => $this->_('Una ruta por linea. No se usan rutas por default.'),
                'notes' => $this->_("Ejemplo multi-site:\n/site-ase/assets/files\n/site-ase/assets/themes/asedisplay/images\n/site-orponet/assets/files"),
                'required' => true,
                'value' => '/site-ase/assets/files\n/site-ase/assets/themes/asedisplay/images',
            ],
        ]);
    }
}
