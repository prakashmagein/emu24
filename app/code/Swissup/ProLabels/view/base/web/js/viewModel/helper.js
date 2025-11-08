'use strict';

const _getVarType = (varName) => {
    var directive, code, type;

    [directive, code, type] = varName.replaceAll('#', '').split(':');
    if (directive !== 'attr') {
        type = code;
    }

    return type;
};

/**
 * [countDecimals description]
 * https://stackoverflow.com/a/17369245
 *
 * @param  {Float} value
 * @return {Number}
 */
function _countDecimals(value) {
    var parts = value.toString().split('.');

    return parts.length < 2 ? 0 : parts[1].length;
}

/**
 * Round value using roundValue and roundMethod
 *
 * @param  {float} value
 * @param  {int} roundValue
 * @param  {String} roundMethod
 * @return {float}
 */
function _roundNumber(value, roundValue, roundMethod) {
    var newValue;

    roundValue = roundValue || 1;
    newValue = Math[roundMethod](value / roundValue) * roundValue;

    return newValue.toFixed(_countDecimals(roundValue));
}

/**
 * Process label text
 *
 * @return {String}
 */
function processText(text, variables, roundValue, roundMethod) {
    var processedText = text || '';

    Object.entries(variables)
        .filter(([variable, value]) => processedText.indexOf(variable) > -1)
        .forEach(([variable, value])=> {
            value = value === null ? '' : value;
            value = (
                    _getVarType(variable) === 'string'
                    || isNaN(value)
                    || value === ''
                )
                ? value
                : _roundNumber(value, roundValue || 1, roundMethod || 'round');
            processedText = processedText.replaceAll(new RegExp(variable, 'g'), value);
        });

    return processedText;
}

export {
    processText
}
