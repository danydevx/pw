(function($) {
    function initSettingsForm() {
        var nameInput = $('input[name="site_name"]');
        var urlInput = $('input[name="site_url"]');
        if(!nameInput.length || !urlInput.length) return;

        var form = nameInput.closest('form');
        if(!form.length || typeof $.fn.validate !== 'function') return;

        var logoField = $('#wrap_Inputfield_site_logo_upload');
        var previewBox = $('#settings-logo-preview');
        var selectedLogoName = '';

        if(logoField.length) {
            var logoContent = logoField.find('.InputfieldContent').first();
            var spinnerClass = 'SettingsLogoSpinner';
            var spinnerTimer = null;
            var uploadInProgress = false;

            function showSpinner() {
                uploadInProgress = true;
                logoContent.find('.' + spinnerClass).show();
                logoField.addClass('is-uploading');
                if(previewBox.length) previewBox.hide();
                if(spinnerTimer) clearTimeout(spinnerTimer);
                spinnerTimer = setTimeout(function() {
                    hideSpinner();
                }, 15000);
            }

            function hideSpinner() {
                uploadInProgress = false;
                logoContent.find('.' + spinnerClass).hide();
                logoField.removeClass('is-uploading');
                if(previewBox.length) previewBox.show();
                if(spinnerTimer) {
                    clearTimeout(spinnerTimer);
                    spinnerTimer = null;
                }
            }

            if(!logoContent.length) logoContent = logoField;

            if(!logoContent.find('.' + spinnerClass).length) {
                logoContent.append('<div class="' + spinnerClass + '" style="display:none;margin-top:8px;padding:6px 10px;background:#f3f6f8;border:1px solid #d9e1e7;border-radius:4px;color:#2f3b45;font-size:13px;"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Subiendo imagen, espera...</div>');
            }

            $(document).off('change.settingsLogoSpinner').on('change.settingsLogoSpinner', '#wrap_Inputfield_site_logo_upload input[type="file"]', function() {
                var hasFile = !!(this.files && this.files.length);
                if(hasFile) showSpinner();
                else hideSpinner();

                if(!hasFile || !previewBox.length || !this.files || !this.files.length) return;

                var file = this.files[0];
                var safeName = $('<div>').text(file.name).html();
                selectedLogoName = file.name || '';

                if(file.type && file.type.indexOf('image/') === 0 && typeof FileReader !== 'undefined') {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewBox.html('<p><strong>Vista previa local (sin guardar)</strong></p><p>' + safeName + '</p><p><img src="' + e.target.result + '" alt="Vista previa local" style="max-width:220px;height:auto;"></p>');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewBox.html('<p><strong>Archivo seleccionado:</strong> ' + safeName + '</p>');
                }
            });

            form.off('submit.settingsLogoSpinner').on('submit.settingsLogoSpinner', function() {
                if(uploadInProgress) {
                    showSpinner();
                } else {
                    hideSpinner();
                }
            });

            $(document).off('ajaxComplete.settingsLogoSpinner').on('ajaxComplete.settingsLogoSpinner', function(event, xhr, settings) {
                if(!settings || !settings.url) return;
                if(settings.url.indexOf('InputfieldFileAjax') === -1) return;
                hideSpinner();

                if(previewBox.length && selectedLogoName !== '') {
                    var safeName = $('<div>').text(selectedLogoName).html();
                    previewBox.find('.settings-upload-ok').remove();
                    previewBox.prepend('<p class="settings-upload-ok" style="color:#1f7a1f;font-weight:600;">Archivo subido por AJAX. Guarda el formulario para persistir en base de datos.</p>');
                    previewBox.find('p').first().next('p').text(selectedLogoName);
                }
            });

            $(document).off('ajaxStop.settingsLogoSpinner').on('ajaxStop.settingsLogoSpinner', function() {
                hideSpinner();
            });

            logoField.off('DOMNodeInserted.settingsLogoSpinner').on('DOMNodeInserted.settingsLogoSpinner', function(e) {
                var $target = $(e.target);
                if($target.closest('.InputfieldFileList').length) {
                    hideSpinner();
                }
            });
        }

        $.validator.addMethod('absoluteUrl', function(value, element) {
            if(this.optional(element)) return true;
            return /^https?:\/\/[\w.-]+(?:\:[0-9]+)?(?:[\/#?].*)?$/i.test(value);
        }, 'Debe ser una URL absoluta (ej: https://dominio.com).');

        form.validate({
            ignore: [],
            errorElement: 'em',
            errorClass: 'InputfieldError',
            rules: {
                site_name: {
                    required: true
                },
                site_url: {
                    required: true,
                    absoluteUrl: true
                },
                site_description: {
                    required: true
                },
                email: {
                    email: true
                },
                facebook: {
                    absoluteUrl: true
                },
                instagram: {
                    absoluteUrl: true
                },
                x_url: {
                    absoluteUrl: true
                },
                tiktok: {
                    absoluteUrl: true
                }
            },
            messages: {
                site_name: {
                    required: 'El nombre del sitio es requerido.'
                },
                site_url: {
                    required: 'La URL del sitio es requerida.'
                },
                site_description: {
                    required: 'La descripcion es requerida.'
                },
                email: {
                    email: 'Ingresa un email valido.'
                },
                facebook: {
                    absoluteUrl: 'Ingresa una URL valida para Facebook.'
                },
                instagram: {
                    absoluteUrl: 'Ingresa una URL valida para Instagram.'
                },
                x_url: {
                    absoluteUrl: 'Ingresa una URL valida para X.'
                },
                tiktok: {
                    absoluteUrl: 'Ingresa una URL valida para Tiktok.'
                }
            },
            highlight: function(element) {
                $(element).closest('.Inputfield').addClass('InputfieldStateError');
            },
            unhighlight: function(element) {
                $(element).closest('.Inputfield').removeClass('InputfieldStateError');
            },
            errorPlacement: function(error, element) {
                var content = element.closest('.InputfieldContent');
                if(content.length) error.appendTo(content);
                else error.insertAfter(element);
            }
        });
    }

    $(document).ready(initSettingsForm);
    $(document).on('reloaded', initSettingsForm);
})(jQuery);
