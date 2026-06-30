define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/modal',
    'core/str',
], function ($, Ajax, Notification, Modal, str) {
    return {
        init: function (config) {
            const typeformid = config.typeformid;
            const cmid = config.cmid;
            const typeformjslink = config.typeformjslink;
            const typeformdomain = config.typeformdomain;
            const studentCode = config.studentCode;
            const includeStudentCode = config.includestudentcode;

            if (config.alreadycompleted) {
                return;
            }

            /**
             * Crea la modal usando la clase nativa Modal
             */
            async function showTypeformModal()
            {
                let title = await str.get_string('activitymodaltitle', 'mod_typeform');
                const modal = await Modal.create({
                    title: title,
                    body: '<div id="tf-container" style="height: 600px; width: 100%;"></div>',
                    footer: '',
                    large: true,
                    removeOnClose: true,
                    show: true,
                });

                modal.show();

                const container = modal.getRoot().find('#tf-container')[0];
                loadTypeformEngine(container);
            }
            /**
             * loadTypeformEngine
             * @param {HTMLElement} container The DOM element where the widget will be loaded.
             */
            function loadTypeformEngine(container)
            {
                if (typeof window.tf === 'undefined') {
                    const script = document.createElement('script');
                    script.src = typeformjslink;
                    script.async = true;
                    script.onload = () => initTypeform(container);
                    document.head.appendChild(script);
                } else {
                    initTypeform(container);
                }
            }

            /**
             * initTypeform
             * @param {HTMLElement} container The DOM element where the widget will be loaded.
             */
            function initTypeform(container)
            {
                if (window.tf && window.tf.createWidget) {
                    // The student_code value is always anonymised upstream; only include it when configured to.
                    const hidden = {};
                    if (includeStudentCode) {
                        hidden.student_code = studentCode;
                    }
                    window.tf.createWidget(typeformid, {
                        domain: typeformdomain,
                        container: container,
                        hideHeaders: true,
                        hideFooter: true,
                        hidden: hidden,
                        onStarted: () => handleStartedEvent(),
                        onSubmit: () => handleCompletion()
                    });
                }
            }

            /**
             * handleCompletion
             */
            function handleCompletion()
            {
                Ajax.call([{
                    methodname: 'mod_typeform_mark_complete',
                    args: { cmid: cmid }
                }])[0].then(response => {
                    if (response.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                }).catch(Notification.exception);
            }

            /**
             * handleStartedEvent
             */
            function handleStartedEvent()
            {
                Ajax.call([{
                    methodname: 'mod_typeform_add_event',
                    args: { eventname: 'attempt_started', cmid: cmid }
                }])[0].catch(Notification.exception);
            }

            // Ejecutar la apertura
            showTypeformModal();
        }
    };
});