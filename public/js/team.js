/* ===== Team Sheet page ===== */
(function () {
    "use strict";

    const members = Array.isArray(window.TEAMIY_TEAM_MEMBERS)
        ? window.TEAMIY_TEAM_MEMBERS
        : [];
    const modalRoot = document.getElementById("teamMemberModalRoot");
    const overlay = document.getElementById("teamMemberModalOverlay");
    const closeButton = document.getElementById("closeTeamMemberModal");
    const cards = document.querySelectorAll("[data-team-card]");
    const searchInput = document.getElementById("teamSearch");
    const countLabel = document.getElementById("teamMemberCount");

    function closeModal() {
        if (!modalRoot) return;

        modalRoot.style.display = "none";
        document.body.style.overflow = "";
    }

    function detailRow(label, value) {
        return (
            '<div class="team-detail-row"><span>' +
            escapeHtml(label) +
            "</span><span>" +
            escapeHtml(value || "-") +
            "</span></div>"
        );
    }

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function openModal(member) {
        if (!modalRoot || !member) return;

        const avatar = document.getElementById("teamModalAvatar");
        const name = document.getElementById("teamModalName");
        const role = document.getElementById("teamModalRole");
        const department = document.getElementById("teamModalDepartment");
        const status = document.getElementById("teamModalStatus");
        const details = document.getElementById("teamModalDetails");

        if (avatar) {
            avatar.className = "team-avatar team-avatar-lg " + member.color;
            avatar.textContent = member.initials;
        }

        if (name) name.textContent = member.name;
        if (role) role.textContent = member.role;
        if (department) department.textContent = member.department;

        if (status) {
            status.className = "team-pill " + member.statusClass;
            status.textContent = member.status;
        }

        if (details) {
            details.innerHTML =
                detailRow("Email", member.email) +
                detailRow("Phone", member.phone) +
                detailRow("Reports to", member.manager) +
                detailRow("Joined", member.joined) +
                detailRow("Branch", member.branch) +
                detailRow("Employee Code", member.employeeCode) +
                detailRow("Employment", member.employmentType) +
                detailRow("User Type", member.userType);
        }

        modalRoot.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function applySearch() {
        const query = (searchInput ? searchInput.value : "").trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const matched = !query || card.dataset.search.includes(query);

            card.style.display = matched ? "" : "none";
            if (matched) visible += 1;
        });

        if (countLabel) {
            countLabel.textContent = visible + (visible === 1 ? " member" : " members");
        }
    }

    cards.forEach(function (card) {
        card.addEventListener("click", function () {
            const member = members.find(function (item) {
                return String(item.id) === String(card.dataset.memberId);
            });

            openModal(member);
        });
    });

    if (searchInput) {
        searchInput.addEventListener("input", applySearch);
    }

    if (closeButton) {
        closeButton.addEventListener("click", closeModal);
    }

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

    applySearch();
})();
