# Limited Module Edit

Allows non-superusers to edit a limited selection of modules.

Of course, there are good reasons why non-superusers are normally not allowed to access the configuration screen of modules so use this module with caution.

## Usage

1\. Install Limited Module Edit.

2\. In the module configuration select one or more modules in the "Modules enabled for limited editing" field.

When you enable a module here a corresponding "lme" permission is installed. For example, if WireMailSmtp is enabled here then a permissioned named "lme-wire-mail-smtp" will be installed.

![lme-1](https://github.com/Toutouwai/LimitedModuleEdit/assets/1538852/b039c7e2-64d4-4f55-b8f0-7129d40125a2)

3\. For any role that you want to allow to configure the previously selected modules, enable the "module-admin" permission and the "lme" permissions for any module they may configure.

![lme-2](https://github.com/Toutouwai/LimitedModuleEdit/assets/1538852/91847505-564b-4a5d-a597-b6a298c0ac92)

4\. Users with these permissions will now see a special Modules section in the main menu that provides links to configure only the modules they have been given permission for. These users are not allowed to install modules nor are they allowed to uninstall the modules they have permission to configure.

![lme-3](https://github.com/Toutouwai/LimitedModuleEdit/assets/1538852/1f7cbf2b-4448-48d0-8864-1c598011e661)
