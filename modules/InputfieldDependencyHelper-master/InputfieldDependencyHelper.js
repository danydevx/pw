$(document).ready(function() {

	// Field value menu items
	$field_lis = $('.idh-menu li[data-field]');

	// Hide all IDH menus
	function hideMenus() {
		$('.idh-menu').removeClass('open');
		$field_lis.removeClass('open');
	}

	// Hide menus when clicking away
	// Have to use mousedown due to PW core stopping click propagation on many elements
	$(document).on('mousedown', function(event) {
		const $target = $(event.target);
		if(!$target.closest('.idh-menu').length) {
			hideMenus();
		}
	});

	// Show menu when button clicked
	$('.idh-menu button').on('click', function() {
		hideMenus();
		$(this).parent().addClass('open');
	});

	// Insert value when menu item clicked
	$(document).on('click', '.idh-menu li[data-insert]', function() {
		hideMenus();
		const $input = $(this).closest('.InputfieldContent').find('input');
		$input.val($input.val() + $(this).data('insert')).trigger('focus');
	});

	// Load field values into menu via AJAX
	$field_lis.on('click', function() {
		const $li = $(this);
		$field_lis.removeClass('open');
		const $icon = $li.find('> i');
		$li.addClass('open');
		// If no child list has been loaded yet
		if(!$li.children('ul').length) {
			// Switch caret icon to spinner
			$icon.removeClass('fa-caret-right').addClass('fa-spin fa-spinner');
			// Get values
			$.getJSON('/idh-field-values/' + $(this).data('field'), function(data) {
				if(!data || $.isEmptyObject(data)) {
					$icon.removeClass('fa-spin fa-spinner').addClass('fa-caret-right');
					return;
				}
				// Append values list
				const $ul = $('<ul class="idh-submenu"></ul>');
				$.each(data, function(key, value) {
					$ul.append($(`<li data-insert="${key}">${value}</li>`));
				});
				$li.append($ul);
				// Switch icon back to caret
				$icon.removeClass('fa-spin fa-spinner').addClass('fa-caret-right');
			});
		}
	});

});
