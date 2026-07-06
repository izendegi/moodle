

define(['block_smowl/interact', 'core/log'], function (interact, log) {
    /**
     *
     */
    function makeCornerDraggable() {
        const cornerId = "block_smowl_content";
        const draggingFromId = "block_smowl_drag";
        interact(`#${cornerId}`).draggable({
            inertia: true,
            allowFrom: `#${draggingFromId}`,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: "body",
                    endOnly: true,
                }),
            ],
            onmove: dragMoveListener,
            onend: function() {
                checkAndRepositionIfNeeded(cornerId, true);
            }
        });

        avoidMoodleMouseDownPropagationProblem(draggingFromId);
    }

    /**
     *
     * @param {string} draggingFromId
     */
    function avoidMoodleMouseDownPropagationProblem(draggingFromId) {

        const draggingEl = document.getElementById(draggingFromId);
        draggingEl.addEventListener("mousedown", function (event) {
            event.preventDefault();
            event.stopPropagation();
        });
    }

    let ldt = 0;
    /**
     * @param {Object} event
     */
    function dragMoveListener(event) {
        event.preventDefault();
        event.stopPropagation();
        let transform = false;
        const time = new Date().getTime();
        if (time - ldt > 15) {
            ldt = time;
            transform = true;
        }
        const target = event.target,
            // Keep the dragged position in the data-x/data-y attributes
            x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx,
            y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        // Translate the element
        if (transform) {
            target.style.webkitTransform =
                target.style.transform =
                'translate(' + x + 'px, ' + y + 'px)';
        }
        // Update the posiion attributes
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    /**
     *
     */
    function moveCornerToBodyTag() {
        const cornerId = "block_smowl_content";
        let corner = document.getElementById(cornerId);
        if (!corner) {
            return;
        }
        const body = document.getElementsByTagName("body")[0];
        body.appendChild(corner);
    }

    /**
     *
     */
    function setFormSrc() {
        const cornerId = "block_smowl_content";
        let corner = document.getElementById(cornerId);
        const iframe = corner.getElementsByTagName("iframe")[0];
        if (iframe.attributes["data-src"]) {
            iframe.src = iframe.attributes["data-src"].value;
        }
    }

    const isInViewport = (element, allowPartialView) => {
     try {
         const rect = element.getBoundingClientRect();
         const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
         const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

         if (allowPartialView) {
             const isPartiallyVisible = (
                 rect.top < viewportHeight &&
                 rect.bottom > 0 &&
                 rect.left < viewportWidth &&
                 rect.right > 0
             );

             return isPartiallyVisible;
         } else {
             const isCompletelyVisible = (
                 rect.top >= 0 &&
                 rect.left >= 0 &&
                 rect.bottom <= viewportHeight &&
                 rect.right <= viewportWidth
             );

             return isCompletelyVisible;
         }
     } catch (error) {
         log.error("[SMOWL Plugin] Error checking viewport visibility");
         return false;
     }
 };

 const resetPosition = (elementId) => {
     try {
         const corner = document.getElementById(elementId);
         if (!corner) { return; }

         const marginBottom = 20;
         const marginRight = 20;
         corner.style.transform = 'translate(0, 0)';
         corner.setAttribute('data-x', 0);
         corner.setAttribute('data-y', 0);
         corner.style.bottom = `${marginBottom}px`;
         corner.style.right = `${marginRight}px`;
     } catch (error) {
         log.error("[SMOWL Plugin] Error resetting position");
     }
 };

 const checkAndRepositionIfNeeded = (elementId, allowPartialView) => {
     try {
         const corner = document.getElementById(elementId);
         if (!corner) { return; }
         if (!isInViewport(corner, allowPartialView)) {
             resetPosition(elementId);
         }
     } catch (error) {
         log.error("[SMOWL Plugin] Error checking and repositioning");
     }
 };

 const initPositionResetHandler = () => {
    const cornerId = "block_smowl_content";
     try {
         const checkPosition = () => checkAndRepositionIfNeeded(cornerId, false);

         window.removeEventListener('resize', checkPosition);
         window.addEventListener('resize', checkPosition);

         checkPosition();

         return checkPosition;
     } catch (error) {
         log.error("[SMOWL Plugin] Error initializing position handler");
         return () => {};
     }
 };

    return {
        init: function () {
            if (document.getElementById("block_smowl_content")) {
                try {
                    makeCornerDraggable();
                    moveCornerToBodyTag();
                    initPositionResetHandler();
                } catch (e) { }
                setFormSrc();
            }
        },
    };
});