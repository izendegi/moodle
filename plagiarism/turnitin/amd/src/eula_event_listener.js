/**
 * Javascript controller for attach eula event listener.
 *
 * @copyright Turnitin
 * @author 2024 Andrii Ilin <ailin@turnitin.com>
 * @module plagiarism_turnitin/eula_event_listener
 */

define(['jquery'], function($) {
    /**
     * Handles the message event.
     *
     * @param {Event} ev - The event object.
     */
    function handleMessage(ev) {
        var message = typeof ev.data === 'undefined' ? ev.originalEvent.data : ev.data;

        // Only make ajax request if message is one of the expected responses.
        if (message === 'turnitin_eula_declined' || message === 'turnitin_eula_accepted') {
            $.ajax({
                type: "POST",
                url: M.cfg.wwwroot + "/plagiarism/turnitin/ajax.php",
                dataType: "json",
                data: {
                    action: "actionuseragreement",
                    message: message,
                    sesskey: M.cfg.sesskey
                },
                success: function() {
                    window.location.reload();
                },
                error: function() {
                    window.location.reload();
                }
            });
        }
    }

    /**
     * Attaches the event listener for the message event.
     */
    function attachEventListener() {
        $(window).on("message", handleMessage);
    }

    return {
        attach: attachEventListener
    };
});