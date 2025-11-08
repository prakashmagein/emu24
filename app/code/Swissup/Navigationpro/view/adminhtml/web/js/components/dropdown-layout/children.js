define([
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/template',
    'mage/translate',
    'text!Swissup_Navigationpro/template/dropdown-layout/children.html'
], function ($, _, registry, mageTemplate, $t, template) {
    'use strict';

    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
    return function (item, provider) {
        var treeData;

        /**
         * @return {Number}
         */
        function getLevelsPerDropdown() {
            if (!item.levels_per_dropdown || item.levels_per_dropdown == 0) { // eslint-disable-line eqeqeq
                return 1;
            }

            return item.levels_per_dropdown;
        }

        /**
         * @return {String}
         */
        function getChildrenSortOrder() {
            return item.children_sort_order || '';
        }

        /**
         * @return {Number}
         */
        function getMaxChildrenCount() {
            if (!item.max_children_count || item.max_children_count == 0) { // eslint-disable-line eqeqeq
                return 1000;
            }

            return item.max_children_count;
        }

        /**
         * @return {Object}
         */
        function generateTreeRoot() {
            return {
                value: undefined,
                level: 0,
                db_path: '0',
                label: 'Menu Items'
            };
        }

        /**
         * @return {Array}
         */
        function generateTreeData() {
            var data = [generateTreeRoot()],
                rules = {
                    level1: {
                        limit: 8
                    },
                    level2: {
                        limit: 3
                    },
                    level3: {
                        limit: 2
                    },
                    level4: {
                        limit: 1
                    },
                    level5: {
                        limit: 1
                    }
                };

            if (item.columns_count > 4) {
                rules.level1.limit = item.columns_count * 2;
            }

            /**
             * @param {String} parentPath
             */
            function addChildren(parentPath) {
                var level = parentPath ? parentPath.split('/').length : 1,
                    rule = rules['level' + level],
                    i = 0,
                    newPath;

                if (!rule || !rule.limit) {
                    return;
                }

                while (rule.limit > i) {
                    newPath = parentPath + '/' + i;

                    data.push({
                        value: newPath,
                        level: level,
                        db_path: newPath,
                        label: 'Item-' + (i + 1)
                    });

                    i++;

                    addChildren(newPath);
                }
            }
            addChildren(0);

            return data;
        }

        /**
         * @return {Array}
         */
        function getTreeData() {
            var tree = registry.get('navigationpro_menu_item.navigationpro_menu_item.aside.tree');

            if (treeData) {
                return treeData;
            }

            if (provider.data.name && tree.cacheOptions) {
                return tree.cacheOptions.plain;
            }

            return [];
        }

        /**
         * @param  {Object} currentItem
         * @return {Array}
         */
        function getChildren(currentItem) {
            var children = [],
                flatOptions = getTreeData();

            currentItem = currentItem || _.find(flatOptions, function (el) {
                return el.value == provider.data.item_id; // eslint-disable-line eqeqeq
            });

            if (currentItem) {
                children = _.filter(flatOptions, function (el) {
                    return el.db_path &&
                        el.db_path.indexOf(currentItem.db_path + '/') === 0 &&
                        el.level == currentItem.level + 1; // eslint-disable-line eqeqeq
                });
            }

            return children;
        }

        /**
         * Recursively render tree beginning from the currentItem
         *
         * @param  {Object} currentItem
         * @return {String}
         */
        function renderTree(currentItem, currentRelativeLevel) {
            var children = getChildren(currentItem),
                html = '';

            currentRelativeLevel = currentRelativeLevel || 1;

            if (!children.length && currentRelativeLevel === 1) {
                treeData = generateTreeData();
                children = getChildren(generateTreeRoot());
            }

            if (getChildrenSortOrder() === 'alpha') {
                children.sort((a, b) => a.label.localeCompare(b.label, 'en'));
            }

            _.find(children, function (el, i) {
                if (getMaxChildrenCount() <= i && currentRelativeLevel === 1) {
                    html += '<div><span class="navpro-all"><strong>' +
                        $t('Shop All') +
                        '</strong></span></div>';

                    return true;
                }

                html += '<div><span>' + el.label + '</span>';

                if (getLevelsPerDropdown() > currentRelativeLevel) {
                    html += renderTree(el, currentRelativeLevel + 1);
                }

                html += '</div>';
            });

            return html;
        }

        return {
            /**
             * @return {String}
             */
            render: function () {
                return mageTemplate(template, {
                    $: $,
                    item: item,
                    render: renderTree,
                    getLevelsPerDropdown: getLevelsPerDropdown
                });
            },

            /**
             * @param {jQuery} container
             */
            afterRender: function (container) {
                //
            }
        };
    };
    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
});
