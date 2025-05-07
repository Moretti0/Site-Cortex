define([
    'jquery',
    'mage/adminhtml/events',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (config, element) {
        const container = $(element);
        const builder = $('<div class="json-layout-builder"/>');
        const sectionName = $('<input type="text" placeholder="Nome da seção" class="input-text" style="width: 200px;"/>');
        const attributeList = $('<input type="text" placeholder="Atributos (separados por vírgula)" class="input-text" style="width: 400px;"/>');
        const addButton = $('<button type="button" class="action-primary"><span>Adicionar Seção</span></button>');
        const outputField = $('#json_config');
        const sectionList = $('<ul class="section-list" style="margin-top:20px;"/>');

        container.append(sectionName, attributeList, addButton, sectionList);

        const layout = [];

        addButton.on('click', function () {
            const name = sectionName.val().trim();
            const attrs = attributeList.val().split(',').map(s => s.trim()).filter(Boolean);
            if (!name || attrs.length === 0) {
                alert('Preencha nome da seção e pelo menos um atributo.');
                return;
            }

            layout.push({ label: name, attributes: attrs });
            const li = $('<li/>').text(name + ': ' + attrs.join(', '));
            sectionList.append(li);

            // Atualiza campo JSON oculto
            const result = {
                title: "Generated Template",
                layout: "default",
                sections: layout
            };
            outputField.val(JSON.stringify(result, null, 2));
        });
    };
});
