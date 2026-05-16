$(function() {

	// Filters
	$('.pfa-filter').each(function() {
		const $input = $(this);
		const $wrap = $input.closest('.pfa-filter-wrap');
		const $clear = $wrap.find('.pfa-icon-clear');
		const $fieldRows = $('.pfa-field-row');
		const $templateRows = $('.pfa-template-row');

		// On keyup
		$input.on('keyup', function() {
			const value = $(this).val();
			if($input.is('#pfa-field-filter')) {
				// Field filter
				if(value.length) {
					$fieldRows.addClass('pfa-hidden-by-field');
					$fieldRows.filter(function() {
						return $(this).is(`[data-name*=${value}]`);
					}).removeClass('pfa-hidden-by-field');
					$clear.show();
				} else {
					$fieldRows.removeClass('pfa-hidden-by-field');
					$clear.hide();
				}
			} else {
				// Template filter
				if(value.length) {
					$fieldRows.addClass('pfa-hidden-by-template');
					$templateRows.hide();
					const $showRows = $templateRows.filter(function() {
						return $(this).is(`[data-name*=${value}]`);
					}).show();
					$showRows.closest('.pfa-field-row').removeClass('pfa-hidden-by-template');
					$clear.show();
				} else {
					$fieldRows.removeClass('pfa-hidden-by-template');
					$templateRows.show();
					$clear.hide();
				}
			}
		});

		// Clear button clicked
		$clear.click(function(event) {
			event.preventDefault();
			$input.val('').trigger('focus').trigger('keyup');
		});
	});

	// Show a tooltip listing all roles when hovering on a guest view role item
	const roleNames = $('#pfa-table-container').data('role-names');
	$('span[data-role-id="37"]').on('mouseover', function() {
		UIkit.tooltip(this, {title: roleNames}).show();
	});
});
