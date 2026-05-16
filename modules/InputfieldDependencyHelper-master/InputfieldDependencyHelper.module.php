<?php namespace ProcessWire;

class InputfieldDependencyHelper extends WireData implements Module, ConfigurableModule {

	protected $skipFieldTypes = [
		'FieldtypeFieldsetOpen',
		'FieldtypeFieldsetClose',
	];
	protected $valueFieldTypes = [
		'FieldtypePage',
		'FieldtypeOptions',
	];

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->valueLimit = 50;
	}

	/**
	 * Ready
	 */
	public function ready() {
		$this->addHookAfter('ProcessField::buildEditForm', $this, 'afterBuildEditForm');
		$this->addHook('/idh-field-values/{field}', $this, 'getFieldValues');
	}

	/**
	 * URL hook to get selectable field values
	 *
	 * @param HookEvent $event
	 * @return string|array
	 */
	protected function getFieldValues(HookEvent $event) {
		// Only for superuser
		if(!$this->wire()->user->isSuperuser()) return '';
		// Only for AJAX requests
		if(!$this->wire()->config->ajax) return '';

		$field_name = $event->field;
		$field = $this->wire()->fields->get($field_name);
		if(!$field) return '';
		if(!wireInstanceOf($field->type, $this->valueFieldTypes)) return '';
		$out = [];

		// Page Reference field
		if(wireInstanceOf($field->type, 'FieldtypePage')) {
			$dummy_page = $this->wire()->pages->get('/');
			/** @var InputfieldPage $inputfield */
			$inputfield = $field->getInputfield($dummy_page);
			$selectable = $inputfield->getSelectablePages($dummy_page);
			$i = 0;
			foreach($selectable as $p) {
				++$i;
				if($i > $this->valueLimit) break;
				$out[$p->id] = (string) $p->getFormatted('title|name');
			}
		}

		// Select Options field
		elseif(wireInstanceOf($field->type, 'FieldtypeOptions')) {
			$selectable = $field->type->getOptions($field);
			$i = 0;
			foreach($selectable as $option /** @var SelectableOption $option */) {
				++$i;
				if($i > $this->valueLimit) break;
				$out[$option->id] = (string) $option->title;
			}
		}

		// Return array (automatically becomes JSON)
		return $out;
	}

	/**
	 * After ProcessField::buildEditForm
	 *
	 * @param HookEvent $event
	 */
	protected function afterBuildEditForm(HookEvent $event) {
		// Only for superuser
		if(!$this->wire()->user->isSuperuser()) return;

		/** @var InputfieldWrapper $wrapper */
		$wrapper = $event->return;
		/** @var ProcessField $pf */
		$pf = $event->object;
		$field = $pf->getField();
		if(!$field) return;
		$config = $this->wire()->config;

		// Add CSS
		$info = $this->wire()->modules->getModuleInfo($this->className);
		$version = $info['version'];
		$config->styles->add($config->urls->$this . "$this.css?v=$version");
		$config->scripts->add($config->urls->$this . "$this.js?v=$version");

		// Labels
		$labels = [
			'insert_field_name' => $this->_('Insert field name...'),
			'insert_value' => $this->_('Insert value...'),
		];

		// Fields
		$flds = [];
		$fieldgroup_id = (int) $this->wire()->input->get('fieldgroup_id');
		if($fieldgroup_id) {
			// Get fields in the fieldgroup
			$fieldgroup = $this->wire()->fieldgroups->get($fieldgroup_id);
			foreach($fieldgroup as $f) {
				if(wireInstanceOf($f->type, $this->skipFieldTypes)) continue;
				// Get field in template context
				$flds[] = $fieldgroup->getFieldContext($f);
			}
		} else {
			// Get all non-system fields
			foreach($this->wire()->fields as $f) {
				if(wireInstanceOf($f->type, $this->skipFieldTypes)) continue;
				if(($f->flags & Field::flagSystem) && $f->name != 'title') continue;
				$flds[] = $f;
			}
		}

		// Insert field
		$fields_markup = '';
		foreach($flds as $f) {
			$fields_markup .= "<li data-insert='$f->name'>$f->name ($f->label)</li>";
		}
		if($fields_markup) {
			$fields_markup = "<div class='idh-menu'><button type='button'>{$labels['insert_field_name']}</button><ul>$fields_markup</ul></div>";
		}

		// Insert value
		$values_markup = '';
		foreach($flds as $f) {
			if(!wireInstanceOf($f->type, $this->valueFieldTypes)) continue;
			$values_markup .= "<li data-field='$f->name'>$f->name ($f->label) <i class='fa fa-fw fa-caret-right'></i></li>";
		}
		if($values_markup) {
			$values_markup = "<div class='idh-menu'><button type='button'>{$labels['insert_value']}</button><ul>$values_markup</ul></div>";
		}

		// Completed markup
		$markup = "<div class='idh-menus'>{$fields_markup}{$values_markup}</div>";

		// Append to dependency fields
		$show_if = $wrapper->getChildByName('showIf');
		if($show_if) $show_if->appendMarkup($markup);
		$required_if = $wrapper->getChildByName('requiredIf');
		if($required_if) $required_if->appendMarkup($markup);
	}

	/**
	 * Config inputfields
	 *
	 * @param InputfieldWrapper $inputfields
	 */
	public function getModuleConfigInputfields($inputfields) {
		$modules = $this->wire()->modules;
		
		/** @var InputfieldInteger $f */
		$f = $modules->get('InputfieldInteger');
		$f_name = 'valueLimit';
		$f->name = $f_name;
		$f->label = $this->_('Menu limit for selectable options');
		$f->description = $this->_("A limit for selectable pages/options, so the menu doesn't get excessively long.");
		$f->value = $this->$f_name;
		$inputfields->add($f);
	}

}
