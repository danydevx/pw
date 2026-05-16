<?php 

use ProcessWire\Process;

class StaticWire extends \ProcessWire\Process {

    public function ___execute()
    {
        $form = $this->modules->get('InputfieldForm');
		$f = $this->modules->get('InputfieldButton'); 
		$f->value = $this->_('Generate');
		$f->icon = 'code';
        $f->href = "./generate/"; 
		$f->description = $this->_('Path: ') . $this->getOutputPath();
        $f->notes = $this->_('or run "php site/modules/StaticWire/cli.php" in your ProcessWire root directory');
        $form->add($f);

		return $form->render();
    }	

    public function ___executeGenerate()
    {
        $currentUser = $this->users->getCurrentUser();
        $this->users->setCurrentUser($this->users->getGuestUser());
        $this->export();
        $this->users->setCurrentUser($currentUser);

        $this->message('HTML files generated in '. $this->getOutputPath()); 
        $this->session->redirect('../'); 
    }

    public function cliCommand()
    {
        echo "Exporting static site to: " . $this->getOutputPath() . "\n";
        $this->export();
    }

    protected function export(string $selector = 'include=hidden')
    {
        $exporterFile = $this->config->paths->siteModules . 'StaticWire/HtmlExporter.php';
        if(!is_file($exporterFile)) {
            $exporterFile = \ProcessWire\wire("config")->paths->root . 'site-pw/modules/StaticWire' . '/HtmlExporter.php';
        }

 require_once(\ProcessWire\wire('files')->compile($exporterFile,array('includes'=>true,'namespace'=>true,'modules'=>false,'skipIfNamespace'=>false)));

        $compressHtml = (bool) ($this->compressHtml ?? false);
        $compressCss = (bool) ($this->compressCss ?? false);
        $compressJs = (bool) ($this->compressJs ?? false);
        $siteUrl = (string) ($this->siteUrl ?? '');
        $allowedModuleAssets = $this->allowedModuleAssets ?? [];
        if(!is_array($allowedModuleAssets)) {
            $allowedModuleAssets = [];
        }

        $exporter = new HtmlExporter(
            $selector,
            $this->getOutputPath(),
            $compressHtml,
            $compressCss,
            $compressJs,
            $siteUrl,
            $allowedModuleAssets
        );

        // Replace $session->redirect() in templates
        $redirectHook = $this->wire->addHookBefore('Session::redirect', function ($event) {
            $event->replace = true;
        });

        $exporter->run();

        $this->wire->removeHook($redirectHook);
    }

    protected function getOutputPath()
    {
        return $this->config->paths->root . $this->rootPath;
    }

}
