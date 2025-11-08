define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/ui-select',
    'uiRegistry',
    'Swissup_Navigationpro/lib/dragula/dragula'
], function ($, _, ko, Select, registry, dragula) {
    'use strict';

    /**
     * Processing options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Array} created - list to add new options
     *
     * @return {Array} Plain options list
     */
    function flattenCollection(array, separator, created) {
        var i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    /**
     * Set levels to options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Number} level - Starting level
     * @param {String} path - path to root
     *
     * @returns {Array} Array with levels
     */
    function setProperty(array, separator, level, path) {
        var i = 0,
            length,
            nextLevel,
            nextPath;

        array = _.compact(array);
        length = array.length;
        level = level || 0;
        path = path || '';

        for (i; i < length; i++) {
            if (array[i]) {
                _.extend(array[i], {
                    level: level,
                    path: path
                });
            }

            if (array[i].hasOwnProperty(separator)) {
                nextLevel = level + 1;
                nextPath = path ? path + '.' + array[i].label : array[i].label;
                setProperty.call(this, array[i][separator], separator, nextLevel, nextPath);
            }
        }

        return array;
    }

    /**
     * Preprocessing options list
     *
     * @param {Array} nodes - Options list
     *
     * @return {Object} Object with property - options(options list)
     *      and cache options with plain and tree list
     */
    function parseOptions(nodes) {
        var caption,
            value,
            cacheNodes,
            copyNodes;

        nodes = setProperty(nodes, 'optgroup');
        copyNodes = JSON.parse(JSON.stringify(nodes));
        cacheNodes = flattenCollection(copyNodes, 'optgroup');

        nodes = _.map(nodes, function (node) {
            value = node.value;

            if (value == null || value === '') {
                if (_.isUndefined(caption)) {
                    caption = node.label;
                }
            } else {
                return node;
            }
        });

        return {
            options: _.compact(nodes),
            cacheOptions: {
                plain: _.compact(cacheNodes),
                tree: _.compact(nodes)
            }
        };
    }

    return Select.extend({

        initialize: function() {
            this._super();

            var debouncedEnableDD = _.debounce(this.enableDD.bind(this), 500);
            $.async(
                '.admin__action-multiselect-menu-inner-item',
                this,
                debouncedEnableDD
            );

            return this;
        },

        initObservable: function () {
            this._super();

            // make the tree always visible
            this.listVisible = function() {
                return true;
            }

            // Highlight active item
            this.value(registry.get(this.provider).data.item_id);

            return this;
        },

        enableDD: function() {
            var component = this;

            if (component.drake) {
                component.drake.destroy();
            }

            // Prepare container for mirror elements
            if (!$('.navpro__mirror-container').length) {
                $(document.body).append(
                    '<div class="navpro__field-tree admin__action-multiselect-tree">' +
                        '<ul class="navpro__mirror-container admin__action-multiselect-menu-inner root"></ul>' +
                    '</div>'
                );
            }

            // Prepare containers for nesting inside leaf items
            $('.admin__action-multiselect-menu-inner-item:not(._parent)')
                .addClass('_parent')
                .append('<ul class="navpro__menu-dummy admin__action-multiselect-menu-inner"></ul>');

            // Initialize D&D
            component.drake = dragula(
                $('.admin__action-multiselect-menu-inner:not(._root)', '#' + this.uid).toArray(),
                {
                    mirrorContainer: $('.navpro__mirror-container').get(0),
                    revertOnSpill: true
                }
            );

            // Styling purpose
            component.drake.on('drag', function(el, source) {
                $('.admin__action-multiselect-menu-inner._root').addClass('navpro__menu-drag');
            });
            component.drake.on('dragend', function(el) {
                $('.admin__action-multiselect-menu-inner._root').removeClass('navpro__menu-drag');
            });

            // Save tree
            component.drake.on('drop', function(el, target, source, sibling) {
                var itemId = ko.dataFor(el).value,
                    menuId = ko.dataFor(el).menu_id,
                    li = $(target).closest('li')[0],
                    targetId = ko.dataFor(li).current ? ko.dataFor(li).current.value : ko.dataFor(li).value,
                    siblingId = sibling ? ko.dataFor(sibling).value : null;

                component
                    .moveItem(itemId, targetId, siblingId, menuId)
                    .done(component.updateTree.bind(component))
                    .done(function(response) {
                        var provider = registry.get(component.provider);
                        if (!provider.data.item_id) {
                            return;
                        }

                        // update currently opened item data to sync it with remote data
                        var item = component.getItemById(provider.data.item_id);
                        provider.set('data.parent_id', item.parent_id);
                        provider.set('data.path', item.db_path);
                        provider.set('data.position', item.position);
                        provider.set('data.level', item.db_level);
                    });
            });
        },

        /**
         * Find item dy its ID
         * @param  {Number} id
         * @return {Object}
         */
        getItemById: function(id) {
            return _.find(this.cacheOptions.plain, function(item) {
                return item.value == id;
            });
        },

        /**
         * Expand tree to the selected item
         * @param  {Object} data - element data
         * @return {Boolean} level visibility.
         */
        showLevels: function (data) {
            // do additional logic on initial load only (visible is undefined)
            var canExpandTree = !data.visible,
                isVisible = this._super(data);
            if (isVisible || !canExpandTree) {
                return isVisible;
            }

            var selected = registry.get(this.provider).data,
                pathPart = '/' + data.value + '/';
            if (selected.path && selected.path.indexOf(pathPart) > 0) {
                data.visible(true);
                data.isVisited = true;
            }

            return data.visible();
        },

        /**
         * Disable hovered state for better active element highlighting
         * @param  {Element} element
         * @return {void}
         */
        _hoverTo: function(element) {
            return;
        },

        toggleOptionSelected: function(data) {
            // prevent click on root item
            if (!parseInt(data.value)) {
                return;
            }

            this._super(data);

            this.switchItem(this.value());
        },

        /**
         * Get selected element labels
         *
         * @returns {Array} array labels
         */
        getOpened: function (options, opened) {
            options = options || this.options();
            opened  = opened || [];

            options.forEach(function(opt) {
                if (opt.visible && opt.visible()) {
                    opened.push(opt);
                }
                if (opt[this.separator]) {
                    this.getOpened(opt[this.separator], opened);
                }
            }, this);

            return opened;
        },

        /**
         * Recursively search for itemId in options and mark it as visible
         *
         * @param  {Numeric} itemId
         * @param  {Array} items
         * @return {void}
         */
        expandItem: function(itemId, items) {
            items = items || this.options();

            items.forEach(function(opt) {
                if (!opt.visible) {
                    opt.visible = ko.observable(false);
                }

                if (opt.value == itemId && opt[this.separator]) {
                    opt.visible(true);
                    opt.isVisited = true;
                } else if (opt[this.separator]) {
                    this.expandItem(itemId, opt[this.separator]);
                } else {
                    // close leaf item that was a parent previously
                    opt.visible(false);
                }
            }, this);
        },

        /**
         * Update tree with new items
         *
         * @param {Object} data - Response data object.
         * @returns {void}
         */
        updateTree: function (data) {
            var opened = this.getOpened();

            var result = parseOptions(data.items);

            this.options([]);
            this.options(result.options);
            this.cacheOptions = result.cacheOptions;

            opened.forEach(function(opt) {
                this.expandItem(opt.value);
            }, this);
        },

        switchItem: function(itemId) {
            var url = window.location.href;

            url = url.replace(/\/item_id\/\d+/, '');
            url = url.replace('/menu_id/', '/item_id/' + itemId + '/menu_id/');

            setLocation(url);
        },

        /**
         * Move item into target before sibling
         *
         * @param  {Number} itemId
         * @param  {Number} targetId
         * @param  {Number|null} siblingId
         * @param  {Number} menuId
         * @return {Deferred}
         */
        moveItem: function(itemId, targetId, siblingId, menuId) {
            var save = $.Deferred();

            var data = {
                form_key: window.FORM_KEY,
                item_id: itemId,
                target_id: targetId,
                sibling_id: siblingId,
                menu_id: menuId,
            };

            $('body').trigger('processStart');

            $.ajax({
                url: this.move_url,
                data: data,
                dataType: 'json',
                error: function(response) {
                    // @todo show error message by status code
                    window.location.reload();
                },
                success: function (response) {
                    if (response.ajaxExpired) {
                        window.location.href = response.ajaxRedirect;
                    }

                    save.resolve(response);

                    $('body').notification('clear');
                    $.each(response.messages, function (key, message) {
                        $('body').notification('add', {
                            error: response.error,
                            message: message,
                            insertMethod: function (msg) {
                                $('.page-main-actions').after(msg);
                            }
                        });
                    });
                },
                complete: function () {
                    $('body').trigger('processStop');
                }
            });

            return save.promise();
        }
    });
});
