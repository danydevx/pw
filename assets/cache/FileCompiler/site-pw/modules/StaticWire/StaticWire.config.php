<?php

use ProcessWire\ModuleConfig;

class StaticWireConfig extends \ProcessWire\ModuleConfig {

	public function __construct() {
		$moduleAssetOptions = $this->getModuleAssetOptions();
		$moduleAssetDefaults = array_keys($moduleAssetOptions);

		$this->add([
			[	
				'name' => 'rootPath',
				'type' => 'text',
				'label' => $this->_('Static file path'),
				'description' => $this->_('Directory to generate the HTML files in.'), 
				'notes' => $this->_('Path relative to website root directory.'), 
				'required' => true, 
				'value' => 'static',
			],
			[
				'name' => 'siteUrl',
				'type' => 'text',
				'label' => $this->_('URL del sitio'),
				'description' => $this->_('URL base para escribir enlaces absolutos en los archivos generados.'),
				'notes' => $this->_('Ejemplo: https://misitio.com'),
				'value' => rtrim((string) $this->wire('config')->httpHost . (string) $this->wire('config')->urls->httpRoot, '/'),
			],
			[
				'name' => 'allowedModuleAssets',
				'type' => 'checkboxes',
				'label' => $this->_('Assets de modulos permitidos'),
				'description' => $this->_('Selecciona los modulos cuyos JS/CSS se exportan al sitio estatico.'),
				'options' => $moduleAssetOptions,
				'value' => $moduleAssetDefaults,
			],
			[
				'name' => 'allowedModuleAssetsTools',
				'type' => 'markup',
				'label' => $this->_('Acciones rapidas'),
				'value' => '<button type="button" class="ui-button" id="staticwire-deselect-modules">Deseleccionar todos</button>'
					. '<script>(function(){var b=document.getElementById("staticwire-deselect-modules");if(!b)return;b.addEventListener("click",function(){var i=document.querySelectorAll("input[name^=\"allowedModuleAssets\"]");for(var x=0;x<i.length;x++){i[x].checked=false;i[x].dispatchEvent(new Event("change",{bubbles:true}));}});})();</script>',
			],
			[
				'name' => 'compressHtml',
				'type' => 'checkbox',
				'label' => $this->_('Comprimir HTML'),
				'value' => 0,
			],
			[
				'name' => 'compressCss',
				'type' => 'checkbox',
				'label' => $this->_('Comprimir CSS'),
				'value' => 0,
			],
			[
				'name' => 'compressJs',
				'type' => 'checkbox',
				'label' => $this->_('Comprimir JS'),
				'value' => 0,
			],
		]); 
	}

	protected function getModuleAssetOptions(): array
	{
		$options = [];
		$roots = [
			['path' => (string) $this->wire('config')->paths->siteModules, 'url' => '/site/modules/'],
			['path' => (string) $this->wire('config')->paths->wire . 'modules/', 'url' => '/wire/modules/'],
		];

		foreach($roots as $root) {
			$rootPath = rtrim($root['path'], '/') . '/';
			if(!is_dir($rootPath)) continue;

			$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS));
			foreach($it as $fileInfo) {
				if(!$fileInfo->isFile()) continue;
				$ext = strtolower(pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION));
				if($ext !== 'js' && $ext !== 'css') continue;

				$filePath = str_replace('\\', '/', $fileInfo->getPathname());
				$relative = substr($filePath, strlen($rootPath));
				$segments = explode('/', $relative);
				if(empty($segments[0])) continue;

				$prefix = rtrim($root['url'], '/') . '/' . trim($segments[0], '/') . '/';
				if(!isset($options[$prefix])) {
					$options[$prefix] = $segments[0] . ' (' . $prefix . ')';
				}
			}
		}

		asort($options, SORT_NATURAL | SORT_FLAG_CASE);
		return $options;
	}

}
