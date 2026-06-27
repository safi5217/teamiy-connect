/* ===== Resignation page modal ===== */
(function () {
    "use strict";

    const modalRoot = document.getElementById("resignationModalRoot");
    const overlay = document.getElementById("resignationModalOverlay");
    const openButton = document.getElementById("openResignationModal");
    const closeButton = document.getElementById("closeResignationModal");
    const closeButtons = document.querySelectorAll("[data-close-resignation-modal]");

    function openModal() {
        if (!modalRoot) return;

        modalRoot.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        if (!modalRoot) return;

        modalRoot.style.display = "none";
        document.body.style.overflow = "";
    }

    if (openButton) {
        openButton.addEventListener("click", openModal);
    }

    if (closeButton) {
        closeButton.addEventListener("click", closeModal);
    }

    closeButtons.forEach(function (button) {
        button.addEventListener("click", closeModal);
    });

    if (overlay) {
        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                closeModal();
            }
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    if (window.TEAMIY_OPEN_RESIGNATION_MODAL || /[?&]new=1/.test(window.location.search)) {
        openModal();
    }
})();
