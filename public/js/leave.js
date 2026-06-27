/* ===== Leave Management page ===== */
(function () {
    "use strict";

    const leaveModalRoot = document.getElementById("leaveModalRoot");
    const leaveModalOverlay = document.getElementById("leaveModalOverlay");
    const openLeaveModal = document.getElementById("openLeaveModal");
    const closeLeaveModal = document.getElementById("closeLeaveModal");
    const closeButtons = document.querySelectorAll("[data-close-leave-modal]");

    const tabButtons = document.querySelectorAll("[data-leave-tab]");
    const panels = document.querySelectorAll("[data-leave-panel]");

    const leaveFilter = document.getElementById("leaveFilter");
    const leaveTableBody = document.getElementById("leaveTableBody");

    function openModal() {
        if (!leaveModalRoot) return;

        leaveModalRoot.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        if (!leaveModalRoot) return;

        leaveModalRoot.style.display = "none";
        document.body.style.overflow = "";
    }

    function setActiveTab(activeTab) {
        tabButtons.forEach(function (button) {
            const isActive = button.dataset.leaveTab === activeTab;

            button.classList.toggle("btn-primary", isActive);
            button.classList.toggle("btn-ghost", !isActive);
        });

        panels.forEach(function (panel) {
            panel.style.display = panel.dataset.leavePanel === activeTab ? "block" : "none";
        });
    }

    function applyFilter() {
        if (!leaveFilter || !leaveTableBody) return;

        const selected = leaveFilter.value;

        leaveTableBody.querySelectorAll("tr").forEach(function (row) {
            const status = row.getAttribute("data-status");

            row.style.display = selected === "All" || selected === status ? "" : "none";
        });
    }

    if (openLeaveModal) {
        openLeaveModal.addEventListener("click", openModal);
    }

    if (closeLeaveModal) {
        closeLeaveModal.addEventListener("click", closeModal);
    }

    closeButtons.forEach(function (button) {
        button.addEventListener("click", closeModal);
    });

    if (leaveModalOverlay) {
        leaveModalOverlay.addEventListener("click", function (event) {
            if (event.target === leaveModalOverlay) {
                closeModal();
            }
        });
    }

    tabButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            setActiveTab(button.dataset.leaveTab);
        });
    });

    if (leaveFilter) {
        leaveFilter.addEventListener("change", applyFilter);
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    setActiveTab("full");
    applyFilter();

    if (/[?&]new=1/.test(window.location.search)) {
        openModal();
    }
})();