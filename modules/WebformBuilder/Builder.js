$(window).on('load', function() {
    $('#ModuleEditForm').after(
        '<div id="pgsModal" uk-modal>' +
            '<div class="uk-modal-dialog uk-modal-body uk-width-1-2@m">' +
                '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                '<h3 class="uk-modal-title">Edit setting</h3>' +
                '<form id="modalForm" class="uk-form-stacked">' +
                    '<div id="pgsModePill" class="pgs-mode-pill">New field</div>' +
                    '<div id="pgsDirtyPill" class="pgs-dirty-pill" hidden>Unsaved changes</div>' +
                    '<p id="tips" class="uk-text-danger"></p>' +
                    '<div class="uk-grid-small uk-child-width-1-2@m" uk-grid>' +
                        '<div><label class="uk-form-label" for="api">API variable</label><div class="uk-form-controls"><input type="text" name="api" id="api" value="" required class="uk-input"><small class="pgs-help" id="api-help">Use lowercase letters, numbers, _ or -.</small></div></div>' +
                        '<div><label class="uk-form-label" for="label">Label</label><div class="uk-form-controls"><input type="text" name="label" id="label" value="" required class="uk-input"></div></div>' +
                        '<div><label class="uk-form-label" for="type">Type</label><div class="uk-form-controls"><select name="type" id="type" class="uk-select">' +
                            '<option>Text</option><option>Textarea</option><option>Select</option><option>Radios</option><option>Checkbox</option><option>Checkboxes</option><option>Integer</option><option>Float</option>' +
                            '<option>URL</option><option>Email</option><option>Date</option><option>Image</option><option>File</option><option>Fieldset</option><option>Markup</option><option>PageListSelect</option>' +
                        '</select></div></div>' +
                        '<div><label class="uk-form-label" for="width">Width in %</label><div class="uk-form-controls"><div class="uk-flex uk-flex-middle uk-grid-small" uk-grid><div class="uk-width-expand"><input type="range" max="100" min="10" step="1" name="width" id="width" value="100" class="uk-range"></div><div class="uk-width-auto"><input type="number" id="width_display" value="100" min="10" max="100" step="1" class="uk-input" style="width:80px;" readonly></div></div><small class="pgs-help">Range: 10 to 100</small></div></div>' +
                        '<div class="uk-width-1-1"><label class="uk-form-label" for="description">Description</label><div class="uk-form-controls"><input type="text" name="description" id="description" value="" class="uk-input"></div></div>' +
                        '<div class="uk-width-1-1"><label class="uk-form-label" for="placeholder">Placeholder</label><div class="uk-form-controls"><input type="text" name="placeholder" id="placeholder" value="" class="uk-input"></div></div>' +
                        '<div><label class="uk-form-label" for="collapsed">Collapsed</label><div class="uk-form-controls"><select name="collapsed" id="collapsed" class="uk-select">' +
                            '<option>Default</option><option>collapsedNever</option><option>collapsedBlank</option><option>collapsedYes</option>' +
                        '</select></div></div>' +
                        '<div><label class="uk-form-label" for="required">Required</label><div class="uk-form-controls"><select name="required" id="required" class="uk-select"><option value="0">False</option><option value="1">True</option></select></div></div>' +
                        '<div class="pgs-image-only"><label class="uk-form-label" for="image_max_size">Image max size (KB)</label><div class="uk-form-controls"><input type="number" min="1" name="image_max_size" id="image_max_size" value="1024" class="uk-input"></div></div>' +
                        '<div class="pgs-image-only"><label class="uk-form-label" for="image_extensions">Allowed formats</label><div class="uk-form-controls"><input type="text" name="image_extensions" id="image_extensions" value="jpg,jpeg,png,webp,gif,svg" class="uk-input"><small class="pgs-help">Comma separated: jpg,jpeg,png...</small></div></div>' +
                        '<div class="pgs-image-only"><label class="uk-form-label" for="image_max_width">Image max width (px)</label><div class="uk-form-controls"><input type="number" min="1" name="image_max_width" id="image_max_width" value="1920" class="uk-input"></div></div>' +
                        '<div class="pgs-file-only"><label class="uk-form-label" for="file_max_size">File max size (KB)</label><div class="uk-form-controls"><input type="number" min="1" name="file_max_size" id="file_max_size" value="2048" class="uk-input"></div></div>' +
                        '<div class="pgs-file-only"><label class="uk-form-label" for="file_extensions">Allowed file formats</label><div class="uk-form-controls"><input type="text" name="file_extensions" id="file_extensions" value="pdf,doc,docx,xls,xlsx,ppt,pptx,txt" class="uk-input"></div></div>' +
                        '<div class="pgs-page-only"><label class="uk-form-label" for="page_parent_mode">Parent source</label><div class="uk-form-controls"><select name="page_parent_mode" id="page_parent_mode" class="uk-select"><option value="id">Parent ID</option><option value="selector">Selector query</option></select></div></div>' +
                        '<div class="pgs-page-only pgs-page-mode-id"><label class="uk-form-label" for="page_parent_id">Parent page ID</label><div class="uk-form-controls"><input type="number" min="1" name="page_parent_id" id="page_parent_id" value="1" class="uk-input"><small class="pgs-help">Only children of this parent will be selectable.</small></div></div>' +
                        '<div class="pgs-page-only pgs-page-mode-selector"><label class="uk-form-label" for="page_parent_selector">Parent selector</label><div class="uk-form-controls"><input type="text" name="page_parent_selector" id="page_parent_selector" value="parent=1" class="uk-input"><small class="pgs-help">Example: parent=2344 or template=basic-page</small></div></div>' +
                        '<div class="pgs-checkboxes-only"><label class="uk-form-label" for="checkboxes_max_selected">Max selections (Checkboxes)</label><div class="uk-form-controls"><input type="number" min="0" max="100" name="checkboxes_max_selected" id="checkboxes_max_selected" value="0" class="uk-input"><small class="pgs-help">0 = no limit, max 100.</small></div></div>' +
                        '<div class="pgs-date-only"><label class="uk-form-label" for="date_min">Min date</label><div class="uk-form-controls"><input type="date" name="date_min" id="date_min" value="" class="uk-input"></div></div>' +
                        '<div class="pgs-date-only"><label class="uk-form-label" for="date_max">Max date</label><div class="uk-form-controls"><input type="date" name="date_max" id="date_max" value="" class="uk-input"></div></div>' +
                        '<div class="uk-width-1-1"><div id="options-hint" class="uk-alert-primary" uk-alert hidden>Options are managed after save from the table action "Options" for Select, Radios and Checkboxes fields.</div></div>' +
                    '</div>' +
                    '<div class="uk-modal-footer uk-padding-remove-horizontal uk-padding-small uk-padding-remove-bottom">' +
                        '<button type="button" class="uk-button uk-button-default uk-modal-close">Cancel</button>' +
                        '<button type="submit" id="pgsSaveField" class="uk-button uk-button-primary">Save</button>' +
                    '</div>' +
                '</form>' +
            '</div>' +
        '</div>' +
        '<div id="pgsOptionsModal" uk-modal>' +
            '<div class="uk-modal-dialog uk-modal-body uk-width-2-3@m">' +
                '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                '<h3 class="uk-modal-title">Manage options</h3>' +
                '<p class="uk-text-meta">Define option key and label. You can choose a default value only when field is required.</p>' +
                '<table class="uk-table uk-table-divider uk-table-small" id="pgsOptionsTable"><thead><tr><th>Key</th><th>Label</th><th>Default</th><th></th></tr></thead><tbody></tbody></table>' +
                '<button type="button" id="pgsAddOption" class="uk-button uk-button-default uk-button-small">Add option</button>' +
                '<div class="uk-modal-footer uk-padding-remove-horizontal uk-padding-small uk-padding-remove-bottom">' +
                    '<button type="button" class="uk-button uk-button-default uk-modal-close">Cancel</button>' +
                    '<button type="button" id="pgsSaveOptions" class="uk-button uk-button-primary">Save options</button>' +
                '</div>' +
            '</div>' +
        '</div>'
    );

    var modal = UIkit.modal('#pgsModal');
    var optionsModal = UIkit.modal('#pgsOptionsModal');
    var form = $('#modalForm');
    var moduleData = $.parseJSON($('#settings').val() || '{}');
    var currentIndex = null;
    var optionsIndex = null;
    var apiTouched = false;
    var isDirty = false;

    function notify(message, status) {
        if (typeof UIkit !== 'undefined' && UIkit.notification) {
            UIkit.notification({ message: message, status: status || 'primary', pos: 'top-right', timeout: 1800 });
        }
    }

    function setDirty(state) {
        isDirty = !!state;
        $('#pgsDirtyPill').attr('hidden', !isDirty);
    }

    function supportsOptions(type) {
        return type === 'Select' || type === 'Radios' || type === 'Checkboxes';
    }

    function isFieldsetType(type) {
        return type === 'Fieldset' || type === 'FieldsetClose';
    }

    function isImageType(type) {
        return type === 'Image';
    }

    function isFileType(type) {
        return type === 'File';
    }

    function isPageListSelectType(type) {
        return type === 'PageListSelect';
    }

    function isCheckboxesType(type) {
        return type === 'Checkboxes';
    }

    function isDateType(type) {
        return type === 'Date';
    }

    function syncPageModeUI() {
        var mode = $('#page_parent_mode').val() || 'id';
        $('.pgs-page-mode-id').toggle(mode === 'id');
        $('.pgs-page-mode-selector').toggle(mode === 'selector');
    }

    function isRequiredSetting(setting) {
        return !!(setting && (setting.required === '1' || setting.required === 1 || setting.required === true));
    }

    function sanitizeKey(value) {
        return (value || '').toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/^_+|_+$/g, '');
    }

    function encodeOptionLabel(value) {
        return encodeURIComponent(String(value || ''));
    }

    function decodeOptionLabel(value) {
        try {
            return decodeURIComponent(String(value || ''));
        } catch (e) {
            return String(value || '');
        }
    }

    function clearErrors() {
        form.find('.uk-form-danger').removeClass('uk-form-danger');
        $('#tips').text('');
    }

    function setError(field, message) {
        field.addClass('uk-form-danger');
        $('#tips').text(message);
    }

    function checkLength(field, label) {
        if (field.val().length < 1) {
            setError(field, label + ' can not be empty.');
            return false;
        }
        return true;
    }

    function checkRegexp(field, regexp, message) {
        if (!regexp.test(field.val())) {
            setError(field, message);
            return false;
        }
        return true;
    }

    function validateApiLive() {
        var apiField = $('#api');
        var apiValue = $.trim(apiField.val()).toLowerCase();
        apiField.val(apiValue);

        apiField.removeClass('uk-form-danger');
        $('#tips').text('');

        if (!apiValue) return true;

        if (!/^[a-z][a-z0-9_-]*$/.test(apiValue)) {
            setError(apiField, 'API variable must start with a lowercase letter and contain only lowercase letters, numbers, underscore (_) or hyphen (-).');
            return false;
        }

        if (!isApiUnique(apiValue, currentIndex)) {
            setError(apiField, 'API variable must be unique. This value is already in use.');
            return false;
        }

        return true;
    }

    function slugifyApi(value) {
        return (value || '').toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_-]/g, '').replace(/^[^a-z]+/, '').replace(/_+/g, '_');
    }

    function validateWidthLive() {
        var widthField = $('#width');
        var widthValue = parseInt(widthField.val(), 10);
        $('#width_display').val(isNaN(widthValue) ? '' : widthValue);
        widthField.removeClass('uk-form-danger');
        if (isNaN(widthValue) || widthValue < 10 || widthValue > 100) {
            setError(widthField, 'Width must be between 10 and 100.');
            return false;
        }
        return true;
    }

    function syncTypeUI(type) {
        var isFieldset = isFieldsetType(type);
        var isImage = isImageType(type);
        var isFile = isFileType(type);
        var isPageListSelect = isPageListSelectType(type);
        var isCheckboxes = isCheckboxesType(type);
        var isDate = isDateType(type);
        var placeholderWrap = $('#placeholder').closest('div.uk-width-1-1');
        var requiredWrap = $('#required').closest('div');
        placeholderWrap.toggle(!isFieldset);
        requiredWrap.toggle(!isFieldset);
        $('.pgs-image-only').toggle(isImage);
        $('.pgs-file-only').toggle(isFile);
        $('.pgs-page-only').toggle(isPageListSelect);
        $('.pgs-checkboxes-only').toggle(isCheckboxes);
        $('.pgs-date-only').toggle(isDate);
        if (isPageListSelect) syncPageModeUI();

        if (type === 'Fieldset') {
            $('#options-hint').attr('hidden', false).text('When saved, a paired FieldsetClose will be created automatically using API suffix _close.');
        } else {
            $('#options-hint').attr('hidden', !supportsOptions(type));
            if (supportsOptions(type)) {
                $('#options-hint').text('Options are managed after save from the table action "Options" for Select, Radios and Checkboxes fields.');
            }
        }
    }

    function findFieldsetCloseIndex(openApi, oldApi) {
        var found = null;
        var expectedApi = (openApi || '') + '_close';
        var expectedOldApi = (oldApi || '') + '_close';
        $.each(moduleData, function(index, setting) {
            if (!setting) return;
            if (setting.type !== 'FieldsetClose') return;
            if (setting._fieldsetCloseFor === openApi || setting._fieldsetCloseFor === oldApi || setting.api === expectedApi || setting.api === expectedOldApi) {
                found = index;
                return false;
            }
        });
        return found;
    }

    function ensureFieldsetClose(openIndex, openField, previousApi) {
        var closeIndex = findFieldsetCloseIndex(openField.api, previousApi);
        var closeApi = openField.api + '_close';
        var closeLabel = (openField.label || openField.api) + ' close';

        if (closeIndex === null) {
            closeIndex = Object.keys(moduleData).length;
        }

        var previous = moduleData[closeIndex] || {};
        moduleData[closeIndex] = {
            api: closeApi,
            label: closeLabel,
            type: 'FieldsetClose',
            width: openField.width || '100',
            description: '',
            collapsed: 'Default',
            required: '0',
            placeholder: '',
            select: '',
            value: '',
            _deleted: previous._deleted ? 1 : 0,
            _fieldsetCloseFor: openField.api
        };

    }

    function removeFieldsetClose(openApi, oldApi) {
        var closeIndex = findFieldsetCloseIndex(openApi, oldApi);
        if (closeIndex !== null) {
            delete moduleData[closeIndex];
            normalizeOrder();
        }
    }

    function updateSaveState() {
        var okApi = validateApiLive();
        var okLabel = validateLabelLive();
        var okWidth = validateWidthLive();

        if (isPageListSelectType($('#type').val()) && $('#page_parent_mode').val() === 'selector') {
            var selectorVal = $.trim($('#page_parent_selector').val());
            if (!selectorVal) {
                setError($('#page_parent_selector'), 'Selector query can not be empty.');
                $('#pgsSaveField').prop('disabled', true);
                return;
            }
        }

        $('#pgsSaveField').prop('disabled', !(okApi && okLabel && okWidth));
        if (okApi && okLabel && okWidth) $('#tips').text('');
    }

    function validateLabelLive() {
        var labelField = $('#label');
        var labelValue = labelField.val();

        labelField.removeClass('uk-form-danger');
        if (!$.trim(labelValue)) {
            setError(labelField, 'Label can not be empty.');
            return false;
        }

        return true;
    }

    function isApiUnique(apiValue, currentKey) {
        var unique = true;
        $.each(moduleData, function(index, setting) {
            if (!setting || setting._deleted) return;
            if (String(index) === String(currentKey)) return;
            if ((setting.api || '') === apiValue) {
                unique = false;
                return false;
            }
        });
        return unique;
    }

    function buildLabel(setting) {
        if (setting.type === 'Fieldset' || setting.type === 'FieldsetClose') return '-- ' + setting.label + ' -- ' + setting.width + '%';
        return setting.label;
    }

    function renderTable() {
        var tbody = $('#pgsSettingsTable tbody');
        tbody.empty();
        $.each(moduleData, function(index, setting) {
            if (!setting) return;
            var isDeleted = setting._deleted === 1 || setting._deleted === '1' || setting._deleted === true;
            var isAutoClose = setting.type === 'FieldsetClose';
            if (isAutoClose && isDeleted) return;
            var actions = '';
            if (isAutoClose) {
                actions = '';
            } else {
                if (supportsOptions(setting.type) && setting.value) {
                    actions += '<span class="uk-label pgs-default-chip">Default: ' + setting.value + '</span> ';
                }
                actions += '<a class="edit uk-button uk-button-default uk-button-small" href="#"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                if (supportsOptions(setting.type)) actions += ' <a class="options uk-button uk-button-secondary uk-button-small" href="#">Options</a>';
                if (isDeleted) {
                    actions += ' <a class="toggle-delete uk-button uk-button-primary uk-button-small" href="#"><i class="fa fa-undo" aria-hidden="true"></i> Restore</a>';
                } else {
                    actions += ' <a class="toggle-delete uk-button uk-button-danger uk-button-small" href="#"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>';
                }
            }

            var row = $('<tr></tr>').attr('data-index', index).toggleClass('pgs-row-deleted', isDeleted);
            row.append('<td class="pgs-drag"><i class="fa fa-bars" aria-hidden="true"></i></td>');
            row.append($('<td></td>').text(buildLabel(setting)));
            row.append($('<td></td>').text(setting.api || ''));
            row.append($('<td></td>').text(setting.type || ''));
            row.append($('<td></td>').text((setting.width || '100') + '%'));
            row.append($('<td></td>').text((setting.required === 1 || setting.required === '1') ? 'True' : 'False'));
            row.append('<td class="pgs-actions">' + actions + '</td>');
            tbody.append(row);
        });
    }

    function resetForm() {
        clearErrors();
        apiTouched = false;
        $('#pgsModePill').text('New field');
        form.find('#api').val('field');
        form.find('#label').val('New field');
        form.find('#type').val('Text');
        form.find('#width').val('100');
        form.find('#width_display').val('100');
        form.find('#description').val('');
        form.find('#collapsed').val('Default');
        form.find('#required').val('0');
        form.find('#placeholder').val('');
        form.find('#image_max_size').val('1024');
        form.find('#image_extensions').val('jpg,jpeg,png,webp,gif,svg');
        form.find('#image_max_width').val('1920');
        form.find('#file_max_size').val('2048');
        form.find('#file_extensions').val('pdf,doc,docx,xls,xlsx,ppt,pptx,txt');
        form.find('#page_parent_id').val('1');
        form.find('#page_parent_mode').val('id');
        form.find('#page_parent_selector').val('parent=1');
        form.find('#checkboxes_max_selected').val('0');
        form.find('#date_min').val('');
        form.find('#date_max').val('');
        syncTypeUI('Text');
        updateSaveState();
        setDirty(false);
    }

    function fillForm(index) {
        var setting = moduleData[index] || {};
        apiTouched = true;
        $('#pgsModePill').text('Edit field');
        form.find('#api').val(setting.api || '');
        form.find('#label').val(setting.label || '');
        form.find('#type').val(setting.type || 'Text');
        form.find('#width').val(setting.width || '100');
        form.find('#width_display').val(setting.width || '100');
        form.find('#description').val(setting.description || '');
        form.find('#collapsed').val(setting.collapsed || 'Default');
        form.find('#required').val((setting.required === 1 || setting.required === '1') ? '1' : '0');
        form.find('#placeholder').val(setting.placeholder || '');
        form.find('#image_max_size').val(setting.image_max_size || '1024');
        form.find('#image_extensions').val(setting.image_extensions || 'jpg,jpeg,png,webp,gif,svg');
        form.find('#image_max_width').val(setting.image_max_width || '1920');
        form.find('#file_max_size').val(setting.file_max_size || '2048');
        form.find('#file_extensions').val(setting.file_extensions || 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt');
        form.find('#page_parent_id').val(setting.page_parent_id || '1');
        form.find('#page_parent_mode').val(setting.page_parent_mode || 'id');
        form.find('#page_parent_selector').val(setting.page_parent_selector || 'parent=1');
        form.find('#checkboxes_max_selected').val(setting.checkboxes_max_selected || '0');
        form.find('#date_min').val(setting.date_min || '');
        form.find('#date_max').val(setting.date_max || '');
        syncTypeUI(form.find('#type').val());
        updateSaveState();
    }

    function saveField() {
        clearErrors();
        var valid = true;
        var apiField = $('#api');
        var apiValue = $.trim(apiField.val()).toLowerCase();
        apiField.val(apiValue);
        $('#width').val($.trim($('#width').val()));

        valid = valid && checkLength($('#api'), 'API variable');
        valid = valid && checkLength($('#label'), 'Label');
        valid = valid && validateWidthLive();
        valid = valid && checkRegexp($('#api'), /^[a-z][a-z0-9_-]*$/, 'API variable must start with a lowercase letter and contain only lowercase letters, numbers, underscore (_) or hyphen (-).');
        if (valid && !isApiUnique(apiValue, currentIndex)) {
            setError(apiField, 'API variable must be unique. This value is already in use.');
            valid = false;
        }
        if (!valid) return false;

        var maxChecks = parseInt(form.find('#checkboxes_max_selected').val(), 10);
        if (isNaN(maxChecks) || maxChecks < 0) maxChecks = 0;
        if (maxChecks > 100) maxChecks = 100;
        form.find('#checkboxes_max_selected').val(String(maxChecks));

        var prev = moduleData[currentIndex] || {};
        var selectedType = form.find('#type').val();
        var selectedPlaceholder = form.find('#placeholder').val();
        var selectedRequired = form.find('#required').val();

        if (isFieldsetType(selectedType)) {
            selectedPlaceholder = '';
            selectedRequired = '0';
        }

        moduleData[currentIndex] = {
            api: form.find('#api').val(),
            label: form.find('#label').val(),
            type: selectedType,
            width: form.find('#width').val(),
            description: form.find('#description').val(),
            collapsed: form.find('#collapsed').val(),
            required: selectedRequired,
            placeholder: selectedPlaceholder,
            image_max_size: form.find('#image_max_size').val(),
            image_extensions: form.find('#image_extensions').val(),
            image_max_width: form.find('#image_max_width').val(),
            file_max_size: form.find('#file_max_size').val(),
            file_extensions: form.find('#file_extensions').val(),
            page_parent_id: form.find('#page_parent_id').val(),
            page_parent_mode: form.find('#page_parent_mode').val(),
            page_parent_selector: form.find('#page_parent_selector').val(),
            checkboxes_max_selected: form.find('#checkboxes_max_selected').val(),
            date_min: form.find('#date_min').val(),
            date_max: form.find('#date_max').val(),
            select: prev.select || '',
            value: prev.value || '',
            _deleted: prev._deleted ? 1 : 0
        };

        if (!supportsOptions(moduleData[currentIndex].type)) moduleData[currentIndex].select = '';
        if (moduleData[currentIndex].required !== '1' && moduleData[currentIndex].required !== 1) {
            moduleData[currentIndex].value = '';
        }

        if (moduleData[currentIndex].type === 'Fieldset') {
            ensureFieldsetClose(currentIndex, moduleData[currentIndex], prev.api || moduleData[currentIndex].api);
        } else if (prev.type === 'Fieldset') {
            removeFieldsetClose(moduleData[currentIndex].api, prev.api);
        }

        renderTable();
        modal.hide();
        setDirty(true);
        notify('Field saved', 'success');
        return true;
    }

    function normalizeOrder() {
        var ordered = {};
        $('#pgsSettingsTable tbody tr').each(function(index) {
            var oldIndex = $(this).attr('data-index');
            if (moduleData[oldIndex]) ordered[index] = moduleData[oldIndex];
        });
        moduleData = ordered;
        renderTable();
        setDirty(true);
    }

    function parseOptions(selectRaw) {
        var out = [];
        var tokens = (selectRaw || '').split(',');
        $.each(tokens, function(_, t) {
            var token = $.trim(t);
            if (!token) return;
            if (token.indexOf(':') > -1) {
                var parts = token.split(':');
                var key = sanitizeKey(parts.shift());
                var label = $.trim(parts.join(':'));
                if (!key) return;
                out.push({ key: key, label: decodeOptionLabel(label || key) });
            } else {
                var k = sanitizeKey(token);
                if (!k) return;
                out.push({ key: k, label: decodeOptionLabel(token) });
            }
        });
        return out;
    }

    function renderOptionsRows(options, selectedDefault, allowDefault) {
        var tbody = $('#pgsOptionsTable tbody');
        tbody.empty();
        $.each(options, function(_, opt) {
            var tr = $('<tr></tr>');
            tr.append('<td><input type="text" class="uk-input opt-key" value="' + (opt.key || '') + '"></td>');
            tr.append('<td><input type="text" class="uk-input opt-label" value="' + (opt.label || '') + '"></td>');
            var checked = (allowDefault && selectedDefault && selectedDefault === opt.key) ? ' checked' : '';
            var disabled = allowDefault ? '' : ' disabled';
            tr.append('<td class="pgs-opt-default"><input type="radio" name="opt-default" class="opt-default" value="' + (opt.key || '') + '"' + checked + disabled + '></td>');
            tr.append('<td><button type="button" class="uk-button uk-button-danger uk-button-small remove-opt">Remove</button></td>');
            tbody.append(tr);
        });
        if (!tbody.children().length) $('#pgsAddOption').trigger('click');
    }

    function validateOptionsRows() {
        var keys = {};
        var hasErrors = false;
        $('#pgsOptionsTable .opt-key, #pgsOptionsTable .opt-label').removeClass('uk-form-danger');
        $('#pgsOptionsTable tbody tr').each(function() {
            var keyField = $(this).find('.opt-key');
            var labelField = $(this).find('.opt-label');
            var key = sanitizeKey(keyField.val());
            var label = $.trim(labelField.val());

            keyField.val(key);

            if (!key) {
                keyField.addClass('uk-form-danger');
                hasErrors = true;
                return;
            }
            if (!label) {
                labelField.addClass('uk-form-danger');
                hasErrors = true;
                return;
            }
            if (keys[key]) {
                keyField.addClass('uk-form-danger');
                hasErrors = true;
                return;
            }
            keys[key] = true;
        });

        return !hasErrors;
    }

    $('#type').on('change', function() {
        syncTypeUI($(this).val());
        updateSaveState();
    });

    $('#page_parent_mode').on('change', function() {
        syncPageModeUI();
        updateSaveState();
    });

    $(document).on('input change', '#modalForm input, #modalForm select', function() {
        setDirty(true);
    });

    $(document).on('input blur', '#api', function() {
        apiTouched = true;
        validateApiLive();
        updateSaveState();
    });

    $(document).on('input blur', '#label', function() {
        if (!apiTouched) {
            $('#api').val(slugifyApi($(this).val()));
        }
        validateLabelLive();
        validateApiLive();
        updateSaveState();
    });

    $(document).on('input change', '#width', function() {
        var width = parseInt($(this).val(), 10);
        if (!isNaN(width)) {
            if (width < 10) width = 10;
            if (width > 100) width = 100;
            $(this).val(width);
            $('#width_display').val(width);
        }
        validateWidthLive();
        updateSaveState();
    });

    $('#pgsSettingsTable tbody').sortable({
        handle: '.pgs-drag',
        axis: 'y',
        update: function() { normalizeOrder(); }
    });

    $(document).on('click', '#pgsSettingsTable a.edit', function(event) {
        event.preventDefault();
        currentIndex = $(this).closest('tr').attr('data-index');
        if (moduleData[currentIndex] && moduleData[currentIndex].type === 'FieldsetClose') return;
        fillForm(currentIndex);
        clearErrors();
        modal.show();
    });

    $(document).on('click', '#pgsSettingsTable a.options', function(event) {
        event.preventDefault();
        optionsIndex = $(this).closest('tr').attr('data-index');
        if (moduleData[optionsIndex] && moduleData[optionsIndex]._deleted) return;
        var allowDefault = isRequiredSetting(moduleData[optionsIndex]);
        renderOptionsRows(parseOptions(moduleData[optionsIndex].select || ''), moduleData[optionsIndex].value || '', allowDefault);
        optionsModal.show();
    });

    $(document).on('input blur', '#pgsOptionsTable .opt-key', function() {
        var key = sanitizeKey($(this).val());
        $(this).val(key);
        $(this).closest('tr').find('.opt-default').val(key);
    });

    $(document).on('click', '#pgsOptionsTable .pgs-opt-default', function() {
        var radio = $(this).find('.opt-default');
        if (!radio.is(':disabled')) {
            radio.prop('checked', true).trigger('change');
        }
    });

    $(document).on('click', '.remove-opt', function() {
        $(this).closest('tr').remove();
    });

    $('#pgsAddOption').on('click', function() {
        var allowDefault = optionsIndex !== null && isRequiredSetting(moduleData[optionsIndex]);
        var disabled = allowDefault ? '' : ' disabled';
        $('#pgsOptionsTable tbody').append('<tr><td><input type="text" class="uk-input opt-key"></td><td><input type="text" class="uk-input opt-label"></td><td class="pgs-opt-default"><input type="radio" name="opt-default" class="opt-default"' + disabled + '></td><td><button type="button" class="uk-button uk-button-danger uk-button-small remove-opt">Remove</button></td></tr>');
    });

    $('#pgsSaveOptions').on('click', function() {
        if (optionsIndex === null) return;
        if (!validateOptionsRows()) return;
        var pairs = [];
        $('#pgsOptionsTable tbody tr').each(function() {
            var key = sanitizeKey($(this).find('.opt-key').val());
            var label = $.trim($(this).find('.opt-label').val());
            if (!key || !label) return;
            pairs.push(key + ':' + encodeOptionLabel(label));
            $(this).find('.opt-default').val(key);
        });
        moduleData[optionsIndex].select = pairs.join(',');

        var allowDefault = isRequiredSetting(moduleData[optionsIndex]);
        if (allowDefault) {
            moduleData[optionsIndex].value = $('#pgsOptionsTable .opt-default:checked').val() || '';
        } else {
            moduleData[optionsIndex].value = '';
        }

        optionsModal.hide();
        setDirty(true);
        notify('Options saved', 'success');
    });

    $(document).on('click', '#pgsSettingsTable a.toggle-delete', function(event) {
        event.preventDefault();
        var index = $(this).closest('tr').attr('data-index');
        if (!moduleData[index]) return;
        moduleData[index]._deleted = moduleData[index]._deleted ? 0 : 1;

        if (moduleData[index].type === 'Fieldset') {
            var closeIndex = findFieldsetCloseIndex(moduleData[index].api, moduleData[index].api);
            if (closeIndex !== null && moduleData[closeIndex]) {
                moduleData[closeIndex]._deleted = moduleData[index]._deleted ? 1 : 0;
            }
        }

        renderTable();
        setDirty(true);
    });

    form.on('submit', function(event) {
        event.preventDefault();
        saveField();
    });

    $('#addNew').on('click', function(event) {
        event.preventDefault();
        currentIndex = Object.keys(moduleData).length;
        moduleData[currentIndex] = { api: '', label: '', type: 'Text', width: '100', description: '', collapsed: 'Default', required: '0', placeholder: '', image_max_size: '1024', image_extensions: 'jpg,jpeg,png,webp,gif,svg', image_max_width: '1920', file_max_size: '2048', file_extensions: 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt', page_parent_id: '1', page_parent_mode: 'id', page_parent_selector: 'parent=1', checkboxes_max_selected: '0', date_min: '', date_max: '', select: '', value: '', _deleted: 0 };
        resetForm();
        modal.show();
    });

    $('#ModuleEditForm').on('submit', function() {
        var persisted = {};
        var idx = 0;
        $.each(moduleData, function(_, setting) {
            if (!setting || setting._deleted) return;
            var clone = $.extend({}, setting);
            delete clone._deleted;
            persisted[idx] = clone;
            idx++;
        });
        $('#settings').val(JSON.stringify(persisted));
        setDirty(false);
    });

    renderTable();
    resetForm();
});
