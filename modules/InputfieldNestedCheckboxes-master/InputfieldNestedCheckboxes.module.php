<?php namespace ProcessWire;

class InputfieldNestedCheckboxes extends InputfieldCheckboxes implements InputfieldHasArrayValue {

	protected $optionIdsString;

	protected $markupAttr = [];

	/**
	 * Init
	 */
	public function init() {
		$this->structure = 'parents';
		parent::init();
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	public function ___render() {
		$pages = $this->wire()->pages;

		// Show an error message if the inputfield is not used with a Page Reference field
		if($this->hasInputfield != 'InputfieldPage') {
			return $this->_('Error: InputfieldNestedCheckboxes is only for use with Page Reference fields.');
		}

		$options = $this->getOptions();
		$this->optionIdsString = implode('|', array_keys($options));
		$parents = $pages->find("children=$this->optionIdsString, include=hidden");
		$out = '';

		// Below is taken from InputfieldCheckboxes
		$columns = (int) $this->optionColumns;
		$inline = $columns === 1 || $columns > 10;
		$liAttr = '';
		$ulClass = '';
		$optionWidth = $this->optionWidth ? $this->getOptionWidthCSS($this->optionWidth, $options) : '';
		if($optionWidth) {
			$liAttr = " style='width:$optionWidth'";
			$ulClass = 'InputfieldCheckboxesWidth';
		} else if($columns) {
			if($inline) {
				$ulClass = 'InputfieldCheckboxesFloated';
			} else {
				$liWidth = round(100 / $columns)-1;  // 1% padding-right added from stylesheet
				$liAttr = " style='width: {$liWidth}%;'";
				$ulClass = 'InputfieldCheckboxesColumns';
			}
			$classes = InputfieldWrapper::getClasses();
			$ulClass .= " " . $classes['list_clearfix'];
		} else {
			$ulClass = 'InputfieldCheckboxesStacked';
		}

		// Set markup attributes so they are available to renderParent()
		$this->markupAttr['ulClass'] = $ulClass;
		$this->markupAttr['liAttr'] = $liAttr;

		if($this->structure === 'grandparents') {
			// Render grandparents and parents
			$grandparents = $pages->find("children=$parents, include=hidden");
			foreach($grandparents as $grandparent) {
				$out .= "<div class='inc-grandparent'>";
				$out .= "<i class='fa fa-angle-down inc-branch-toggle'></i>";
				$label = $this->wire()->sanitizer->entities1($grandparent->title);
				$out .= "<label><input type='checkbox' class='uk-checkbox' name='_inc_parent'>$label</label>";
				foreach($grandparent->children("id=$parents|$this->optionIdsString") as $item) {
					if(isset($options[$item->id])) {
						$out .= $this->renderOption($item);
					} else {
						$out .= $this->renderParent($item);
					}
				}
				$out .= "</div>";
			}
		} else {
			// Render parents
			foreach($parents as $parent) {
				$out .= $this->renderParent($parent);
			}
		}

		return "<div class='inc-container' data-collapse='$this->collapseStructure'>$out</div>";
	}

	/**
	 * Render a parent structure
	 */
	protected function renderParent($parent) {
		$out = "<div class='inc-parent'>";
		$out .= "<i class='fa fa-angle-down inc-branch-toggle'></i>";
		$label = $this->wire()->sanitizer->entities1($parent->title);
		$out .= "<label><input type='checkbox' class='uk-checkbox' name='_inc_parent'>$label</label>";
		$out .= "<ul class='{$this->markupAttr['ulClass']}'>";
		foreach($parent->children("id=$this->optionIdsString") as $child) {
			$element = $this->renderOption($child);
			$out .= "<li{$this->markupAttr['liAttr']}>$element</li>";
		}
		$out .= "</ul>";
		$out .= "</div>";
		return $out;
	}

	/**
	 * Render an individual checkbox option
	 */
	protected function renderOption($page) {
		$options = $this->getOptions();

		$checked = '';
		if($this->isOptionSelected($page->id)) $checked = " checked='checked'";

		$attrs = $this->getOptionAttributes($page->id);
		$disabled = empty($attrs['disabled']) ? '' : " disabled='disabled'";
		unset($attrs['checked'], $attrs['selected'], $attrs['disabled']);
		$attrs = $this->getOptionAttributesString($attrs);
		if($attrs) $attrs = ' ' . $attrs;

		return "<label$attrs><input$checked$disabled type='checkbox' name='{$this->name}[]' class='uk-checkbox' value='$page->id'>{$options[$page->id]}</label>";
	}

	/**
	 * Config inputfields
	 */
	public function ___getConfigInputfields() {
		$inputfields = parent::___getConfigInputfields();
		$modules = $this->wire()->modules;
		
		/** @var InputfieldRadios $f */
		$f = $modules->get('InputfieldRadios');
		$name = 'structure';
		$f->name = $name;
		$f->label = $this->_('Checkboxes structure');
		$f->addOption('parents', $this->_('Parents'));
		$f->addOption('grandparents', $this->_('Parents and grandparents'));
		$f->optionColumns = 1;
		$f->value = $this->$name;
		$inputfields->add($f);

		/** @var InputfieldCheckbox $f */
		$f = $modules->get('InputfieldCheckbox');
		$name = 'collapseStructure';
		$f->name = $name;
		$f->label = $this->_('Collapse sections that contain no checked checkboxes');
		$f->checked = $this->$name === 1 ? 'checked' : '';
		$inputfields->add($f);

		return $inputfields;
	}

	/**
	 * Install
	 * Add module as a selection inputfield to InputfieldPage
	 */
	public function ___install() {
		$data = $this->wire()->modules->getConfig('InputfieldPage');
		$data['inputfieldClasses'][] = $this->className();
		$this->wire()->modules->saveConfig('InputfieldPage', $data);
	}

	/**
	 * Uninstall
	 * Remove module as a selection inputfield from InputfieldPage
	 */
	public function ___uninstall() {
		$data = $this->wire()->modules->getConfig('InputfieldPage');
		foreach($data['inputfieldClasses'] as $key => $value) {
			if($value == $this->className()) unset($data['inputfieldClasses'][$key]);
		}
		$this->wire()->modules->saveConfig('InputfieldPage', $data);
	}

}
