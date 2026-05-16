<?php namespace ProcessWire;

// Optional initialization file, called before rendering any template file.
// This is defined by $config->prependTemplateFile in /site/config.php.
// Use this to define shared variables, functions, classes, includes, etc. 

if (isset($config, $page)) {
	if (!$config->production || $page->template->name === 'http404') {
		$page->set('skip_minify', 1);
	}
}
