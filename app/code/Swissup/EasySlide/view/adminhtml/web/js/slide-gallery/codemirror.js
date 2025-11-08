define([
    'jquery',
    'Swissup_Codemirror/js/form/element/editor'
], function ($, Editor) {
    'use strict';

    return function (options, textarea) {
        const $textarea = $(textarea);
        const editor = new Editor({
            dataScope: '',
            editorConfig: {
                indentUnit: 2,
                mode: 'htmlmixed',
                lineWrapping: true
            }
        });

        editor.value($textarea.val());
        editor.initEditor(textarea);
        editor.value.subscribe((newValue) => {
            $textarea.val(newValue);
            $textarea.trigger('change');
        });
    };
});
