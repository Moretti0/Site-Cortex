define([
    'jquery',
    'mage/adminhtml/events'
], function ($) {
    'use strict';

    return function (config, element) {
        const textarea = $(element);
        const validateButton = $('<button type="button" class="action-default scalable"><span>Validar JSON</span></button>');
        textarea.after(validateButton);

        validateButton.on('click', function () {
            try {
                JSON.parse(textarea.val());
                alert('JSON válido!');
            } catch (e) {
                alert('Erro de sintaxe no JSON: ' + e.message);
            }
        });
    };
});
