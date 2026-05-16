# Nested Checkboxes

An inputfield for Page Reference fields that groups options by their parent page, and optionally by grandparent page too. 

This can help editors understand the grouping of the selectable pages, and also makes it quicker for an editor to select or unselect an entire group of pages.

![Screen recording](https://github.com/user-attachments/assets/ad84f144-9223-4633-8da8-8b48acae5aaa)

The checkboxes at the parent and grandparent level are not for storing those pages in the field value - only for quickly selecting or unselecting groups of pages at the lowest level of the hierarchy. For example, in the screen recording above the "Cities" Page Reference field allows only pages with the "city" template, and the pages at the country and continent level are not included in the field value.

The inputfield is only for use with Page Reference fields because the structure comes from the page tree.

Requires PW >= v3.0.248.

## Configuration

For each field that uses the inputfield you have these options:

- Checkboxes structure: choose "Parents" or "Parents and grandparents".
- Collapse sections that contain no checked checkboxes: this option makes the inputfield more compact.
- There are also the standard column width and column quantity options familiar from the InputfieldCheckboxes inputfield. These apply to the selectable pages at the lowest level of the hierarchy, and the structure is arguably more readable when these are left at their defaults.
