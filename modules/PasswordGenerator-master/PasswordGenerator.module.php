<?php namespace ProcessWire;

class PasswordGenerator extends WireData implements Module, ConfigurableModule {

	/**
	 * Ready
	 */
	public function ready() {
		$this->addHookAfter('InputfieldPassword::render', $this, 'addGenerator');
	}

	/**
	 * Add generator to InputfieldPassword
	 */
	public function addGenerator(HookEvent $event) {
		$inputfield = $event->object;
		$out = $event->return;

		// Add JS and CSS
		$config = $this->wire()->config;
		$info = $this->wire()->modules->getModuleInfo($this);
		$version = $info['version'];
		$config->scripts->add($config->urls->$this . "pGenerator.jquery.js?v=$version");
		$config->scripts->add($config->urls->$this . "$this.js?v=$version");
		$config->styles->add($config->urls->$this . "$this.css?v={$version}");

		// Get requirements of this password field
		$requirements = [
			'lower' => false,
			'upper' => false,
			'digit' => false,
			'other' => false,
		];
		foreach($inputfield->requirements as $requirement) {
			if($requirement === 'letter' || $requirement === 'none') {
				$requirements['lower'] = true;
			} else {
				$requirements[$requirement] = true;
			}
		}
		// Set a sensible default length if the password field settings have not been saved
		$length = $inputfield->minlength ?: 10;

		// Override settings
		$override_requirements = $this->requirements ?: [];
		foreach($override_requirements as $requirement) $requirements[$requirement] = true;
		if($this->length) $length = $this->length;

		// Translate requirements into settings needed by pGenerator
		$settings = [];
		$settings['passwordLength'] = $length;
		$settings['lowercase'] = $requirements['lower'];
		$settings['uppercase'] = $requirements['upper'];
		$settings['numbers'] = $requirements['digit'];
		$settings['specialChars'] = $requirements['other'];
		$json = json_encode($settings);

		// Add extra markup
		$copy_button_text = $this->_('Copy to clipboard');
		$generate_button_text = $this->_('Generate password');
		// Using single quotes for data attribute as JSON has double quotes
		$out .= <<<EOT
<div class="pg-wrap" data-pg-settings='$json'>
	<div class="pg-row">
		<div class="pg-display-wrap">
			<div class="pg-display"></div>
			<button type="button" class="pg-copy" title="$copy_button_text"><i class="fa fa-copy"></i></button>
		</div>
		<button type="button" class="pg-generate ui-button">$generate_button_text</button>
	</div>
</div>
EOT;
		$event->return = $out;
	}

	/**
	 * Config inputfields
	 *
	 * @param InputfieldWrapper $inputfields
	 */
	public function getModuleConfigInputfields($inputfields) {
		$modules = $this->wire()->modules;

		/* @var InputfieldFieldset $fs */
		$fs = $modules->InputfieldFieldset;
		$fs->label = $this->_('Password generator settings: overrides');
		$fs->description = $this->_('Any values you set here will override the requirements set for the password inputfield. Useful if you want the generator to create stronger passwords than the inputfield requires.');
		$inputfields->add($fs);

		/* @var InputfieldCheckboxes $f */
		$f = $modules->InputfieldCheckboxes;
		$f_name = 'requirements';
		$f->name = $f_name;
		$f->label = $this->_('Password requirements');
		$f->addOption('lower', $this->_('lowercase letter'));
		$f->addOption('upper', $this->_('uppercase letter'));
		$f->addOption('digit', $this->_('digit'));
		$f->addOption('other', $this->_('symbol/punctuation'));
		$f->value = $this->$f_name;
		$f->columnWidth = 50;
		$fs->add($f);

		/* @var InputfieldInteger $f */
		$f = $modules->InputfieldInteger;
		$f_name = 'length';
		$f->name = $f_name;
		$f->label = $this->_('Password length');
		$f->inputType = 'number';
		$f->value = $this->$f_name;
		$f->columnWidth = 50;
		$fs->add($f);

	}

}
