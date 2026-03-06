(function ($) {
    'use strict';

    var assetsUrl = window.etimAssetsUrl || '';

    window.ETIM = {
        data: window.etimDataInitial || [],
        currentClass: null,
        currentGroupCode: null,
        allFeatures: [],
        assignedFeatures: [],
        isRestoring: false,

        init: function () {
            var self = this;
            this.cacheElements();
            this.initSelect2();
            this.bindEvents();
            this.loadGroups();
            this.initSortable();

            // Initialize the hidden field with existing data
            if (this.data && this.data.length > 0) {
                this.$dataJson.val(JSON.stringify(this.data));
            }

            // Restore state if data exists
            setTimeout(function () { self.restoreState(); }, 100);
        },

        initSelect2: function (element) {
            if (!$.fn.select2) return;
            var options = { width: '100%', dropdownCssClass: 'etim-select2-dropdown' };
            if (element) {
                $(element).select2(options);
            } else {
                this.$groupSelect.select2(options);
                this.$classSelect.select2(options);
                $('#etim-feature-select').select2({ width: '100%', dropdownParent: $('#etim-add-feature-modal'), dropdownCssClass: 'etim-select2-dropdown' });
            }
        },

        cacheElements: function () {
            this.$container = $('#etim-meta-box-container');
            this.$groupSelect = $('#etim-group-select');
            this.$classSelect = $('#etim-class-select');
            this.$classRow = $('#etim-class-row');
            this.$featuresSection = $('#etim-features-section');
            this.$featuresGrid = $('#etim-features-grid');
            this.$emptyFeatures = $('#etim-empty-features');
            this.$loading = $('#etim-loading-indicator');
            this.$saveSection = $('#etim-save-section');
            this.$saveBtn = $('#etim-save-btn');
            this.$saveStatus = $('#etim-save-status');
            this.$dataJson = $('#etim-data-json');
            this.$header = $('#etim-header');
            this.$clearGroup = $('#etim-clear-group');
            this.$clearClass = $('#etim-clear-class');
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
                    update: function (event, ui) {
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
                var feature = null;
                for (var i = 0; i < self.assignedFeatures.length; i++) {
                    if (self.assignedFeatures[i].code === code) {
                        feature = self.assignedFeatures[i];
                        break;
                    }
                }
                if (feature) newAssigned.push(feature);
            });

            this.assignedFeatures = newAssigned;
            this.updateDataJson();
        },

        bindEvents: function () {
            var self = this;

            this.$groupSelect.on('change', function () {
                self.onGroupChange($(this).val());
                self.toggleClearButton(self.$groupSelect, self.$clearGroup);
            });

            this.$classSelect.on('change', function () {
                self.onClassChange($(this).val());
                self.toggleClearButton(self.$classSelect, self.$clearClass);
            });

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

            $('#etim-add-feature').on('click', function () { self.showAddFeatureModal(); });

            $('#etim-sort-features').on('click', function () {
                self.sortFeaturesAlphabetically();
            });

            this.$saveBtn.on('click', function () { self.saveData(); });

            this.$featuresGrid.on('click', '.etim-remove-feature', function (e) {
                e.preventDefault();
                self.removeFeature($(this).data('feature-code'));
            });

            this.$featuresGrid.on('change', '.etim-feature-value', function () {
                self.updateFeatureValue($(this));
            });

            // Handle Select Feature Name Change
            this.$featuresGrid.on('change', '.etim-feature-name-select', function () {
                self.onFeatureNameChange($(this));
            });

            this.$featuresGrid.on('click', '.etim-toggle-btn', function (e) {
                e.preventDefault();
                self.toggleLogicalValue($(this));
            });

            this.$featuresGrid.on('input change', '.etim-range-from, .etim-range-to', function () {
                self.updateFeatureValue($(this));
            });

            this.$container.on('click', '.etim-modal-close, .etim-modal-cancel, .etim-modal-overlay', function () {
                self.hideAddFeatureModal();
            });

            $('#etim-confirm-add-feature').on('click', function () { self.confirmAddFeature(); });

            $('#etim-add-feature-modal').on('keyup', function (e) {
                if (e.key === 'Enter' && $(this).is(':visible')) {
                    self.confirmAddFeature();
                }
            });
        },

        toggleClearButton: function ($select, $clearBtn) {
            if ($select.val()) {
                $clearBtn.css('display', 'flex'); // Use flex to center icon
                $select.addClass('has-value');
            } else {
                $clearBtn.hide();
                $select.removeClass('has-value');
            }
        },

        sortFeaturesAlphabetically: function () {
            var self = this;
            this.assignedFeatures.sort(function (a, b) {
                var nameA = (a.description || a.code).toUpperCase();
                var nameB = (b.description || b.code).toUpperCase();
                return (nameA < nameB) ? -1 : (nameA > nameB) ? 1 : 0;
            });

            this.$featuresGrid.empty();
            $.each(this.assignedFeatures, function (i, f) {
                self.addFeatureCard(f, false);
            });
            this.updateDataJson();
        },

        loadGroups: function () {
            var self = this;
            if (!window.etimProductMeta) return;

            this.showLoading();

            $.ajax({
                url: etimProductMeta.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_fetch_groups',
                    nonce: etimProductMeta.nonce
                },
                success: function (response) {
                    self.hideLoading();
                    var groups = response.data || [];
                    if (response.success && response.data && response.data.groups) groups = response.data.groups;
                    self.populateGroups(groups);
                },
                error: function () { self.hideLoading(); }
            });
        },

        populateGroups: function (groups) {
            var self = this;
            this.$groupSelect.empty().append('<option value="">Select Etim Group</option>');
            if (Array.isArray(groups)) {
                $.each(groups, function (i, group) {
                    var code = group.code || group.groupCode || '';
                    var description = group.description || group.groupDescription || code;
                    // Limit text length for pill design?
                    var text = code + ' - ' + description;
                    if (text.length > 50) text = text.substring(0, 47) + '...';
                    self.$groupSelect.append($('<option></option>').val(code).text(text).data('group', group));
                });
            }
            if ($.fn.select2) this.$groupSelect.trigger('change.select2');
        },

        onGroupChange: function (groupCode) {
            if (this.isRestoring) {
                // During restore, only load classes without clearing existing data
                this.currentGroupCode = groupCode;
                this.currentGroup = this.$groupSelect.find('option:selected').data('group');
                if (groupCode) {
                    this.loadClasses(groupCode);
                }
                return;
            }

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

        loadClasses: function (groupCode) {
            var self = this;
            this.showLoading();
            this.$classRow.css('display', 'flex'); // Ensure Flex for alignment

            $.ajax({
                url: etimProductMeta.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_fetch_classes',
                    group_code: groupCode,
                    nonce: etimProductMeta.nonce
                },
                success: function (response) {
                    self.hideLoading();
                    var classes = response.data || [];
                    if (response.success && response.data && response.data.classes) classes = response.data.classes;
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
                this.updateEmptyState();
                return;
            }
            this.loadClassFeatures(classCode);
        },

        loadClassFeatures: function (classCode) {
            var self = this;
            this.showLoading();

            $.ajax({
                url: etimProductMeta.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_get_class_features',
                    class_code: classCode,
                    nonce: etimProductMeta.nonce
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

                    // Attach Group Data for Saving
                    if (self.currentGroup) {
                        classData.group = self.currentGroup;
                    } else if (self.currentGroupCode) {
                        classData.group = { code: self.currentGroupCode, description: '' };
                    }

                    self.allFeatures = features || [];
                    // Sort features alphabetically by default?
                    // self.allFeatures.sort(...) 

                    self.currentClass = classData;
                    self.assignedFeatures = [];
                    self.$featuresGrid.empty();
                    self.$featuresSection.show();
                    self.$saveSection.show();

                    self.updateDataJson();
                    self.updateEmptyState();
                },
                error: function () {
                    self.hideLoading();
                    self.$featuresSection.show();
                }
            });
        },

        restoreState: function () {
            var self = this;
            if (this.data && Array.isArray(this.data) && this.data.length > 0) {
                var savedClass = this.data[0];
                if (!savedClass) return;

                var groupCode = (savedClass.group && savedClass.group.code) ? savedClass.group.code : '';

                if (groupCode) {
                    self.isRestoring = true;
                    var interval = setInterval(function () {
                        if (self.$groupSelect.find('option').length > 1) {
                            clearInterval(interval);
                            self.$groupSelect.val(groupCode).trigger('change');

                            var classInterval = setInterval(function () {
                                if (self.$classSelect.find('option').length > 1) {
                                    clearInterval(classInterval);
                                    var classCode = savedClass.code;
                                    self.$classSelect.val(classCode);
                                    if ($.fn.select2) {
                                        self.$classSelect.trigger('change.select2');
                                    }
                                    self.$classRow.css('display', 'flex');
                                    self.toggleClearButton(self.$classSelect, self.$clearClass);
                                    self.loadClassFeaturesWithRestore(classCode, savedClass.features);
                                }
                            }, 500);
                        }
                    }, 200);
                }
            }
        },

        loadClassFeaturesWithRestore: function (classCode, savedFeatures) {
            var self = this;
            this.showLoading();
            $.ajax({
                url: etimProductMeta.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'etim_get_class_features',
                    class_code: classCode,
                    nonce: etimProductMeta.nonce
                },
                success: function (response) {
                    self.hideLoading();
                    var features = (response.data && response.data.features) ? response.data.features : response.data;
                    self.allFeatures = Array.isArray(features) ? features : [];
                    self.assignedFeatures = [];
                    self.$featuresGrid.empty();
                    self.$featuresSection.show();
                    self.$saveSection.css('display', 'flex');

                    // Build currentClass from response data - THIS IS THE CRITICAL FIX
                    var classData = { code: classCode };
                    if (response && response.success && response.data) {
                        classData = $.extend({}, response.data);
                    }
                    classData.code = classCode;

                    // Get class description from the select dropdown
                    var selectedClassData = self.$classSelect.find('option:selected').data('class');
                    if (selectedClassData && selectedClassData.description) {
                        classData.description = classData.description || selectedClassData.description;
                    }

                    // Attach group data
                    if (self.currentGroup) {
                        classData.group = self.currentGroup;
                    } else if (self.currentGroupCode) {
                        classData.group = { code: self.currentGroupCode, description: '' };
                    }

                    self.currentClass = classData;

                    if (savedFeatures && Array.isArray(savedFeatures)) {
                        savedFeatures.forEach(function (sFeat) {
                            var fullFeat = self.allFeatures.find(function (f) { return f.code === sFeat.code; });
                            var featToRender = fullFeat ? $.extend({}, fullFeat, { assignedValue: sFeat.assignedValue }) : sFeat;
                            self.addFeatureCard(featToRender);
                        });
                    }

                    // Update the hidden JSON field with restored data
                    self.updateDataJson();
                    self.isRestoring = false;
                }
            });
        },

        updateEmptyState: function () {
            if (this.assignedFeatures.length === 0) {
                this.$emptyFeatures.show();
                this.$featuresGrid.hide();
            } else {
                this.$emptyFeatures.hide();
                this.$featuresGrid.show();
            }
        },

        showAddFeatureModal: function () {
            var self = this;
            var $select = $('#etim-feature-select');
            $select.empty().append('<option value="">Select a feature...</option>');

            var assignedCodes = this.assignedFeatures.map(function (f) { return f.code; });

            // Populate modal with unassigned features
            // Sort them for easier finding
            var availableLibs = [];
            $.each(this.allFeatures, function (i, feature) {
                if (assignedCodes.indexOf(feature.code) === -1) {
                    availableLibs.push(feature);
                }
            });
            availableLibs.sort(function (a, b) {
                var nameA = (a.description || a.code).toUpperCase();
                var nameB = (b.description || b.code).toUpperCase();
                return (nameA < nameB) ? -1 : (nameA > nameB) ? 1 : 0;
            });

            $.each(availableLibs, function (i, feature) {
                $select.append($('<option></option>').val(feature.code).text((feature.description || feature.code)).data('feature', feature));
            });

            if ($.fn.select2) $select.trigger('change.select2');

            $('#etim-add-feature-modal').addClass('show');
        },

        hideAddFeatureModal: function () {
            $('#etim-add-feature-modal').removeClass('show');
        },

        confirmAddFeature: function () {
            var $select = $('#etim-feature-select');
            var featureCode = $select.val();
            if (!featureCode) return;

            var feature = $select.find('option:selected').data('feature');
            if (feature) {
                this.addFeatureCard($.extend({}, feature, { assignedValue: '' }));
                this.hideAddFeatureModal();
            }
        },

        addFeatureCard: function (feature, addToAssigned) {
            if (typeof addToAssigned === 'undefined') addToAssigned = true;

            var template = $('#etim-feature-card-template').html();
            var featureOptions = '';

            var self = this;
            // Build simple select options for the Feature Name dropdown
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

            // Re-initialize select2 for the newly added feature selects
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
                this.updateEmptyState();
            }
        },

        buildValueInput: function (feature) {
            var type = (feature.type || 'alphanumeric').toLowerCase();
            var value = feature.assignedValue || '';
            var unit = feature.unit || {};
            var unitAbbr = unit.abbreviation || unit.abbr || '';
            var html = '';

            // Clean value
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
                    // Range inputs with labels inside/beside
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
                    // Alphanumeric with values
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
                setTimeout(function () { window.ETIM.updateEmptyState(); }, 350);
            }
        },

        onFeatureNameChange: function ($select) {
            var $card = $select.closest('.etim-feature-card');
            var oldCode = $card.data('feature-code');
            var newCode = $select.val();
            if (oldCode === newCode) return;

            var newFeatureIdx = -1;
            for (var i = 0; i < this.allFeatures.length; i++) {
                if (this.allFeatures[i].code === newCode) { newFeatureIdx = i; break; }
            }

            if (newFeatureIdx > -1) {
                var newFeat = $.extend({}, this.allFeatures[newFeatureIdx], { assignedValue: '' });

                // Find and replace in assignedFeatures
                var assignedIdx = -1;
                for (var j = 0; j < this.assignedFeatures.length; j++) {
                    if (this.assignedFeatures[j].code === oldCode) { assignedIdx = j; break; }
                }

                if (assignedIdx > -1) {
                    this.assignedFeatures[assignedIdx] = newFeat;

                    // Re-render this specific card completely
                    var template = $('#etim-feature-card-template').html();
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
                    value = {
                        code: $opt.val(),
                        description: $opt.text()
                    };
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

        updateDataJson: function () {
            if (this.currentClass) {
                this.currentClass.features = this.assignedFeatures;
                this.data = [this.currentClass];
                this.$dataJson.val(JSON.stringify(this.data));
            }
        },

        /**
         * Validate that all assigned features have values filled in.
         * Returns true if valid, false if there are empty values.
         * If no features are added (class only), returns true (allow save).
         */
        validateFeatureValues: function () {
            if (this.assignedFeatures.length === 0) {
                return true; // Allow saving with class only, no features
            }

            var emptyFeatures = [];
            var self = this;

            this.assignedFeatures.forEach(function (feature) {
                var val = feature.assignedValue;
                var isEmpty = false;

                if (val === '' || val === null || val === undefined) {
                    isEmpty = true;
                } else if (typeof val === 'object' && val !== null) {
                    if (!val.code && !val.description) isEmpty = true;
                } else if (typeof val === 'string') {
                    // Check range values
                    if (val === '::') isEmpty = true;
                    if (val.trim() === '') isEmpty = true;
                }

                if (isEmpty) {
                    emptyFeatures.push(feature.description || feature.code);
                }
            });

            if (emptyFeatures.length > 0) {
                // Highlight empty feature cards
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

            // Clear all validation errors
            this.$featuresGrid.find('.etim-feature-card').removeClass('etim-validation-error');
            this.$featuresGrid.find('.etim-feature-value-field').removeClass('etim-field-required');
            return true;
        },

        saveData: function () {
            var self = this;

            // Safety: re-sync the hidden JSON field before saving
            this.updateDataJson();

            // Validate feature values are filled (mandatory)
            if (!this.validateFeatureValues()) {
                return;
            }

            var jsonVal = this.$dataJson.val();
            // Prevent accidental deletion: if we have features on screen but JSON is empty
            if ((!jsonVal || jsonVal === '[]' || jsonVal === '') && this.assignedFeatures.length > 0) {
                console.warn('ETIM: Data mismatch detected - features exist but JSON is empty. Rebuilding...');
                if (this.currentClass) {
                    this.currentClass.features = this.assignedFeatures;
                    this.data = [this.currentClass];
                    this.$dataJson.val(JSON.stringify(this.data));
                    jsonVal = this.$dataJson.val();
                }
            }

            this.$saveBtn.text('Saving...').prop('disabled', true);
            this.$saveStatus.text('').removeClass('success error');

            $.ajax({
                url: etimProductMeta.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'etim_save_product_data',
                    nonce: etimProductMeta.nonce,
                    product_id: etimProductMeta.productId,
                    etim_data: jsonVal
                },
                success: function (response) {
                    self.$saveBtn.text('Save ETIM Data').prop('disabled', false);
                    if (response.success) {
                        self.$saveStatus.addClass('success').text('Data saved successfully!');
                        setTimeout(function () { self.$saveStatus.text(''); }, 3000);
                    } else {
                        // Handle product limit reached
                        if (response.data && response.data.error_type === 'product_limit_reached') {
                            self.showProductLimitModal(response.data);
                            return;
                        }
                        self.$saveStatus.addClass('error').text(response.data.message || 'Error saving data.');
                    }
                },
                error: function () {
                    self.$saveBtn.text('Save ETIM Data').prop('disabled', false);
                    self.$saveStatus.addClass('error').text('Network error. Check console.');
                }
            });
        },

        escapeHtml: function (text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        },

        showProductLimitModal: function (data) {
            $('#etim-limit-modal').remove();

            var count = data.current_count || 0;
            var max = data.max_allowed || 0;
            var url = data.upgrade_url || '#';
            var imgUrl = assetsUrl + 'pro.png';

            var html = '<div id="etim-limit-modal" class="etim-limit-modal-overlay">';
            html += '<div class="etim-limit-modal-content" style="max-width:420px;border-radius:20px;padding:40px 32px;text-align:center;position:relative;">';
            html += '<button type="button" class="etim-limit-btn-close" style="position:absolute;top:12px;right:12px;width:32px;height:32px;border-radius:50%;background:#fef2f2;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:#ef4444;font-weight:700;">&times;</button>';
            html += '<div class="etim-limit-modal-image"><img src="' + imgUrl + '" alt="" style="max-width:200px;height:auto;" /></div>';
            html += '<h3 style="color:#4888E8;font-size:20px;font-weight:700;margin:16px 0 12px;">Product Limit Reached</h3>';
            html += '<p style="font-size:14px;color:#334155;line-height:1.6;">You have assigned ETIM Data to <strong>' + count + ' of ' + max + ' products</strong><br>allowed on your current plan</p>';
            html += '<p style="color:#64748b;font-size:13px;margin-bottom:24px;">Upgrade your plan to assign ETIM data to more products</p>';
            html += '<div class="etim-limit-modal-actions">';
            html += '<a href="' + url + '" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:12px 32px;border-radius:30px;font-size:14px;font-weight:700;text-decoration:none;box-shadow:0 4px 12px rgba(245,158,11,0.3);">&#128081; Upgrade</a>';
            html += '</div></div></div>';

            $('body').append(html);

            $(document).on('click.etim-limit', '.etim-limit-btn-close', function () {
                $('#etim-limit-modal').remove();
                $(document).off('click.etim-limit');
            });
            $(document).on('click.etim-limit', '.etim-limit-modal-overlay', function (e) {
                if (e.target === this) {
                    $('#etim-limit-modal').remove();
                    $(document).off('click.etim-limit');
                }
            });
        },

        showProductLimitWarning: function () {
            if ($('#etim-limit-warning').length) return;
            var fa = window.etimProductMeta ? window.etimProductMeta.featureAccess : null;
            if (!fa) return;

            var count = fa.assignedCount || 0;
            var max = fa.productLimit || 0;
            var url = fa.upgradeUrl || '#';

            if (max === -1) return; // unlimited

            var html = '<div id="etim-limit-warning" class="etim-limit-warning-banner" style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;margin-bottom:16px;">';
            html += '<img src="' + assetsUrl + 'open.png" alt="" style="width:32px;height:32px;flex-shrink:0;" />';
            html += '<span style="font-size:13px;color:#991b1b;line-height:1.5;">Product limit reached (<strong>' + count + '/' + max + '</strong>). You cannot assign ETIM data to new products. ';
            html += '<a href="' + url + '" target="_blank" style="color:#4888E8;font-weight:600;">Upgrade your plan</a></span>';
            html += '</div>';

            this.$container.prepend(html);
        },

        showLoading: function () { this.$loading.removeClass('etim-hidden'); },
        hideLoading: function () { this.$loading.addClass('etim-hidden'); }
    };

    $(document).ready(function () {
        window.ETIM.init();

        // Proactive warning if product can't be assigned
        var fa = window.etimProductMeta ? window.etimProductMeta.featureAccess : null;
        if (fa && fa.canAssign === false) {
            window.ETIM.showProductLimitWarning();
        }
    });

})(jQuery);
