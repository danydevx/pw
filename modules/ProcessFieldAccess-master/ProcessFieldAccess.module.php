<?php namespace ProcessWire;

class ProcessFieldAccess extends Process {

	/**
	 * Execute
	 */
	public function ___execute() {
		$config = $this->wire()->config;
		$modules = $this->wire()->modules;
		$adminUrl = $config->urls->admin;
		$labels = [
			'field' => $this->_('Field'),
			'access' => $this->_('Control'),
			'view' => $this->_('View'),
			'edit' => $this->_('Edit'),
			'show' => $this->_('Show'),
			'api' => $this->_('API'),
			'overrides' => $this->_('Overrides'),
			'template' => $this->_('Template'),
			'no-roles' => $this->_('[no roles]'),
		];
		$wrapper = new InputfieldWrapper();

		// Information
		/** @var InputfieldMarkup $f */
		$f = $modules->get('InputfieldMarkup');
		$f->label = $this->_('Information');
		$f->icon = 'info-circle';
		$f->addClass('pfa-information');
		$f->collapsed = Inputfield::collapsedYes;
		$markdown = $this->_("###Table column headers\n\n- **Control:** Is access control enabled for this field?\n- **View:** Roles that can view the field\n- **Edit:** Roles that can edit the field\n- **Show:** Show field in page editor if viewable but not editable (user can see but not change)\n- **API:** Make field value accessible from API even if not viewable\n- **Overrides:** Overrides of the field access settings in template context\n\n###Tips\n- If the guest role has view access then it means that all roles have view access. You can hover the guest role in the View column to see a tooltip with all the role names if you want a reminder of those.\n- Overrides: when access control is enabled as a template override, the Control, View, Edit, Show and API columns only display settings that are different from the field access settings. If a column is empty it means the field access setting applies.");
		$f->value = $this->wire()->sanitizer->entitiesMarkdown($markdown, true);
		$wrapper->add($f);

		/** @var InputfieldMarkup $f */
		$f = $modules->get('InputfieldMarkup');
		$f->name = 'filter_by_field';
		$f->label = $this->_('Filter by field name');
		$f->icon = 'search';
		$f->value = <<<EOT
<div class="pfa-filter-wrap">
	<input class="uk-input pfa-filter" id="pfa-field-filter" type="text">
	<i class="fa fa-times-circle pfa-icon-clear"></i>
</div>
EOT;
		$f->columnWidth = 50;
		$wrapper->add($f);

		/** @var InputfieldMarkup $f */
		$f = $modules->get('InputfieldMarkup');
		$f->name = 'filter_by_template';
		$f->label = $this->_('Filter by template name');
		$f->icon = 'search';
		$f->value = <<<EOT
<div class="pfa-filter-wrap">
	<input class="uk-input pfa-filter" id="pfa-template-filter" type="text">
	<i class="fa fa-times-circle pfa-icon-clear"></i>
</div>
EOT;
		$f->columnWidth = 50;
		$wrapper->add($f);

		// Table
		/** @var $table MarkupAdminDataTable */
		$table = $modules->get('MarkupAdminDataTable');
		$table->setID($this->className . 'Table');
		$table->encodeEntities = false;
		$table->sortable = false;

		// Table header row
		$table->headerRow([
			$labels['field'],
			$labels['access'],
			$labels['view'],
			$labels['edit'],
			$labels['show'],
			$labels['api'],
			$labels['overrides'],
		]);

		// Get names for all roles, excluding superuser
		$roleNames = $this->wire()->roles->find('id!=38')->explode('name', ['key' => 'id']);
		$roleNamesStr = implode(', ', $roleNames);
		$this->roleNames = $roleNames;
		$editRoleNames = $roleNames;
		unset($editRoleNames[37]); // exclude guest

		// Default roles for view
		$defaultViewRoles = array_keys($roleNames);

		// Default roles for edit
		$defaultEditRoles = array_keys($editRoleNames);

		// Get all fields that support access control
		$allFields = $this->wire()->fields->find("type!=FieldtypeFieldsetOpen|FieldtypeFieldsetClose|FieldtypeFieldsetTabOpen, sort=name");
		foreach($allFields as $field) {

			// Skip system fields apart from title
			if($field->flags & Field::flagSystem && $field->name !== 'title') continue;

			// Set default roles
			$fieldViewRoles = $defaultViewRoles;
			$fieldEditRoles = $defaultEditRoles;

			// Field link
			$fieldLink = "<a href='{$adminUrl}setup/field/edit?id={$field->id}#access'>$field->name</a>";

			// Field access settings
			$fieldAccessIcon = $field->useRoles ? '<i class="fa fa-check"></i>' : '';
			$fieldViewRoleItems = [];
			$fieldViewStr = '';
			$fieldEditRoleItems = [];
			$fieldEditStr = '';
			$fieldShowIcon = '';
			$fieldApiIcon = '';
			if($field->useRoles) {

				// View roles
				$fieldViewRoles = $field->viewRoles;
				foreach($fieldViewRoles as $id) $fieldViewRoleItems[] = "<span class='pfa-role' data-role-id='$id'>{$roleNames[$id]}</span>";
				$fieldViewStr = implode(', ', $fieldViewRoleItems);
				if(!$fieldViewStr) $fieldViewStr = $labels['no-roles'];

				// Edit roles
				$fieldEditRoles = $field->editRoles;
				foreach($fieldEditRoles as $id) $fieldEditRoleItems[] = "<span class='pfa-role'>{$roleNames[$id]}</span>";
				$fieldEditStr = implode(', ', $fieldEditRoleItems);
				if(!$fieldEditStr) $fieldEditStr = $labels['no-roles'];

				// Show in editor
				$fieldShowIcon = $field->flags & Field::flagAccessEditor ? '<i class="fa fa-check"></i>' : '';

				// API access
				$fieldApiIcon = $field->flags & Field::flagAccessAPI ? '<i class="fa fa-check"></i>' : '';
			}

			// Overrides table
			/** @var $overridesTable MarkupAdminDataTable */
			$overridesTable = $modules->get('MarkupAdminDataTable');
			$overridesTable->encodeEntities = false;
			$overridesTable->sortable = false;
			$overridesTable->setClass('pfa-overrides-table');

			// Overrides table header row
			$overridesTable->headerRow([
				$labels['template'],
				$labels['access'],
				$labels['view'],
				$labels['edit'],
				$labels['show'],
				$labels['api'],
			]);

			// Check for access control overrides for each fieldgroup
			foreach($this->wire()->fieldgroups->find("sort=name") as $fieldgroup) {
				if(!$fieldgroup->getField($field->name)) continue;
				if(!$fieldgroup->hasFieldContext($field)) continue;
				$context = $fieldgroup->getFieldContextArray($field->id);
				if(empty($context['editRoles']) && empty($context['flagsAdd']) && empty($context['flagsDel'])) continue;

				// Template link
				$templateLink = "<a href='{$adminUrl}setup/field/edit?id={$field->id}&fieldgroup_id={$fieldgroup->id}#access'>$fieldgroup->name</a>";

				// Template access
				$templateAccess = $field->useRoles;
				if($field->useRoles) {
					$templateAccessIcon = '';
					if(isset($context['flagsDel']) && $context['flagsDel'] & Field::flagAccess) {
						$templateAccess = false;
						$templateAccessIcon = '<i class="fa fa-minus-circle"></i>';
					}
				} else {
					if(isset($context['flagsAdd']) && $context['flagsAdd'] & Field::flagAccess) {
						$templateAccess = true;
						$templateAccessIcon = '<i class="fa fa-check"></i>';
					}
				}

				// Default values
				$templateViewRoleItems = [];
				$templateViewStr = '';
				$templateEditRoleItems = [];
				$templateEditStr = '';
				$templateShowIcon = '';
				$templateApiIcon = '';

				if($templateAccess) {

					// View roles
					if(isset($context['viewRoles'])) {
						// If guest has view access then all roles have view access
						if(in_array(37, $fieldViewRoles)) $fieldViewRoles = $defaultViewRoles;
						if(in_array(37, $context['viewRoles'])) $context['viewRoles'] = $defaultViewRoles;
						if($context['viewRoles'] !== $fieldViewRoles) {
							$addViewRoles = array_diff($context['viewRoles'], $fieldViewRoles);
							$deleteViewRoles = array_diff($fieldViewRoles, $context['viewRoles']);
							foreach($addViewRoles as $id) $templateViewRoleItems[] = "<span class='pfa-role pfa-role-add' data-role-id='$id'>{$roleNames[$id]}</span>";
							foreach($deleteViewRoles as $id) $templateViewRoleItems[] = "<span class='pfa-role pfa-role-delete'>{$roleNames[$id]}</span>";
							$templateViewStr = implode(', ', $templateViewRoleItems);
						}
					} else {
						$templateViewStr = $field->useRoles ? '' : $labels['no-roles'];
					}

					// Edit roles
					if(isset($context['editRoles'])) {
						if($context['editRoles'] !== $fieldEditRoles) {
							$addEditRoles = array_diff($context['editRoles'], $fieldEditRoles);
							$deleteEditRoles = array_diff($fieldEditRoles, $context['editRoles']);
							foreach($addEditRoles as $id) $templateEditRoleItems[] = "<span class='pfa-role pfa-role-add'>{$roleNames[$id]}</span>";
							foreach($deleteEditRoles as $id) $templateEditRoleItems[] = "<span class='pfa-role pfa-role-delete'>{$roleNames[$id]}</span>";
							$templateEditStr = implode(', ', $templateEditRoleItems);
						}
					} else {
						$templateEditStr = $field->useRoles ? '' : $labels['no-roles'];
					}

					// Show in editor
					if($fieldShowIcon) {
						if(isset($context['flagsDel']) && $context['flagsDel'] & Field::flagAccessEditor) {
							$templateShowIcon = '<i class="fa fa-minus-circle"></i>';
						}
					} else {
						if(isset($context['flagsAdd']) && $context['flagsAdd'] & Field::flagAccessEditor) {
							$templateShowIcon = '<i class="fa fa-check"></i>';
						}
					}

					// API access
					if($fieldApiIcon) {
						if(isset($context['flagsDel']) && $context['flagsDel'] & Field::flagAccessAPI) {
							$templateApiIcon = '<i class="fa fa-minus-circle"></i>';
						}
					} else {
						if(isset($context['flagsAdd']) && $context['flagsAdd'] & Field::flagAccessAPI) {
							$templateApiIcon = '<i class="fa fa-check"></i>';
						}
					}
				}

				// Add row to overrides table
				$overridesTable->row([
					[$templateLink, 'pfa-template-link'],
					[$templateAccessIcon, 'pfa-template-access'],
					[$templateViewStr, 'pfa-template-view-roles'],
					[$templateEditStr, 'pfa-template-edit-roles'],
					[$templateShowIcon, 'pfa-template-show'],
					[$templateApiIcon, 'pfa-template-api'],
				], [
					'class' => 'pfa-template-row',
					'attrs' => ['data-name' => $fieldgroup->name],
				]);

			}

			// Add row to table
			$table->row([
				[$fieldLink, 'pfa-field-link'],
				[$fieldAccessIcon, 'pfa-field-access'],
				[$fieldViewStr, 'pfa-field-view-roles'],
				[$fieldEditStr, 'pfa-field-edit-roles'],
				[$fieldShowIcon, 'pfa-field-show'],
				[$fieldApiIcon, 'pfa-field-api'],
				[$overridesTable->render(), 'pfa-field-overrides'],
			], [
				'class' => 'pfa-field-row',
				'attrs' => ['data-name' => $field->name],
			]);
		}

		$renderedTable = $table->render();
		return $wrapper->render() . "<div id='pfa-table-container' data-role-names='$roleNamesStr'>$renderedTable</div>";
	}

}
