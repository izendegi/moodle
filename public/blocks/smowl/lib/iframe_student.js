document.addEventListener("click", function (event) {
    window.frames.smowliframe.postMessage("allowcamera", "*");
});
document.addEventListener("keydown", function (event) {
    window.frames.smowliframe.postMessage("allowcamera", "*");
});

toSend = true;
document.addEventListener("click", sendTitlePost);
document.addEventListener("keydown", sendTitlePost);
window.addEventListener("message", stopSending);

function sendTitlePost(event) {
    if (toSend) {
        window.frames.smowliframe.postMessage("gettitle:" + document.title, "*");
    }
}

function stopSending(event) {
    if (event.data == "titleStored") {
        toSend = false;
    }
}

setTimeout(setNone, 2500);
function setNone() {
    var elements = document.getElementsByClassName("block-hider-hide");
    for (var i = 0, l = elements.length; i < l; i++) {
        if (elements[i].title.match("SMOWL")) {
            elements[i].style.display = "none";
        }
    }
    var elements = document.getElementsByClassName("block-hider-show");
    for (var i = 0, l = elements.length; i < l; i++) {
        if (elements[i].title.match("SMOWL")) {
            elements[i].style.display = "none";
        }
    }
    var elements = document.getElementsByClassName("moveto customcommand requiresjs");
    for (var i = 0, l = elements.length; i < l; i++) {
        if (elements[i].title.match("SMOWL")) {
            elements[i].style.display = "none";
        }
    }
}