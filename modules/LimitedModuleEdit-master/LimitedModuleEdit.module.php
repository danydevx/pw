<?php namespace ProcessWire;

class LimitedModuleEdit extends WireData implements Module, ConfigurableModule {

	/**
	 * Ready
	 */
	public function ready() {
		$this->addHookAfter('AdminThemeFramework::getPrimaryNavArray', $this, 'afterPrimaryNavArray');
		$this->addHookBefore('ProcessController::execute', $this, 'beforeProcessController');
		$this->addHookBefore('ProcessModule::executeEdit', $this, 'beforeModuleEdit');
		$this->addHookBefore('Modules::saveConfig', $this, 'beforeSaveConfig');
	}

	/**
	 * After AdminThemeFramework::getPrimaryNavArray
	 * Replace normal modules menu with a custom menu
	 *
	 * @param HookEvent $event
	 */
	protected function afterPrimaryNavArray(HookEvent $event) {
		$user = $this->wire()->user;
		// Only applicable to non-superusers
		if($user->isSuperuser()) return;
		$nav_items = $event->return;

		// Don't allow the normal "Modules" section to appear in the menu
		$modules_title = '';
		foreach($nav_items as $key => $item) {
			if($item['id'] === 21) {
				unset($nav_items[$key]);
			}
		}

		if($this->noMenu) {
			$event->return = $nav_items;
			return;
		}

		// Are there any modules this user is allowed to configure?
		$user_configurable = [];
		foreach($this->lmeConfigurable as $item) {
			$p_name = $this->getPermissionName($item);
			if(!$user->hasPermission($p_name)) continue;
			$info = $this->wire()->modules->getModuleInfo($item);
			$user_configurable[$item] = $info['icon'] ?: 'plug';
		}

		// If so then add a custom "Modules" section to the menu
		if($user_configurable) {
			$base = $this->wire()->config->urls->admin . 'module/edit?name=';
			$nav_item = [
				'id' => -1,
				'parent_id' => 0,
				'name' => '',
				'title' => $this->_x('Modules', $this->className),
				'url' => '#', // Top-level menu item is not navigable
				'icon' => '',
				'children' => [],
				'navJSON' => '',
			];
			foreach($user_configurable as $name => $icon) {
				$nav_item['children'][] = [
					'id' => 0,
					'parent_id' => 0,
					'name' => '',
					'title' => $name,
					'url' => $base . $name . '&collapse_info=1',
					'icon' => $icon,
					'children' => [],
					'navJSON' => '',
				];
			}
			$nav_items[] = $nav_item;
		}

		$event->return = $nav_items;
	}

	/**
	 * Before ProcessController::execute
	 * Place restrictions on the use of ProcessModule
	 *
	 * @param HookEvent $event
	 */
	protected function beforeProcessController(HookEvent $event) {
		$user = $this->wire()->user;
		$config = $this->wire()->config;
		// Only applicable to non-superusers
		if($user->isSuperuser()) return;

		// Prevent admin menus from being cached
		$this->wire()->session->removeFor('AdminThemeUikit', 'prnav');
		$this->wire()->session->removeFor('AdminThemeUikit', 'sidenav');
		
		// Add CSS
		$info = $this->wire()->modules->getModuleInfo($this->className);
		$version = $info['version'];
		$config->styles->add($config->urls->{$this} . "$this.css?v=$version");

		/** @var ProcessController $pc */
		$pc = $event->object;
		$process = $pc->getProcess();
		if($process != 'ProcessModule') return;
		$method = $pc->getProcessMethodName($process);

		// If attempting to use the main execute method then redirect to the admin root
		// The user may have navigated there from the breadcrumb menu
		if($method === 'execute') {
			$this->wire()->session->location($this->wire()->config->urls->admin);
		}

		$error_notice = $this->_('Access denied.');

		// Only the edit method is allowed
		if($method !== 'executeEdit') {
			$event->replace = true;
			$event->return = $error_notice;
		}

		// The user is only allowed to edit modules they have the corresponding permission for
		$module_name = $this->wire()->input->get('name');
		$p_name = $this->getPermissionName($module_name);
		if(!$user->hasPermission($p_name)) {
			$event->replace = true;
			$event->return = $error_notice;
		}
	}

	/**
	 * Before ProcessModule::executeEdit
	 * Don't allow users to uninstall modules
	 *
	 * @param HookEvent $event
	 */
	protected function beforeModuleEdit(HookEvent $event) {
		// Only applicable to non-superusers
		if($this->wire()->user->isSuperuser()) return;
		// Remove the uninstall checkbox
		$event->wire()->addHookBefore('InputfieldForm::render', function(HookEvent $event2) {
			/** @var InputfieldForm $form */
			$form = $event2->object;
			$form->remove('uninstall');
		});
	}

	/**
	 * Config inputfields
	 *
	 * @param InputfieldWrapper $inputfields
	 */
	public function getModuleConfigInputfields($inputfields) {
		$modules = $this->wire()->modules;

		/** @var InputfieldCheckboxes $f */
		$f = $modules->get('InputfieldCheckboxes');
		$f_name = 'lmeConfigurable';
		$f->name = $f_name;
		$f->label = $this->_('Modules enabled for limited editing');
		$f->description = $this->_('Enabled modules will have a corresponding permission installed. Example: If WireMailSmtp is enabled here then a permission named "lme-wire-mail-smtp" will be installed. For any role you can activate one or more of these "lme" permissions to allow users with that role to configure the corresponding module.');
		$module_names = $modules->getKeys();
		asort($module_names);
		foreach($module_names as $name) {
			if($name == $this) continue;
			if(!$modules->isConfigable($name)) continue;
			$f->addOption($name);
		}
		$f->optionWidth = 300;
		$f->value = $this->$f_name;
		$inputfields->add($f);

		/** @var InputfieldCheckbox $f */
		$f = $modules->get('InputfieldCheckbox');
		$f_name = 'noMenu';
		$f->name = $f_name;
		$f->label = $this->_("Don't show a Modules menu");
		$f->description = $this->_('May be useful if you are providing alternative links to edit the allowed modules.');
		$f->checked = $this->$f_name === 1 ? 'checked' : '';
		$f->collapsed = Inputfield::collapsedBlank;
		$inputfields->add($f);
	}

	/**
	 * Before Modules::saveConfig
	 *
	 * @param HookEvent $event
	 */
	protected function beforeSaveConfig(HookEvent $event) {
		// Only for this module's config data
		if($event->arguments(0) != $this) return;
		$data = $event->arguments(1);
		$permissions = $this->wire()->permissions;

		// Get all existing LME permissions
		$existing_lme_permissions = $permissions->find("name^=lme-");

		// Create any new LME permissions
		$lme_permissions = [];
		foreach($data['lmeConfigurable'] as $item) {
			$p_name = $this->getPermissionName($item);
			$lme_permissions[] = $p_name;
			if($existing_lme_permissions->get("name=$p_name")) continue;
			$p = $permissions->add($p_name);
			$p->title = sprintf($this->_('Configure the %s module via LimitedModuleEdit'), $item);
			$p->save('title');
		}

		// Delete any LME permissions that are no longere enabled
		$lme_permissions_str = implode('|', $lme_permissions);
		foreach($existing_lme_permissions->find("name!=$lme_permissions_str") as $permission) {
			$permissions->delete($permission);
		}
	}

	/**
	 * Get the LME permission name for the given module name
	 *
	 * @param string $module
	 * @return string
	 */
	protected function getPermissionName($module) {
		return 'lme-' . $this->wire()->sanitizer->kebabCase($module);
	}

	/**
	 * Install
	 */
	public function ___install() {
		// Install the module-admin permission
		$p = $this->wire()->permissions->add('module-admin');
		$p->title = $this->_('Administer modules');
		$p->save('title');
	}

	/**
	 * Uninstall
	 */
	public function ___uninstall() {
		// Delete all related permissions
		$permissions = $this->wire()->permissions;
		$lme_permissions = $permissions->find("name^=lme-");
		foreach($lme_permissions as $p) {
			$permissions->delete($p);
		}
		$p = $permissions->get('module-admin');
		if($p && $p->id) $permissions->delete($p);
	}

}
