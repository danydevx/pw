$(function() {
    var form = $('#SettingsModule');
    if (!form.length) return;

    function bootValidate(retries) {
        if (typeof $.fn.validate !== 'function') {
            if (retries > 0) {
                setTimeout(function() { bootValidate(retries - 1); }, 150);
            }
            return;
        }

        form.attr('novalidate', 'novalidate');

        form.validate({
        ignore: ':hidden:not(.InputfieldStateRequired :input)',
        errorClass: 'ui-state-error',
        validClass: 'ui-state-valid',
        errorElement: 'small',
        errorPlacement: function(error, element) {
            var inputfield = element.closest('.Inputfield');
            if (inputfield.length) {
                inputfield.find('.InputfieldContent').first().append(error);
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            $(element).addClass('ui-state-error');
        },
        unhighlight: function(element) {
            $(element).removeClass('ui-state-error');
        }
    });

        function addRuleByWrapper(selector, rules, messages) {
            form.find(selector + ' :input[name]').filter(function() {
                return !$(this).is(':disabled') && $(this).attr('type') !== 'hidden';
            }).each(function() {
                var ruleConfig = $.extend({}, rules);
                if (messages) ruleConfig.messages = messages;
                $(this).rules('add', ruleConfig);
            });
        }

        addRuleByWrapper('.InputfieldText.InputfieldStateRequired', { required: true });
        addRuleByWrapper('.InputfieldEmail.InputfieldStateRequired', { required: true, email: true }, {
            email: 'Please enter a valid email address.'
        });

        if (!$.validator.methods.pgsFilesizeKb) {
            $.validator.addMethod('pgsFilesizeKb', function(value, element, maxKb) {
                if (!element.files || !element.files.length) return true;
                return element.files[0].size <= (parseInt(maxKb, 10) * 1024);
            }, 'File is too large.');
        }

        if (!$.validator.methods.pgsImageMaxWidth) {
            $.validator.addMethod('pgsImageMaxWidth', function(value, element, maxWidth) {
                if (!element.files || !element.files.length) return true;
                var done = this.async();
                var img = new Image();
                img.onload = function() { done(img.width <= parseInt(maxWidth, 10)); };
                img.onerror = function() { done(false); };
                img.src = URL.createObjectURL(element.files[0]);
            }, 'Image width exceeds limit.');
        }

        if (!$.validator.methods.pgsCheckboxesMax) {
            $.validator.addMethod('pgsCheckboxesMax', function(value, element, maxAllowed) {
                var name = $(element).attr('name');
                if (!name) return true;
                var checked = form.find(':input[name="' + name + '"]:checked').length;
                return checked <= parseInt(maxAllowed, 10);
            }, 'Too many options selected.');
        }
        addRuleByWrapper('.InputfieldInteger.InputfieldStateRequired', { required: true, digits: true }, {
            digits: 'Please enter a valid integer.'
        });
        addRuleByWrapper('.InputfieldFloat.InputfieldStateRequired', { required: true, number: true }, {
            number: 'Please enter a valid number.'
        });
        addRuleByWrapper('.InputfieldSelect.InputfieldStateRequired', { required: true });

        form.find('.InputfieldRadios.InputfieldStateRequired, .InputfieldCheckboxes.InputfieldStateRequired').each(function() {
            var options = $(this).find(':input[name]').filter(function() {
                return !$(this).is(':disabled');
            });
            if (!options.length) return;
            options.each(function() {
                $(this).rules('add', { required: true });
            });
        });

        form.find('.InputfieldCheckbox.InputfieldStateRequired :input[type="checkbox"][name]').filter(function() {
            return !$(this).is(':disabled');
        }).each(function() {
            $(this).rules('add', { required: true });
        });

        form.find('.InputfieldCheckboxes[data-pgs-checkboxes-max]').each(function() {
            var wrapper = $(this);
            var maxAllowed = parseInt(wrapper.attr('data-pgs-checkboxes-max'), 10);
            if (!maxAllowed || maxAllowed < 1) return;
            var options = wrapper.find(':input[type="checkbox"][name]').filter(function() {
                return !$(this).is(':disabled');
            });
            if (!options.length) return;
            options.each(function() {
                $(this).rules('add', {
                    pgsCheckboxesMax: maxAllowed,
                    messages: {
                        pgsCheckboxesMax: 'Maximum ' + maxAllowed + ' options allowed.'
                    }
                });
            });
        });

        form.find('.InputfieldURL :input[name]').filter(function() {
            return !$(this).is(':disabled') && $(this).attr('type') !== 'hidden';
        }).each(function() {
            $(this).rules('add', {
                url: true,
                messages: {
                    url: 'Please enter a valid URL (example: https://domain.com).'
                }
            });
        });

        form.find('.InputfieldFile :input[type="file"][name]').filter(function() {
            return !$(this).is(':disabled');
        }).each(function() {
            var fileInput = $(this);
            var maxKb = fileInput.attr('data-pgs-image-max-size') || fileInput.attr('data-pgs-file-max-size');
            var maxWidth = fileInput.attr('data-pgs-image-max-width');
            var ext = fileInput.attr('data-pgs-image-extensions') || fileInput.attr('data-pgs-file-extensions');
            var rules = {};
            var messages = {};

            if (ext) {
                rules.extension = ext.replace(/,/g, '|');
                messages.extension = 'Allowed formats: ' + ext;
            }
            if (maxKb) {
                rules.pgsFilesizeKb = maxKb;
                messages.pgsFilesizeKb = 'Max file size: ' + maxKb + ' KB.';
            }
            if (maxWidth) {
                rules.pgsImageMaxWidth = maxWidth;
                messages.pgsImageMaxWidth = 'Max image width: ' + maxWidth + ' px.';
            }

            fileInput.rules('add', $.extend(rules, { messages: messages }));
        });

        form.data('pgs-validate-ready', 1);
    }

    if (!form.data('pgs-validate-ready')) {
        bootValidate(20);
    }
});
