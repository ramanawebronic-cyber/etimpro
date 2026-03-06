(function ($) {
    'use strict';

    var assetsUrl = window.etimBulkAssetsUrl || '';

    window.ETIM_BULK = {
        data: [],
        currentClass: null,
        currentGroupCode: null,
        currentGroup: null,
        allFeatures: [],
        assignedFeatures: [],

        init: function () {
            var self = this;
            this.cacheElements();
            this.initSelect2();
            this.bindEvents();
            this.loadGroups();
            this.initSortable();
        },

        cacheElements: function () {
            this.$container = $('#etim-bulk-container');
            this.$groupSelect = $('#etim-bulk-group-select');
            this.$classSelect = $('#etim-bulk-class-select');
            this.$classRow = $('#etim-bulk-class-row');
            this.$featuresSection = $('#etim-bulk-features-section');
            this.$featuresGrid = $('#etim-bulk-features-grid');
            this.$loading = $('#etim-bulk-loading');
            this.$saveBtn = $('#etim-bulk-save-btn');
            this.$saveStatus = $('#etim-bulk-save-status');
            this.$dataJson = $('#etim-bulk-data-json');
            this.$productIds = $('#etim-bulk-product-ids');
            this.$clearGroup = $('#etim-bulk-clear-group');
            this.$clearClass = $('#etim-bulk-clear-class');
        },

        initSelect2: function (element) {
            if (!$.fn.select2) return;
            var options = { width: '100%', dropdownCssClass: 'etim-select2-dropdown' };
            if (element) {
                $(element).select2(options);
            } else {
                this.$groupSelect.select2(options);
                this.$classSelect.select2(options);
                $('#etim-bulk-feature-select').select2({
                    width: '100%',
                    dropdownParent: $('#etim-bulk-add-feature-modal'),
                    dropdownCssClass: 'etim-select2-dropdown'
                });
            }
        },

        initSortable: function () {
            var self = this;
            if ($.fn.sortable) {
                this.$featuresGrid.sortable({
                    items: '.etim-feature-card',
                    cursor: 'grabbing',
                    placeholder: 'etim-feature-card ui-sortable-placeholder',
                    forcePlaceholderSize: true,
                    tolerance: 'pointer',
                    update: function () {
                        self.reorderFeatures();
                    }
                });
            }
        },

        reorderFeatures: function () {
            var self = this;
            var newAssigned = [];
            this.$featuresGrid.find('.etim-feature-card').each(function () {
                var code = $(this).data('feature-code');
                for (var i = 0; i < self.assignedFeatures.length; i++) {
                    if (self.assignedFeatures[i].code === code) {
                        newAssigned.push(self.assignedFeatures[i]);
                        break;
                    }
                }
            });
            this.assignedFeatures = newAssigned;
            this.updateDataJson();
        },

        bindEvents: function () {
            var self = this;

            // Group change
            this.$groupSelect.on('change', function () {
                self.onGroupChange($(this).val());
                self.toggleClearButton(self.$groupSelect, self.$clearGroup);
            });

            // Class change
            this.$classSelect.on('change', function () {
                self.onClassChange($(this).val());
                self.toggleClearButton(self.$classSelect, self.$clearClass);
            });

            // Clear buttons
            this.$clearGroup.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.$groupSelect.val('').trigger('change');
                if ($.fn.select2) self.$groupSelect.trigger('change.select2');
                $(this).hide();
            });

            this.$clearClass.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.$classSelect.val('').trigger('change');
                if ($.fn.select2) self.$classSelect.trigger('change.select2');
                $(this).hide();
            });

            // Add feature
            $('#etim-bulk-add-feature').on('click', function () { self.showAddFeatureModal(); });

            // Sort features
            $('#etim-bulk-sort-features').on('click', function () { self.sortFeaturesAlphabetically(); });

            // Save
            this.$saveBtn.on('click', function () { self.saveData(); });

            // Remove feature
            this.$featuresGrid.on('click', '.etim-remove-feature', function (e) {
                e.preventDefault();
                self.removeFeature($(this).data('feature-code'));
            });

            // Value change
            this.$featuresGrid.on('change', '.etim-feature-value', function () {
                self.updateFeatureValue($(this));
            });

            // Feature name change
            this.$featuresGrid.on('change', '.etim-feature-name-select', function () {
                self.onFeatureNameChange($(this));
            });

            // Toggle logical
            this.$featuresGrid.on('click', '.etim-toggle-btn', function (e) {
                e.preventDefault();
                self.toggleLogicalValue($(this));
            });

            // Range inputs
            this.$featuresGrid.on('input change', '.etim-range-from, .etim-range-to', function () {
                self.updateFeatureValue($(this));
            });

            // Modal close
            this.$container.closest('.etim-bulk-wrap').on('click', '.etim-modal-close, .etim-modal-cancel, .etim-modal-overlay', function () {
                self.hideAddFeatureModal();
            });

            // Confirm add feature
            $('#etim-bulk-confirm-add-feature').on('click', function () { self.confirmAddFeature(); });

            // Enter key in modal
            $('#etim-bulk-add-feature-modal').on('keyup', function (e) {
                if (e.key === 'Enter' && $(this).is(':visible')) {
                    self.confirmAddFeature();
                }
            });

            // Products list toggle
            $('#etim-bulk-products-toggle').on('click', function () {
                var $list = $('#etim-bulk-products-list');
                var $chevron = $(this).find('.etim-bulk-chevron');
                $list.slideToggle(200);
                $chevron.toggleClass('open');
            });

            // Copy single shortcode
            $(document).on('click', '.etim-bulk-copy-single', function () {
                var text = $(this).data('shortcode');
                var $btn = $(this);
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(function () {
                        $btn.addClass('copied');
                        setTimeout(function () { $btn.removeClass('copied'); }, 2000);
                    });
                }
            });

            // Copy main shortcode
            $('#etim-bulk-copy-shortcode').on('click', function () {
                var text = $('#etim-bulk-shortcode-text').text();
                var $btn = $(this);
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(function () {
                        $btn.addClass('copied');
                        setTimeout(function () { $btn.removeClass('copied'); }, 2000);
                    });
                }
            });
        },

        toggleClearButton: function ($select, $clearBtn) {
            if ($select.val()) {
                $clearBtn.css('display', 'flex');
                $select.addClass('has-value');
            } else {
                $clearBtn.hide();
                $select.removeClass('has-value');
            }
        },

        // ==================== GROUPS ====================
        loadGroups: function () {
            var self = this;
            if (!window.etimBulk) return;
            this.showLoading();

            $.ajax({
                url: etimBulk.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_fetch_groups',
                    nonce: etimBulk.nonce
                },
                success: function (response) {
                    self.hideLoading();
                    var groups = [];
                    if (response.success && response.data && response.data.groups) {
                        groups = response.data.groups;
                    } else if (response.data) {
                        groups = response.data;
                    }
                    self.populateGroups(groups);
                },
                error: function () { self.hideLoading(); }
            });
        },

        populateGroups: function (groups) {
            var self = this;
            this.$groupSelect.empty().append('<option value="">Select ETIM Group</option>');
            if (Array.isArray(groups)) {
                $.each(groups, function (i, group) {
                    var code = group.code || group.groupCode || '';
                    var description = group.description || group.groupDescription || code;
                    var text = code + ' - ' + description;
                    if (text.length > 50) text = text.substring(0, 47) + '...';
                    self.$groupSelect.append($('<option></option>').val(code).text(text).data('group', group));
                });
            }
            if ($.fn.select2) this.$groupSelect.trigger('change.select2');
        },

        onGroupChange: function (groupCode) {
            this.$classSelect.empty().append('<option value="">Select ETIM Class</option>');
            this.$featuresGrid.empty();
            this.assignedFeatures = [];
            this.allFeatures = [];
            this.data = [];
            this.$dataJson.val('[]');

            if (!groupCode) {
                this.$classRow.hide();
                this.$featuresSection.hide();
                this.$clearClass.hide();
                return;
            }

            this.currentGroupCode = groupCode;
            this.currentGroup = this.$groupSelect.find('option:selected').data('group');
            this.loadClasses(groupCode);
        },

        // ==================== CLASSES ====================
        loadClasses: function (groupCode) {
            var self = this;
            this.showLoading();
            this.$classRow.css('display', 'flex');

            $.ajax({
                url: etimBulk.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_fetch_classes',
                    group_code: groupCode,
                    nonce: etimBulk.nonce
                },
                success: function (response) {
                    self.hideLoading();
                    var classes = [];
                    if (response.success && response.data && response.data.classes) {
                        classes = response.data.classes;
                    } else if (response.data) {
                        classes = response.data;
                    }
                    self.populateClasses(classes);
                },
                error: function () { self.hideLoading(); }
            });
        },

        populateClasses: function (classes) {
            var self = this;
            this.$classSelect.empty().append('<option value="">Select ETIM Class</option>');
            if (Array.isArray(classes)) {
                $.each(classes, function (i, cls) {
                    var code = cls.code || cls.classCode || '';
                    var description = cls.description || cls.classDescription || code;
                    self.$classSelect.append($('<option></option>').val(code).text(code + ' - ' + description).data('class', cls));
                });
            }
            if ($.fn.select2) this.$classSelect.trigger('change.select2');
            this.$featuresSection.hide();
        },

        onClassChange: function (classCode) {
            this.$featuresGrid.empty();
            this.assignedFeatures = [];
            this.allFeatures = [];
            this.currentClassData = this.$classSelect.find('option:selected').data('class');

            if (!classCode) {
                this.$featuresSection.hide();
                this.data = [];
                this.$dataJson.val('[]');
                return;
            }
            this.loadClassFeatures(classCode);
        },

        // ==================== FEATURES ====================
        loadClassFeatures: function (classCode) {
            var self = this;
            this.showLoading();

            $.ajax({
                url: etimBulk.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_get_class_features',
                    class_code: classCode,
                    nonce: etimBulk.nonce
                },
                success: function (response) {
                    self.hideLoading();
                    var features = [];
                    var classData = { code: classCode };

                    if (self.currentClassData) {
                        classData.description = self.currentClassData.description || self.currentClassData.classDescription || '';
                    }

                    if (response && response.success) {
                        if (response.data && response.data.features) {
                            features = response.data.features;
                            var originalDescription = classData.description;
                            classData = response.data;
                            if (originalDescription && !classData.description) {
                                classData.description = originalDescription;
                            }
                        } else if (Array.isArray(response.data)) {
                            features = response.data;
                            classData.features = features;
                        }
                    }

                    // Attach group data
                    if (self.currentGroup) {
                        classData.group = self.currentGroup;
                    } else if (self.currentGroupCode) {
                        classData.group = { code: self.currentGroupCode, description: '' };
                    }

                    self.allFeatures = features || [];
                    self.currentClass = classData;
                    self.assignedFeatures = [];
                    self.$featuresGrid.empty();
                    self.$featuresSection.show();

                    self.updateDataJson();
                },
                error: function () {
                    self.hideLoading();
                    self.$featuresSection.show();
                }
            });
        },

        // ==================== ADD FEATURE MODAL ====================
        showAddFeatureModal: function () {
            var self = this;
            var $select = $('#etim-bulk-feature-select');
            $select.empty().append('<option value="">Select a feature...</option>');

            var assignedCodes = this.assignedFeatures.map(function (f) { return f.code; });

            var available = [];
            $.each(this.allFeatures, function (i, feature) {
                if (assignedCodes.indexOf(feature.code) === -1) {
                    available.push(feature);
                }
            });
            available.sort(function (a, b) {
                var nameA = (a.description || a.code).toUpperCase();
                var nameB = (b.description || b.code).toUpperCase();
                return nameA < nameB ? -1 : nameA > nameB ? 1 : 0;
            });

            $.each(available, function (i, feature) {
                $select.append($('<option></option>').val(feature.code).text(feature.description || feature.code).data('feature', feature));
            });

            if ($.fn.select2) $select.trigger('change.select2');
            $('#etim-bulk-add-feature-modal').addClass('show');
        },

        hideAddFeatureModal: function () {
            $('#etim-bulk-add-feature-modal').removeClass('show');
        },

        confirmAddFeature: function () {
            var $select = $('#etim-bulk-feature-select');
            var featureCode = $select.val();
            if (!featureCode) return;

            var feature = $select.find('option:selected').data('feature');
            if (feature) {
                this.addFeatureCard($.extend({}, feature, { assignedValue: '' }));
                this.hideAddFeatureModal();
            }
        },

        // ==================== FEATURE CARDS ====================
        addFeatureCard: function (feature, addToAssigned) {
            if (typeof addToAssigned === 'undefined') addToAssigned = true;

            var template = $('#etim-bulk-feature-card-template').html();
            var featureOptions = '';
            var self = this;

            $.each(this.allFeatures, function (i, f) {
                var selected = f.code === feature.code ? ' selected' : '';
                featureOptions += '<option value="' + self.escapeHtml(f.code) + '"' + selected + '>' + self.escapeHtml(f.description || f.code) + '</option>';
            });

            var valueInput = this.buildValueInput(feature);

            var html = template
                .replace(/\{\{code\}\}/g, this.escapeHtml(feature.code))
                .replace('{{featureOptions}}', featureOptions)
                .replace('{{valueInput}}', valueInput);

            this.$featuresGrid.append(html);

            if ($.fn.select2) {
                var newCard = this.$featuresGrid.find('.etim-feature-card').last();
                this.initSelect2(newCard.find('.etim-feature-name-select'));
                if (newCard.find('select.etim-feature-value').length > 0) {
                    this.initSelect2(newCard.find('select.etim-feature-value'));
                }
            }

            if (addToAssigned) {
                this.assignedFeatures.push(feature);
                this.updateDataJson();
            }
        },

        buildValueInput: function (feature) {
            var type = (feature.type || 'alphanumeric').toLowerCase();
            var value = feature.assignedValue || '';
            var unit = feature.unit || {};
            var unitAbbr = unit.abbreviation || unit.abbr || '';
            var html = '';

            var valCodeForSelect = value;
            if (typeof value === 'object' && value !== null) {
                valCodeForSelect = value.code || '';
                value = valCodeForSelect;
            }

            switch (type) {
                case 'logical':
                case 'boolean':
                    var trueActive = (value === 'true' || value === true || value === '1') ? ' active' : '';
                    var falseActive = (value === 'false' || value === false || value === '0') ? ' active' : '';
                    html = '<div class="etim-toggle-group">';
                    html += '<button type="button" class="etim-toggle-btn' + trueActive + '" data-value="true">True</button>';
                    html += '<button type="button" class="etim-toggle-btn' + falseActive + '" data-value="false">False</button>';
                    html += '</div>';
                    html += '<input type="hidden" class="etim-feature-value" value="' + value + '">';
                    break;

                case 'range':
                    var values = String(value).split('::');
                    var fromVal = values[0] || '';
                    var toVal = values[1] || '';
                    html = '<div class="etim-range-input">';
                    html += '<span class="etim-range-label">min</span>';
                    html += '<input type="text" class="etim-input etim-range-from" value="' + this.escapeHtml(fromVal) + '" placeholder="00" />';
                    html += '<span class="etim-range-separator">-</span>';
                    html += '<span class="etim-range-label">max</span>';
                    html += '<input type="text" class="etim-input etim-range-to" value="' + this.escapeHtml(toVal) + '" placeholder="00" />';
                    html += '</div>';
                    break;

                case 'numeric':
                case 'number':
                    html = '<div class="etim-input-with-unit">';
                    html += '<input type="number" step="any" class="etim-input etim-feature-value" value="' + this.escapeHtml(value) + '" placeholder="0.00" />';
                    if (unitAbbr) html += '<span class="etim-unit-label">' + this.escapeHtml(unitAbbr) + '</span>';
                    html += '</div>';
                    break;

                default:
                    if (feature.values && Array.isArray(feature.values) && feature.values.length > 0) {
                        html = '<div class="etim-value-select-wrapper" style="position:relative;width:100%;">';
                        html += '<select class="etim-custom-select etim-feature-value"><option value="">Select value...</option>';
                        $.each(feature.values, function (i, v) {
                            var valCode = v.code || v.value || '';
                            var valDesc = v.description || v.label || valCode;
                            var selected = (valCode == value) ? ' selected' : '';
                            html += '<option value="' + valCode + '"' + selected + '>' + valDesc + '</option>';
                        });
                        html += '</select>';
                        html += '<span class="etim-select-arrow" style="right:12px;"><img src="' + assetsUrl + 'drop.png" alt="" /></span>';
                        html += '</div>';
                    } else {
                        html = '<input type="text" class="etim-input etim-feature-value" value="' + this.escapeHtml(value) + '" placeholder="Text value" />';
                    }
            }
            return html;
        },

        removeFeature: function (code) {
            var index = -1;
            for (var i = 0; i < this.assignedFeatures.length; i++) {
                if (this.assignedFeatures[i].code === code) { index = i; break; }
            }
            if (index > -1) {
                this.assignedFeatures.splice(index, 1);
                this.$featuresGrid.find('.etim-feature-card[data-feature-code="' + code + '"]').fadeOut(300, function () { $(this).remove(); });
                this.updateDataJson();
            }
        },

        onFeatureNameChange: function ($select) {
            var $card = $select.closest('.etim-feature-card');
            var oldCode = $card.data('feature-code');
            var newCode = $select.val();
            if (oldCode === newCode) return;

            var newFeatData = null;
            for (var i = 0; i < this.allFeatures.length; i++) {
                if (this.allFeatures[i].code === newCode) { newFeatData = this.allFeatures[i]; break; }
            }

            if (newFeatData) {
                var newFeat = $.extend({}, newFeatData, { assignedValue: '' });
                var assignedIdx = -1;
                for (var j = 0; j < this.assignedFeatures.length; j++) {
                    if (this.assignedFeatures[j].code === oldCode) { assignedIdx = j; break; }
                }

                if (assignedIdx > -1) {
                    this.assignedFeatures[assignedIdx] = newFeat;

                    var template = $('#etim-bulk-feature-card-template').html();
                    var featureOptions = '';
                    var self = this;
                    $.each(this.allFeatures, function (i, f) {
                        var selected = f.code === newCode ? ' selected' : '';
                        featureOptions += '<option value="' + self.escapeHtml(f.code) + '"' + selected + '>' + self.escapeHtml(f.description || f.code) + '</option>';
                    });

                    var valueInput = this.buildValueInput(newFeat);
                    var newHtml = template
                        .replace(/\{\{code\}\}/g, this.escapeHtml(newFeat.code))
                        .replace('{{featureOptions}}', featureOptions)
                        .replace('{{valueInput}}', valueInput);

                    $card.replaceWith(newHtml);

                    var newCard = this.$featuresGrid.find('.etim-feature-card[data-feature-code="' + newCode + '"]');
                    if ($.fn.select2) {
                        this.initSelect2(newCard.find('.etim-feature-name-select'));
                        if (newCard.find('select.etim-feature-value').length > 0) {
                            this.initSelect2(newCard.find('select.etim-feature-value'));
                        }
                    }
                    this.updateDataJson();
                }
            }
        },

        toggleLogicalValue: function ($btn) {
            var $group = $btn.parent();
            $group.find('.etim-toggle-btn').removeClass('active');
            $btn.addClass('active');
            var val = $btn.data('value');
            $group.find('.etim-feature-value').val(val).trigger('change');
            this.updateFeatureValue($group.find('.etim-feature-value'));
        },

        updateFeatureValue: function ($input) {
            var $card = $input.closest('.etim-feature-card');
            var code = String($card.data('feature-code'));
            var value = '';

            if ($card.find('.etim-range-input').length > 0) {
                var from = $card.find('.etim-range-from').val();
                var to = $card.find('.etim-range-to').val();
                value = from + '::' + to;
            } else if ($card.find('.etim-feature-value').is('select')) {
                var $opt = $card.find('.etim-feature-value option:selected');
                if ($opt.val()) {
                    value = { code: $opt.val(), description: $opt.text() };
                } else {
                    value = '';
                }
            } else {
                value = $card.find('.etim-feature-value').val();
            }

            for (var i = 0; i < this.assignedFeatures.length; i++) {
                if (String(this.assignedFeatures[i].code) === code) {
                    this.assignedFeatures[i].assignedValue = value;
                    break;
                }
            }
            this.updateDataJson();
        },

        sortFeaturesAlphabetically: function () {
            var self = this;
            this.assignedFeatures.sort(function (a, b) {
                var nameA = (a.description || a.code).toUpperCase();
                var nameB = (b.description || b.code).toUpperCase();
                return nameA < nameB ? -1 : nameA > nameB ? 1 : 0;
            });

            this.$featuresGrid.empty();
            $.each(this.assignedFeatures, function (i, f) {
                self.addFeatureCard(f, false);
            });
            this.updateDataJson();
        },

        // ==================== DATA / SAVE ====================
        updateDataJson: function () {
            if (this.currentClass) {
                this.currentClass.features = this.assignedFeatures;
                this.data = [this.currentClass];
                this.$dataJson.val(JSON.stringify(this.data));
            }
        },

        /**
         * Validate feature values are filled (mandatory when features are added)
         */
        validateFeatureValues: function () {
            if (this.assignedFeatures.length === 0) {
                return true;
            }

            var emptyFeatures = [];
            var self = this;

            this.assignedFeatures.forEach(function (feature) {
                var val = feature.assignedValue;
                var isEmpty = false;
                if (val === '' || val === null || val === undefined) isEmpty = true;
                else if (typeof val === 'object' && val !== null && !val.code && !val.description) isEmpty = true;
                else if (typeof val === 'string' && (val === '::' || val.trim() === '')) isEmpty = true;
                if (isEmpty) emptyFeatures.push(feature.description || feature.code);
            });

            if (emptyFeatures.length > 0) {
                this.$featuresGrid.find('.etim-feature-card').each(function () {
                    var code = $(this).data('feature-code');
                    var feature = self.assignedFeatures.find(function (f) { return f.code === code; });
                    if (feature) {
                        var val = feature.assignedValue;
                        var isEmpty = false;
                        if (val === '' || val === null || val === undefined) isEmpty = true;
                        else if (typeof val === 'object' && val !== null && !val.code && !val.description) isEmpty = true;
                        else if (typeof val === 'string' && (val === '::' || val.trim() === '')) isEmpty = true;
                        if (isEmpty) {
                            $(this).addClass('etim-validation-error');
                            $(this).find('.etim-feature-value-field').addClass('etim-field-required');
                        } else {
                            $(this).removeClass('etim-validation-error');
                            $(this).find('.etim-feature-value-field').removeClass('etim-field-required');
                        }
                    }
                });
                this.$saveStatus.addClass('error').text('Please fill in all feature values. ' + emptyFeatures.length + ' feature(s) missing values.');
                return false;
            }

            this.$featuresGrid.find('.etim-feature-card').removeClass('etim-validation-error');
            this.$featuresGrid.find('.etim-feature-value-field').removeClass('etim-field-required');
            return true;
        },

        saveData: function () {
            var self = this;
            this.updateDataJson();

            // Validate feature values are filled (mandatory)
            if (!this.validateFeatureValues()) {
                return;
            }

            var jsonVal = this.$dataJson.val();
            var productIds = this.$productIds.val();

            if (!productIds) {
                this.$saveStatus.addClass('error').text('No products selected.');
                return;
            }

            if (!jsonVal || jsonVal === '[]' || jsonVal === '') {
                if (this.assignedFeatures.length > 0 && this.currentClass) {
                    this.currentClass.features = this.assignedFeatures;
                    this.data = [this.currentClass];
                    this.$dataJson.val(JSON.stringify(this.data));
                    jsonVal = this.$dataJson.val();
                }
            }

            this.$saveBtn.text(etimBulk.strings.saving || 'Saving...').prop('disabled', true);
            this.$saveStatus.text('').removeClass('success error');

            $.ajax({
                url: etimBulk.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'etim_bulk_save_data',
                    nonce: etimBulk.nonce,
                    product_ids: productIds,
                    etim_data: jsonVal
                },
                success: function (response) {
                    self.$saveBtn.text('Save ETIM Data').prop('disabled', false);
                    if (response.success) {
                        self.$saveStatus.addClass('success').text(response.data.message || 'Data saved successfully!');
                        setTimeout(function () { self.$saveStatus.text('').removeClass('success'); }, 4000);
                    } else {
                        self.$saveStatus.addClass('error').text(response.data.message || 'Error saving data.');
                    }
                },
                error: function () {
                    self.$saveBtn.text('Save ETIM Data').prop('disabled', false);
                    self.$saveStatus.addClass('error').text('Network error. Check console.');
                }
            });
        },

        // ==================== HELPERS ====================
        escapeHtml: function (text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        showLoading: function () { this.$loading.removeClass('etim-hidden'); },
        hideLoading: function () { this.$loading.addClass('etim-hidden'); }
    };

    $(document).ready(function () {
        if ($('#etim-bulk-container').length > 0) {
            window.ETIM_BULK.init();
        }
    });

})(jQuery);
