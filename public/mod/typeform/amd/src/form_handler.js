define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    return {
        init: function(typeformid, workspaceid) {
            let workspaceSelect = $('#' + workspaceid);
            let formSelect = $('#' + typeformid);
            let titleHidden = $('input[name="typeformtitle"]');
            let typeformidHidden = $('input[name="typeformid"]');
            formSelect.on('change', function() {
                let selectedValue = $(this).val();
                $(this).find('option').removeAttr('selected');
                $(this).find('option[value="' + selectedValue + '"]').attr('selected', 'selected');
                $('#' + typeformid).val(selectedValue);
                let selectedText = $(this).find('option:selected').text();
                titleHidden.val(selectedText);
                typeformidHidden.val(selectedValue);
            });
            workspaceSelect.on('change', function() {
                let workspacevalue = $(this).val();

                // Limpiar el segundo select
                formSelect.empty().append('<option value="0">Cargando...</option>');

                if (workspacevalue == 0) {
                    formSelect.empty().append('<option value="0">Esperando categoría...</option>');
                    return;
                }

                Ajax.call([{
                    methodname: 'mod_typeform_get_workspace_forms',
                    args: { id: workspacevalue }
                }])[0].then(response => {
                    if (response.success) {
                        formSelect.empty();
                        formSelect.append('<option value="0">Seleccione un producto...</option>');
                        $.each(response.list, function(key, form) {
                            let option = $('<option></option>')
                                .attr('value', form.id)
                                .text(form.name);
                            formSelect.append(option);
                        });
                    }
                }).catch(Notification.exception);
            });
        }
    };
});
