/* ===== Notices page ===== */
(function () {
    "use strict";
    var TC = window.TC,
        esc = TC.esc,
        badge = TC.badge,
        avatar = TC.avatar,
        I = TC.I;

    function view() {
        var state = TC.state;
        var rows = state.notices
            .map(function (n, i) {
                return (
                    '<div style="display:flex;gap:13px;padding:16px 18px;background:#fff;border:1px solid ' +
                    (n.read ? "#EEF2F7" : "#D6E6F7") +
                    ';border-radius:14px;cursor:pointer;box-shadow:0 1px 2px rgba(16,30,54,.04)" data-action="open-notice" data-idx="' +
                    i +
                    '"><span class="dot ' +
                    (n.read ? "off" : "on") +
                    '"></span><div style="flex:1;min-width:0"><div class="row flex-wrap" style="gap:10px"><span style="font-size:14.5px;font-weight:' +
                    (n.read ? 600 : 800) +
                    ';color:#1E293B">' +
                    esc(n.title) +
                    "</span>" +
                    badge(n.priority) +
                    '</div><p style="font-size:13px;color:#64748B;margin-top:5px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden">' +
                    esc(n.preview) +
                    '</p><div style="font-size:12px;color:#94A3B8;margin-top:7px;font-weight:600">' +
                    esc(n.category) +
                    " · " +
                    esc(n.date) +
                    '</div></div><span style="flex:none;align-self:center">' +
                    I.chevR +
                    "</span></div>"
                );
            })
            .join("");
        return (
            '<div class="wrap-sm"><div class="spread" style="margin-bottom:16px"><span class="section-title" style="margin-right:auto">All Notices</span><button class="btn btn-sm" style="background:transparent;color:var(--primary)" data-action="mark-all-notices">Mark all read</button></div><div style="display:flex;flex-direction:column;gap:12px">' +
            rows +
            "</div></div>"
        );
    }

    TC.modals.notice = function () {
        var n = TC.state.notices[TC.state.activeNotice];
        return TC.ov(
            '<div class="modal" style="max-width:560px"><div style="padding:24px 26px 0"><div class="row" style="gap:10px;margin-bottom:14px">' +
                badge(n.priority) +
                '<span style="font-size:12.5px;color:#94A3B8;font-weight:600">' +
                esc(n.category) +
                " · " +
                esc(n.date) +
                '</span><button class="modal-x" style="margin-left:auto" data-action="close-modal">' +
                I.x +
                '</button></div><h3 style="font-size:21px;font-weight:800;letter-spacing:-.01em;line-height:1.25">' +
                esc(n.title) +
                '</h3></div><div style="padding:16px 26px 24px;font-size:14.5px;color:#475569;line-height:1.7;white-space:pre-line">' +
                esc(n.content) +
                '</div><div style="padding:14px 26px;border-top:1px solid var(--line-2);background:#FAFCFE;border-radius:0 0 20px 20px" class="row"><div style="display:flex;align-items:center;gap:10px">' +
                avatar(n.by, 32) +
                '<div style="font-size:12.5px;color:#64748B"><strong style="color:#334155">' +
                esc(n.by) +
                "</strong> · " +
                esc(n.dept) +
                "</div></div></div></div>",
        );
    };

    Object.assign(TC.actions, {
        "open-notice": function (el, e, idx) {
            var s = TC.state;
            s.activeNotice = +idx;
            s.notices[+idx].read = true;
            TC.openModal("notice");
            TC.render();
        },
        "mark-all-notices": function () {
            TC.state.notices.forEach(function (n) {
                n.read = true;
            });
            TC.toast("All notices marked read");
            TC.render();
        },
    });

    TC.boot(view);
})();
