$(function() {

	// Initialise InputfieldNestedCheckboxes
	function initInputfieldNestedCheckboxes($inputfield) {

		const $container = $inputfield.find('.inc-container');
		const collapse = $container.data('collapse') === 1;
		const $checkboxes = $inputfield.find('input[type="checkbox"]');
		const $parents = $inputfield.find('.inc-parent');
		const $grandparents = $inputfield.find('.inc-grandparent');

		// Return early if already initialised
		if($container.hasClass('init')) return;

		// Set parent checkboxes for the given elements
		function setParentCheckboxes($elements) {
			$elements.each(function() {
				const $parent = $(this);
				const $checkbox = $parent.find('> label > input[type="checkbox"]');
				const $descendantCheckboxes = $parent.find('input[type="checkbox"]').not($checkbox);
				const countDescendant = $descendantCheckboxes.length;
				const countDescendantChecked = $descendantCheckboxes.filter(':checked').length;
				if(countDescendantChecked === 0) {
					$checkbox.prop('indeterminate', false);
					$checkbox.prop('checked', false);
					if(collapse) $parent.addClass('collapsed');
				} else if(countDescendantChecked === countDescendant) {
					$checkbox.prop('indeterminate', false);
					$checkbox.prop('checked', true);
					if(collapse) $parent.removeClass('collapsed');
				} else {
					$checkbox.prop('indeterminate', true);
					if(collapse) $parent.removeClass('collapsed');
				}
			});
		}

		// Set all parent checkboxes
		function setAllParentCheckboxes() {
			// First parents
			setParentCheckboxes($parents);
			// Then grandparents
			setParentCheckboxes($grandparents);
		}

		// When branch toggle icon clicked
		$inputfield.find('.inc-branch-toggle').on('click', function() {
			$(this).closest('.inc-parent, .inc-grandparent').toggleClass('collapsed');
		});

		// Update child checkboxes when parent checkbox changes
		const $parentCheckboxes = $checkboxes.filter('input[name="_inc_parent"]');
		$parentCheckboxes.on('change', function(event, source) {
			if(source === 'inc-parent') return;
			const $parent = $(this).closest('.inc-parent, .inc-grandparent');
			const $children = $parent.find('input[type="checkbox"]');
			if($(this).is(':checked')) {
				$children.prop('checked', true).trigger('change', ['inc-parent']);
			} else {
				$children.prop('checked', false).trigger('change', ['inc-parent']);
			}
		});

		// Set parent checkboxes when any checkbox changes
		$checkboxes.on('change', function(event, source) {
			// Return early if the change was triggered by a parent checkbox
			if(source === 'inc-parent') return;
			setAllParentCheckboxes();
		});

		// Set parent checkboxes on init
		setAllParentCheckboxes();

		// Add init class
		$container.addClass('init');
	}

	// Init on DOM ready
	$('.InputfieldNestedCheckboxes').each(function() {
		initInputfieldNestedCheckboxes($(this));
	});

	// Init when inputfield is reloaded
	$(document).on('reloaded', '.InputfieldPage', function() {
		$(this).find('.InputfieldNestedCheckboxes').each(function() {
			initInputfieldNestedCheckboxes($(this));
		});
	});

});
