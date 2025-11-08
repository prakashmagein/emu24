/* global $H */
define([
    'jquery',
    'jquery/ui',
    'mage/adminhtml/grid',
    'mage/translate'
], function ($) {
    'use strict';

    const packedMap = {
        'st_weight': 'w',
        'promo_rule': 'r',
        'promo_value': 'v'
    };

    /**
     * Collect data from table row
     *
     * @param  {jQuery} $row
     * @return {Object}
     */
    function _collectData($row) {
        var data = {};

        $row.find('.editable').each((i, el) => {
            var $control = $(el).find('[name]')
                name = $control.attr('name');

            if ($control.val() && packedMap[name]) {
                data[packedMap[name]] = $control.val();
            }
        });

        return data;
    }

    /**
     * Find (massaction) checkbox in table row
     *
     * @param  {jQuery} $row
     * @return {jQuery}
     */
    function _findCheckbox($row) {
        return $row.find('.col-massaction .checkbox');
    }


    return function (config) {
        var selectedProducts = config.selectedProducts,
            linkedProducts = $H(selectedProducts),
            gridJsObject = window[config.gridJsObjectName],
            $storageInput = $('#' + config.inputId),
            tabIndex = 1000;

        $storageInput.val(Object.toJSON(linkedProducts));

        function getItemColumnValue(itemId, columnName) {
            var item = linkedProducts.get(itemId),
                packedColumnName = packedMap[columnName] || '';

            return item && item[packedColumnName];
        }

        /**
         * Register Category Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerProduct(grid, element, checked) {
            var $controls = $(element).closest('tr').find('.input-text, select'),
                $row = $(element).closest('tr');

            if (checked) {
                $controls.prop('disabled', false);
                $row.addClass('selected');
            } else {
                $controls.prop('disabled', true);
                $row.removeClass('selected');
            }

            if ($row.find('[name="promo_rule"]').val()) {
                $row.addClass('promoted');
            } else {
                $row.removeClass('promoted');
            }

            linkedDataUpdate($(element));
            grid.reloadParams = {
                'selected_products[]': linkedProducts.keys()
            };
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function productRowClick(grid, event) {
            var $row = $(event.currentTarget),
                checkbox = _findCheckbox($row).get(0);

            gridJsObject.setCheckboxChecked(checkbox, checkbox.checked);

            if (!$(event.target).hasClass('handle')
                && $(event.target).closest('.col-thumbnail').length
                && $row.hasClass('selected')
                && $row.find('[name="promo_rule"]').length
            ) {
                showPromotePopup(event);
            }
        }

        /**
         * Change product position
         *
         * @param {jQuery} checkbox
         */
        function linkedDataUpdate($checkbox) {
            var $row = $checkbox.closest('tr');

            if ($checkbox.get(0)?.checked) {
                linkedProducts.set(
                    $checkbox.val(),
                    _collectData($checkbox.closest('tr'))
                );
            } else {
                linkedProducts.unset($checkbox.val());
            }

            $storageInput.val(Object.toJSON(linkedProducts));
        }

        function updateListener(event) {
            var $row = $(event.currentTarget).closest('tr');

            if ($row.find('[name="promo_rule"]').val()) {
                $row.addClass('promoted');
            } else {
                $row.removeClass('promoted');
            }

            linkedDataUpdate(_findCheckbox($row));
        }

        function showPromotePopup(event) {
            var $table = $(event.currentTarget).closest('table'),
                $row = $(event.currentTarget).closest('tr'),
                $popup = $('.soldtogether-promote'),
                $inputs = $row.find('[name="promo_value"], [name="promo_rule"]');

            $popup.html(
                '<div class="col-thumbnail">' +
                    $row.find('.col-thumbnail').html() +
                '</div>' +
                '<div class="col-promo"></div>'
            );
            $inputs.each((i, el) => {
                var name = $(el).attr('name'),
                    $div = $('<div></div>'),
                    $control;

                $popup.find('.col-promo').append($div);
                $div.append($table.find('[data-sort="' + name + '"]').html());
                $control = $(el).clone();
                $control.data('elementToUpdate', el);
                $control.val($(el).val());
                $div.append($control);
            });

            $popup.trigger('openModal');

            $popup.closest('.modal-inner-wrap').find('.modal-footer button').each((i, el) => {
                if (!$(el).data('actionAssigned')) {
                    $(el).on('click', (event) => {
                        $popup.find('[name="promo_value"], [name="promo_rule"]').each((i, el) => {
                            var elementToUpdate = $(el).data('elementToUpdate');

                            $(elementToUpdate).val($(el).val());
                            $(elementToUpdate).trigger('change');
                            $(elementToUpdate).trigger('keyup');
                        })
                    })

                    $(el).data('actionAssigned', true);
                }
            });
        }

        /**
         * Initialize category product row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function productRowInit(grid, row) {
            var checkbox = row.querySelector('.checkbox'),
                editableControls = row.querySelectorAll('.editable select, .editable input'),
                weight = row.querySelector('[name="st_weight"]');

            if (checkbox && editableControls) {
                editableControls.forEach((el) => {
                    checkbox.weightElement = el;
                    el.checkboxElement = checkbox;
                    el.disabled = !checkbox.checked;
                    el.tabIndex = tabIndex++;
                    el.value = getItemColumnValue(checkbox.value, el.name) || el.value;
                    if (el.tagName == 'SELECT') {
                        $(el).on('change', updateListener);
                    } else {
                        $(el).on('keyup', updateListener);
                    }
                });
            }

            if (checkbox && checkbox.checked) {
                $(row).addClass('selected');
            }

            if ($(row).find('[name="promo_rule"]').val()) {
                $(row).addClass('promoted');
            }

        }

        function initSortable() {
            $('#' + gridJsObject.containerId + ' tbody').sortable({
                handle: '.handle',
                update: (event) => {
                    var $weights = $(event.target).find('tr.selected [name="st_weight"]'),
                        stopFlag,
                        iteration = 0;

                    do {
                        stopFlag = true;
                        $weights.each((i, el) => {
                            var next = $weights.get(i+1),
                                currentValue,
                                nextValue;

                            if (!next) return;

                            currentValue = parseFloat(el.value);
                            currentValue = isNaN(currentValue) ? 0 : currentValue;
                            nextValue = parseFloat(next.value);
                            nextValue = isNaN(nextValue) ? 0 : nextValue;
                            if (nextValue >= currentValue) {
                                stopFlag = false;
                                el.value = nextValue + 1;
                            }
                        })
                        iteration++;
                    } while (!stopFlag && iteration < 999);
                    $weights.trigger('keyup');
                }
            });
        }

        function updateHeader() {
            var $container = $('#' + gridJsObject.containerId),
                $gridFilters = $container.find('.data-grid-filters');

            $gridFilters.find('td').each((i, el) => {
                var column = $(el).data('column');

                $(el).prepend(
                    $container.find('[data-sort="' + column + '"]').html()
                    || ('<span>' + $.mage.__('Selected') + '</span>')
                );
            });

            $gridFilters.append(
                $container.find('.admin__filter-actions button')
            );

            $container.find('.admin__filter-actions').append(
                '<button class="action-filter">' +
                    $.mage.__('Filter') +
                '</button>');
            $gridFilters.find('input, select').each((i, el) => {
                if (el.value) {
                    $container.find('.action-filter').addClass('filter-applied');
                }
            });
            $container.find('.admin__filter-actions .action-filter').on('click', () => {
                $container.find('thead').toggleClass('show-filters');
            })
        }

        function updateGrid() {
            const $container = $('#' + gridJsObject.containerId);
            const $gridFilters = $container.find('.data-grid-filters');

            $container.find('.data-grid-tr-no-data td').append(
                '<div>' +
                    '<button data-action="grid-show-all">' +
                        'Select Products' +
                    '</button>' +
                '</div>'
            );

            $container.find('[data-action="grid-show-all"]').on('click', (event) => {
                const $buttonApply = $gridFilters.find('[data-action="grid-filter-apply"]');

                event.stopPropagation();
                $gridFilters.find('[name="is_linked"]').val('');
                $gridFilters.find('[name="visibility"]').val(4);
                $buttonApply.click();
            });
        }

        gridJsObject.rowClickCallback = productRowClick;
        gridJsObject.initRowCallback = productRowInit;
        gridJsObject.checkboxCheckCallback = registerProduct;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                productRowInit(gridJsObject, row);
            });
        }

        updateHeader();
        updateGrid();
        initSortable();
        $('#' + gridJsObject.containerId).on('contentUpdated', () => {
            updateHeader();
            updateGrid();
            initSortable();
        });
    };
});
