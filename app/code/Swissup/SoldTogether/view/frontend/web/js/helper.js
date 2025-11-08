'use strict';

function uniq(arr) {
    // Simple uniq implementation (replace with lodash if needed)
    return Array.from(new Set(arr));
}

let relatedSuperInput = null;
let promotedInput = null;

const helper = {
    /**
     * @return {Array}
     */
    getRelatedIds: function () {
        const related = document.getElementById('related-products-field');
        return related && related.value ? related.value.split(',') : [];
    },

    /**
     * @param {Array} ids
     */
    setRelatedIds: function (ids) {
        const related = document.getElementById('related-products-field');
        if (related) {
            related.value = uniq(ids).join(',');
        }
    },

    /**
     * @return {Object}
     */
    getRelatedSupers: function () {
        const related = document.getElementById('related-products-field');
        if (!relatedSuperInput && related) {
            relatedSuperInput = document.createElement('input');
            relatedSuperInput.type = 'hidden';
            relatedSuperInput.name = 'related_product_super_attribute';
            related.parentNode.insertBefore(relatedSuperInput, related.nextSibling);
        }
        return relatedSuperInput && relatedSuperInput.value
            ? JSON.parse(relatedSuperInput.value)
            : {};
    },

    /**
     * @param {Object} superAttribute
     */
    setRelatedSupers: function (superAttribute) {
        if (relatedSuperInput) {
            relatedSuperInput.value = JSON.stringify(superAttribute);
        }
    },

    getPromotedFlag: function () {
        const related = document.getElementById('related-products-field');
        if (!promotedInput && related) {
            promotedInput = document.createElement('input');
            promotedInput.type = 'hidden';
            promotedInput.name = 'soldtogether_promoted';
            related.parentNode.insertBefore(promotedInput, related.nextSibling);
        }
        return promotedInput ? promotedInput.value : '';
    },

    setPromotedFlag: function (flag) {
        if (promotedInput) {
            promotedInput.value = flag;
        }
    },

    /**
     * Get attribute Id from product option element
     *
     * @param  {HTMLElement} optionEl
     * @return {String}
     */
    getAttributeId: function (optionEl) {
        return optionEl.getAttribute('data-attribute-id') || optionEl.getAttribute('attribute-id');
    },

    /**
     * Get selected option from product option element
     *
     * @param  {HTMLElement} optionEl
     * @return {String}
     */
    getOptionSelected: function (optionEl) {
        return optionEl.getAttribute('data-option-selected') || optionEl.getAttribute('option-selected');
    },

    /**
     * Find product options for element
     *
     * @param  {HTMLElement} el
     * @return {Array<HTMLElement>}
     */
    findProductOptions: function (el) {
        return Array.from(el.querySelectorAll('.swatch-attribute, .field.configurable'));
    },

    toggleElementHidden(toggler) {
        const data = toggler.dataset;
        const target = data.target && document.getElementById(data.target);

        if (target) {
            target.hidden = !target.hidden;

            if (!target.hidden && data.hide) toggler.innerText = data.hide;
            if (target.hidden && data.show) toggler.innerText = data.show;
            return target;
        }
    },

    setRelatedData(submitIds, submitSuperAttribute, promotedFlag) {
        this.setRelatedIds(submitIds);
        this.setRelatedSupers(submitSuperAttribute);
        this.setPromotedFlag(promotedFlag);
    },

    getValuesToRestore() {
        return {
            ids: this.getRelatedIds(),
            superAttribute: this.getRelatedSupers(),
            promotedFlag: this.getPromotedFlag()
        }
    },

    restoreValues(restoreObject) {
        this.setRelatedIds(restoreObject.ids);
        this.setRelatedSupers(restoreObject.superAttribute);
        this.setPromotedFlag(restoreObject.promotedFlag);
    }
};

export {
    helper
};
