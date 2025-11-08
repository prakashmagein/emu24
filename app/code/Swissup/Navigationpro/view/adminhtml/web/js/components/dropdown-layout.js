define([
    'jquery',
    'underscore',
    'mageUtils',
    'ko',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Catalog/js/components/visible-on-option/strategy',
    'Swissup_Navigationpro/lib/dragula/dragula',
    'Magento_Ui/js/modal/confirm'
], function (
    $,
    _,
    utils,
    ko,
    registry,
    Abstract,
    visibleOnOptionStrategy,
    dragula,
    confirm
) {
    'use strict';

    return Abstract.extend(visibleOnOptionStrategy).extend({
        defaults: {
            elementTmpl: 'Swissup_Navigationpro/dropdown-layout',
            position: 'left',
            width: 'small',
            default: JSON.stringify({
                'start': {
                    'size': 0,
                    'rows': []
                },
                'center': {
                    'size': 12,
                    'rows': [[{
                        'id': utils.uniqueid(),
                        'size': '12',
                        'type': 'children',
                        'is_active': '1',
                        'columns_count': '1'
                    }]]
                },
                'end': {
                    'size': 0,
                    'rows': []
                }
            })
        },

        /**
         * @returns {exports}
         */
        initObservable: function () {
            this._super();

            this.observe([
                'width',
                'position'
            ]);

            return this;
        },

        /**
         * @returns {exports}
         */
        initialize: function () {
            this._super();

            this.start = {
                code: 'start',
                size: this.getSize.bind(this, 'start'),
                rows: this.getRows.bind(this, 'start')
            };
            this.center = {
                code: 'center',
                size: this.getSize.bind(this, 'center'),
                rows: this.getRows.bind(this, 'center')
            };
            this.end = {
                code: 'end',
                size: this.getSize.bind(this, 'end'),
                rows: this.getRows.bind(this, 'end')
            };

            $.async(
                '#' + this.uid + ' .region .navpro-row',
                this,
                this.enableUI.bind(this)
            );

            $.async(
                '#' + this.uid + ' .region .navpro-row .item',
                this,
                this.initItemObservers.bind(this)
            );

            var self = this;
            this.width.subscribe(function () {
                // give some time to resize an element
                setTimeout(self.enableUI.bind(self), 100);
            });
            this.visible.subscribe(function () {
                // give some time to resize an element
                setTimeout(self.enableUI.bind(self), 100);
            });

            return this;
        },

        /**
         * @param {Element} item
         */
        initItemObservers: function (item) {
            var self = this;
            $(item).off('dblclick');
            $(item).on('dblclick', function () {
                self.editItem(ko.dataFor(item));
            });
        },

        /**
         * @returns {bool}
         */
        isFirstLevel: function () {
            return !registry.get(this.provider).data.level ||
                registry.get(this.provider).data.level <= 2;
        },

        /**
         * @returns {String}
         */
        getNavbarItems: function () {
            var items = [];

            if (registry.get(this.provider).data.name) {
                items.push(registry.get(this.provider).data.name);
            } else {
                items.push('Link Example');
            }

            return items;
        },

        /**
         * @return {Object}
         */
        getJsonValue: function () {
            return JSON.parse(this.value());
        },

        /**
         * @param {Object} json
         * @returns {exports}
         */
        setJsonValue: function (json) {
            this.value(JSON.stringify(json));
            this.enableUI();

            return this;
        },

        /**
         * Find item by id
         * @param  {String} id
         * @return {Object}
         */
        findItem: function (id) {
            var item = {};
            _.find(this.getRegions(), function (region) {
                return _.find(region.rows(), function (row) {
                    item = _.findWhere(row, {
                        id: id
                    });

                    return item;
                });
            });

            return item;
        },

        /**
         * Try to find and update item values by id
         * @param  {Numeric} id
         * @param  {Object} values
         * @return {Boolean} True on success, False if item not found
         */
        updateItem: function (id, values) {
            var currentValue = this.getJsonValue(),
                regionCode,
                rowIndex,
                itemIndex;

            _.find(this.getRegions(), function (region) {
                regionCode = region.code;
                rowIndex = _.findIndex(region.rows(), function (row) {
                    itemIndex = _.findIndex(row, function (_item) {
                        return _item.id == id;
                    });

                    return itemIndex > -1;
                });

                return rowIndex > -1;
            });

            if (rowIndex > -1) {
                if (values === false) {
                    currentValue[regionCode].rows[rowIndex].splice(itemIndex, 1);

                    if (!currentValue[regionCode].rows[rowIndex].length) {
                        currentValue[regionCode].rows.splice(rowIndex, 1);

                        if (!currentValue[regionCode].rows[0]) {
                            currentValue[regionCode].rows[0] = [];
                        }
                    }
                } else {
                    _.extend(
                        currentValue[regionCode].rows[rowIndex][itemIndex],
                        values
                    );
                }

                this.setJsonValue(currentValue);

                return true;
            }

            return false;
        },

        /**
         * Update region size
         * @param  {String} regionCode
         * @param  {Number} size
         */
        updateRegionSize: function (regionCode, size) {
            var currentValue = this.getJsonValue(),
                oldSize = currentValue[regionCode].size;

            currentValue[regionCode].size = size;
            currentValue.center.size += oldSize - size;

            this.setJsonValue(currentValue);
        },

        /**
         * @returns {Array}
         */
        getRegions: function () {
            return [
                // this.start,
                this.center
                // ,this.end
            ];
        },

        /**
         * @param {String} region
         * @return {Number}
         */
        getSize: function (region) {
            return this.getJsonValue()[region].size;
        },

        /**
         * @param {String} region
         * @return {Array}
         */
        getRows: function (region) {
            return this.getJsonValue()[region].rows;
        },

        /**
         * @param {Array} row
         */
        getRowSize: function (row) {
            return row.reduce(function (sum, current) {
                return sum + current.size;
            }, 0);
        },

        //
        // DD & Resize methods
        //
        /**
         * Enable UI support
         */
        enableUI: function () {
            var component = this,
                gridSize = 12;

            if (component.drake) {
                try {
                    component.drake.destroy();
                } catch (e) {
                    // "The provided double value is non-finite."
                }
            }

            component.drake = dragula($('.region .navpro-row', '#' + this.uid).toArray(), {
                    revertOnSpill: true,
                    /**
                     * @param {Element} el
                     * @param {Element} container
                     * @param {Element} handle
                     * @returns {jQuery}
                     */
                    moves: function (el, container, handle) {
                        // allow to resize with outer DOMElement (.navpro-entity)
                        return $(handle).hasClass('item');
                    }
                })
                .on('drag', function (el) {
                    $('#' + component.uid).addClass('drag');
                    // if (!$(el).siblings().length) {
                    //     $(el).parent('.navpro-row').prev('.navpro-row').addClass('hidden');
                    //     $(el).parent('.navpro-row').next('.navpro-row').addClass('hidden');
                    // }
                })
                .on('dragend', function (el) {
                    $('#' + component.uid).removeClass('drag');
                    // $('.navpro-row.hidden', '.navpro').removeClass('hidden');
                })
                .on('drop', function () {
                    component.updateAfterDD();
                })
                .on('over', function (el, container) {
                    $(container).addClass('over');
                })
                .on('out', function (el, container) {
                    $(container).removeClass('over');
                });

            $('.navpro-entity, .navpro-col.region-start, .navpro-col.region-end', '#' + this.uid).each(function () {
                var gridStep = Math.round($(this).parent('.navpro-row').width() / gridSize),
                    handles = 'e',
                    autoHide = true,
                    regionCode;

                if (regionCode = $(this).data('region')) {
                    autoHide = false;

                    if (regionCode === 'end') {
                        handles = 'w';
                    }
                }

                $(this).resizable({
                    helper: 'navpro-resizable-helper',
                    autoHide: autoHide,
                    grid: [gridStep, 0],
                    handles: handles,
                    /**
                     * @param {Event} event
                     * @param {Object} ui
                     */
                    stop: function (event, ui) {
                        var match, size, item;

                        // do not change width and height, but use navpro-col-* class instead
                        ui.element.css({
                            width: '',
                            height: '',
                            left: '',
                            right: ''
                        });

                        // match old navpro-col-* class name and col size
                        match = ui.element.attr('class').match(/navpro-col-\d+/);

                        if (!match) {
                            return;
                        }

                        size = Math.round(ui.size.width / gridStep);

                        if (size < 1 && !ui.element.data('region')) {
                            // region size could be 0
                            size = 1;
                        } else if (size > gridSize) {
                            size = gridSize;
                        }

                        ui.element
                            .removeClass(match[0])
                            .addClass('navpro-col-' + size);

                        if (ui.element.data('region')) {
                            component.updateRegionSize(ui.element.data('region'), size);
                        } else {
                            item = component.findItem(ui.element.attr('id'));
                            item.size = size;
                            component.updateItem(item.id, item);
                        }
                    }
                });
            });
        },

        /**
         * Update json data to match visual sort order
         */
        updateAfterDD: function () {
            var self = this,
                newJsonValue = {};

            _.each(this.getRegions(), function (region) {
                newJsonValue[region.code] = {
                    size: region.size(),
                    rows: []
                };

                var rowIndex = 0;
                $('.region-' + region.code + ' .navpro-row', '#' + self.uid).each(function () {
                    var items = $('.navpro-entity', this);

                    if (!items.length) {
                        return;
                    }

                    newJsonValue[region.code].rows[rowIndex] = [];
                    items.each(function () {
                        newJsonValue[region.code]
                            .rows[rowIndex]
                            .push(
                                self.findItem(this.id)
                            );
                    });

                    rowIndex++;
                });
            });

            this.setJsonValue(newJsonValue);
        },

        //
        // Item edit/create methods
        //
        /**
         * Show modal popup with the form for a new item
         */
        newItem: function () {
            this.showAddContentModal();

            this.getContentModalElements().map(function (el) {
                el.reset();

                if (el.index === 'id') {
                    el.value(utils.uniqueid());
                }

                if (el.pageBuilder) {
                    el.initialValue = '';
                    el.isComponentInitialized(false);
                    el.initPageBuilder();
                }
            });
        },

        /**
         * @param {Object} item
         */
        editItem: function (item) {
            this.showAddContentModal();

            this.getContentModalElements().map(function (el) {
                if (typeof item[el.index] !== 'undefined') {
                    el.value(item[el.index]);

                    if (el.pageBuilder) {
                        el.initialValue = el.value();
                        el.isComponentInitialized(false);
                        el.initPageBuilder();
                    }
                }
            });
        },

        /**
         * @param {Object} item
         */
        deleteItem: function (item) {
            var self = this;

            confirm({
                content: 'Are you sure you want to delete this item?',
                actions: {
                    /**
                     * Confirm callback
                     */
                    confirm: function () {
                        self.updateItem(item.id, false);
                    }
                }
            });
        },

        //
        // Content Modal methods
        //
        /**
         * @return {Object}
         */
        getAddContentModal: function () {
            return registry.get(this.addContentModalName);
        },

        /**
         * Open modal popup
         */
        showAddContentModal: function () {
            var modal = this.getAddContentModal();
            modal.actionDone = this.saveItem.bind(this);
            modal.openModal();
        },

        /**
         * Hide modal popup
         */
        hideAddContentModal: function () {
            this.getAddContentModal().closeModal();
        },

        /**
         * @returns {bool}
         */
        validateAddContentModal: function () {
            var modal = this.getAddContentModal();

            modal.valid = true;
            modal.validate(registry.get(this.addContentFieldName));

            return modal.valid;
        },

        /**
         * Collect all inputs from addContentModal popup
         *
         * @return {Array}
         */
        getContentModalElements: function () {
            var elements = [];

            /**
             * @param {Object} parentEl
             */
            function collect(parentEl) {
                if (parentEl.elems) {
                    _.each(parentEl.elems(), function (parentEl) {
                        collect(parentEl);
                    });
                } else if (parentEl.reset) {
                    elements.push(parentEl);
                }
            }

            registry.get(
                this.addContentFieldName,
                collect
            );

            return elements;
        },

        /**
         * doneAction for the addContentModal popup
         */
        saveItem: function () {
            var currentValue,
                item = {};

            if (!this.validateAddContentModal()) {
                return;
            }

            $('body').trigger('processStart');

            // See save method in module-page-builder/view/adminhtml/web/js/form/form-mixin.js
            $.when.apply(
                null,
                this.getContentModalElements().map(function (el) {
                    if (el.pageBuilder) {
                        return el.pageBuilder.stage.renderingLocks.at(-1);
                    }
                    return true;
                })
            ).then(function () {
                $('body').trigger('processStop');

                this.getContentModalElements().map(function (el) {
                    item[el.index] = el.value();
                });

                if (!this.updateItem(item.id, item)) {
                    currentValue = this.getJsonValue();
                    currentValue.center.rows[0].push(item);
                    this.setJsonValue(currentValue);
                }

                this.hideAddContentModal();
            }.bind(this));
        },

        /**
         * @param  {Object} item
         * @return {String}
         */
        renderItemContents: function (item) {
            var provider = registry.get(this.provider),
                parentId = this.uid;

            require([
                'Swissup_Navigationpro/js/components/dropdown-layout/' + item.type
            ], function (template) {
                var renderer = template(item, provider),
                    scope = '#' + parentId + ' #' + item.id,
                    el = $('.item-contents', scope);

                el.html(renderer.render());

                renderer.afterRender(el);
            });

            return 'Rendering...';
        }
    });
});
