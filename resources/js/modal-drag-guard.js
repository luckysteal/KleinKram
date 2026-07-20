/**
 * Global guard preventing modals (and overlays/dropdowns) from closing 
 * when a user starts mouse selection/click inside a modal panel and releases outside.
 */
(function () {
    let lastMouseDownTarget = null;

    document.addEventListener('mousedown', (e) => {
        lastMouseDownTarget = e.target;
    }, true);

    document.addEventListener('click', (e) => {
        if (!lastMouseDownTarget) return;

        // Find closest modal panel / content element containing the mousedown target
        const container = lastMouseDownTarget.closest(
            '.glass-panel, .modal-panel, [data-modal-panel], [role="dialog"], .modal-dialog, .modal-content'
        );

        // If mousedown started inside a modal panel, but the click (mouseup) target is OUTSIDE that panel
        if (container && !container.contains(e.target)) {
            // Stop propagation so @click.away, @click.outside, and backdrop click handlers do not fire
            e.stopImmediatePropagation();
        }

        lastMouseDownTarget = null;
    }, true);
})();
