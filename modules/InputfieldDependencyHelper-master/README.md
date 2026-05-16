# Inputfield Dependency Helper

Adds "Insert field name" and "Insert value" dropdown menus to help with constructing show-if/required-if conditions, aka [inputfield dependencies](https://processwire.com/docs/fields/dependencies/). 

The "Insert field name" menu helps you remember the field names that exist in your site (or exist in the current template) and avoids typos.

The "Insert value" menu lets you select values for Page Reference or Select Options fields via the human-friendly label whereas the show-if/required-if conditions require those values to be inserted as numerical IDs.

![idh-1](https://github.com/user-attachments/assets/bf2913af-cf45-449a-b51d-a23a057fc434)

## Insert field name

When you click the button a dropdown menu appears listing field names, with the field labels in parentheses. When editing a field in template context only the fields that exist in the template are listed, and the field labels are in template context. When editing a field outside of template context all the non-system fields are listed. When you click an item in the list the field name is inserted into the settings field.

## Insert value

When using a Page Reference or Select Options field value in a show-if/required-if condition you have to enter the numerical ID of the page/option, and this is not so user-friendly – often you have to switch to another tab and go and look up the relevant ID. The "Insert value" button is intended to make this process easier.

When you click the button a dropdown menu appears listing any Page Reference and Select Options fields that exist in the current template (when editing in template context) or in the site. When you click one of the fields the selectable options for the field are AJAX-loaded into a flyout menu. Clicking one of the selectable options will insert the numerical ID of the option into the settings field.

## Configuration

In the module config you can set a limit to the number of selectable options shown in the menu, so the menu doesn't get excessively long.
