/* ===== TADA page modal ===== */
(function () {
    "use strict";

    const tadaModalRoot = document.getElementById("tadaModalRoot");
    const tadaModalOverlay = document.getElementById("tadaModalOverlay");
    const openTadaModal = document.getElementById("openTadaModal");
    const closeTadaModal = document.getElementById("closeTadaModal");
    const closeButtons = document.querySelectorAll("[data-close-tada-modal]");

    function openModal() {
        if (!tadaModalRoot) return;

        tadaModalRoot.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        if (!tadaModalRoot) return;

        tadaModalRoot.style.display = "none";
        document.body.style.overflow = "";
    }

    if (openTadaModal) {
        openTadaModal.addEventListener("click", openModal);
    }

    if (closeTadaModal) {
        closeTadaModal.addEventListener("click", closeModal);
    }

    closeButtons.forEach(function (button) {
        button.addEventListener("click", closeModal);
    });

    if (tadaModalOverlay) {
        tadaModalOverlay.addEventListener("click", function (event) {
            if (event.target === tadaModalOverlay) {
                closeModal();
            }
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    if (window.TEAMIY_OPEN_TADA_MODAL || /[?&]new=1/.test(window.location.search)) {
        openModal();
    }
})();
