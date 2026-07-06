const textacwaiting = document.getElementsByName('langacwaiting')[0].value;
const textacaccess = document.getElementsByName('langacaccess')[0].value;
const textacnotaccess = document.getElementsByName('langacnotaccess')[0].value;

const preventSubmit = (event) => {
    console.log('prevent submit');
    event.preventDefault();
};
// Get submit button
const moodleStartQuizButtons = document.querySelector('.quizstartbuttondiv button[type="submit"]');

// Disable submit buttons
moodleStartQuizButtons.disabled = true;
moodleStartQuizButtons.addEventListener("click", preventSubmit);

// Prepend alert block to quiz button
moodleStartQuizButtons.insertAdjacentHTML('beforebegin', getAlertBlock(textacwaiting));

function escapeHTML(string) {
    const temporalElement = document.createElement('p');
    temporalElement.textContent = string;

    return temporalElement.innerHTML;
}

function getAlertBlock(text) {
    const escapedText = escapeHTML(text);
    return '<div class="alert alert-warning alert-block smowl_status_alert"> ' + escapedText + '</div>';
}

listenCornerStatus();

function listenCornerStatus() {
    var eventMethod = window.addEventListener
        ? "addEventListener"
        : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod === "attachEvent"
        ? "onmessage"
        : "message";

    eventer(messageEvent, function (e) {
        if (e.data === "monitoringstatusOK" || e.message === "monitoringstatusOK") {
            const alerts = document.getElementsByClassName('smowl_status_alert');
            for (const element of alerts) {
                element.innerHTML = textacaccess;
                element.className = 'alert alert-success alert-block smowl_status_alert';
            }
            moodleStartQuizButtons.disabled = false;
            moodleStartQuizButtons.removeEventListener("click", preventSubmit);
        }
        if (e.data === "monitoringstatusNOTOK" || e.message === "monitoringstatusNOTOK") {
            const alerts = document.getElementsByClassName('smowl_status_alert');
            for (const element of alerts) {
                element.innerHTML = textacnotaccess;
                element.className = 'alert alert-danger alert-block smowl_status_alert';
            }
            moodleStartQuizButtons.disabled = true;
            moodleStartQuizButtons.addEventListener("click", preventSubmit);
        }
    });
}
