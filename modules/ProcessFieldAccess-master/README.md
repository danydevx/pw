# Field Access

A Process module that provides an overview of field access settings, including template overrides.

![Screenshot of module](https://github.com/user-attachments/assets/fc498b08-6754-43a4-aba9-3b9d179ab6ae)

## Usage

The table has a sticky header so that the columns can be understood when the table is scrolled. The empty space underneath the table is to allow scrolling to the bottom of the table.

There are fields for filtering the table by field name or by template name.

The field names link to the Access tab of the field settings, and the template names link to edit the access settings for the field in the context of that template.

A collapsed field at the top of the page has information about the meaning of the table column headers, and tips for understanding the values in the table:

### Table column headers

* **Control:** Is access control enabled for this field?
* **View:** Roles that can view the field
* **Edit:** Roles that can edit the field
* **Show:** Show field in page editor if viewable but not editable (user can see but not change)
* **API:** Make field value accessible from API even if not viewable
* **Overrides:** Overrides of the field access settings in template context

### Tips

* If the guest role has view access then it means that all roles have view access. You can hover the guest role in the View column to see a tooltip with all the role names if you want a reminder of those.
* Overrides: when access control is enabled as a template override, the Control, View, Edit, Show and API columns only display settings that are different from the field access settings. If a column is empty it means the field access setting applies.
