define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.breadcrumbs', widget, {
            /**
             * Overridden for easycatalogimages support
             *
             * @return {Object|null}
             * @private
             */
            _resolveCategoryMenuItem: function () {
                var categoryMenuItem = this._super();

                if (!categoryMenuItem || !categoryMenuItem.length) {
                    // easycatalogimages instead of navpro in dropdown
                    categoryMenuItem = $(this.options.menuContainer).find(
                        '.category-name > ' +
                        'a[href="' + this._resolveCategoryUrl() + '"]'
                    );
                }

                return categoryMenuItem.first();
            },

            /**
             * Overridden to skip non-category links
             *
             * @param {Object} menuItem
             * @return {Object|null}
             * @private
             */
            _getParentMenuItem: function (menuItem) {
                var parentMenuItem = this._super(menuItem);

                if (!parentMenuItem) {
                    return null;
                }

                if (!parentMenuItem.parent('li').hasClass('category-item')) {
                    return this._getParentMenuItem(parentMenuItem);
                }

                return parentMenuItem;
            }
        });
    };
});
