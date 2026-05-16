$(document).ready(function() {

	// Copy a string to the clipboard
	function copyToClipboard(string) {
		// HTTPS websites
		if(navigator && navigator.clipboard && navigator.clipboard.writeText) {
			const clipboardItem = new ClipboardItem({
				'text/html': new Blob([string], {type: 'text/html'}),
				'text/plain': new Blob([string], {type: 'text/plain'})
			});
			navigator.clipboard.write([clipboardItem]);
		}
		// Old browsers or non-HTTPS websites
		else {
			const $input = $('<input type="text" value="' + string + '">');
			$('body').append($input);
			$input.select();
			document.execCommand('copy');
			$input.remove();
		}
	}

	// Initialise password generator
	function initPasswordGenerator($wrap) {
		const $input = $wrap.closest('.InputfieldPassword').find('input[type=password]:not(.InputfieldPasswordOld)');
		const $display = $wrap.find('.pg-display');
		const $trigger = $wrap.find('.pg-generate');
		let settings = $wrap.data('pg-settings');
		settings.bind = 'click';
		settings.passwordElement = $input;
		settings.displayElement = $display;
		settings.onPasswordGenerated = function() {
			$input.focus().blur();
		};
		$trigger.pGenerator(settings);
	}

	// Init on DOM ready
	$('.pg-wrap').each(function() {
		initPasswordGenerator($(this));
	});

	// Init on InpufieldPassword reloaded
	$(document).on('reloaded', '.InputfieldPassword', function(event) {
		initPasswordGenerator($(this).find('.pg-wrap'));
	});

	// Copy password to clipboard
	$(document).on('click', '.pg-copy', function() {
		const $preview = $(this).siblings('.pg-display');
		const pw = $preview.text();
		if(pw) {
			copyToClipboard(pw);
			$preview.effect('highlight');
		}
	});

});
