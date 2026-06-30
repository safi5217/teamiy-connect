/* ===== Teamiy Connect — shared core =====
   Data, persisted state, helpers, the sidebar/topbar shell, modal plumbing
   and global event wiring. Each page's js/<page>.js registers its own view,
   modals and actions against the TC namespace, then calls TC.boot(renderFn). */
(function () {
    "use strict";

    var TC = (window.TC = {});

    // ---------- ICONS ----------
    var I = {
        dashboard:
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
        leave: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>',
        clock: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/></svg>',
        tada: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3.5h14v17l-2.5-1.5L14 20.5 12 19l-2 1.5L7.5 19 5 20.5z"/><path d="M9 8h6M9 12h6"/></svg>',
        resign: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h4M9 17h6"/></svg>',
        team: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3 2.5-4.6 5.5-4.6s5.5 1.6 5.5 4.6"/><path d="M16 5.2a3.2 3.2 0 0 1 0 6.1M17.5 14.6c2.2.5 3.5 1.9 3.5 4.4"/></svg>',
        projects:
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2.5h8a2 2 0 0 1 2 2V18a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>',
        assets: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="4.5" width="19" height="12" rx="2"/><path d="M2 20.5h20M9.5 20.5l.5-4M14.5 20.5l-.5-4"/></svg>',
        holidays:
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.8 4.8l1.8 1.8M17.4 17.4l1.8 1.8M19.2 4.8l-1.8 1.8M6.6 17.4l-1.8 1.8"/></svg>',
        notices:
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8.5a6 6 0 0 0-12 0c0 6-2.5 7.5-2.5 7.5h17S18 14.5 18 8.5"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>',
        meetings:
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="6" width="13" height="12" rx="2.5"/><path d="M15.5 10l6-3v10l-6-3z"/></svg>',
        settings:
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H2a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 3.6 8a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V2a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H22a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
        plus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>',
        x: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>',
        check: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>',
        chevR: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>',
        back: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>',
        send: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>',
        cal: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 2.5v4M16 2.5v4"/></svg>',
        comment:
            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-9 8.5 8.5 8.5 0 0 1-3.8-.9L3 20l1.9-5.2A8.5 8.5 0 0 1 12 3a8.38 8.38 0 0 1 9 8.5z"/></svg>',
        mic: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2.5" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3.5"/></svg>',
        clip: '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5l-8.5 8.5a5 5 0 0 1-7-7L13.5 5a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.7 1.7 0 0 1-2.3-2.3l7.8-7.8"/></svg>',
        photo: '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2.5"/><circle cx="8.5" cy="9.5" r="1.8"/><path d="M21 16l-5-5L5 20"/></svg>',
        file: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
        upload: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5-5 5 5M12 5v12"/></svg>',
        ret: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14L4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 5 5v2"/></svg>',
    };
    TC.I = I;
    TC.assetIcons = {
        laptop: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="11" rx="1.8"/><path d="M2 20h20"/></svg>',
        phone: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10.5 18.5h3"/></svg>',
        audio: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 13v-1a8 8 0 0 1 16 0v1"/><rect x="3" y="13" width="4" height="6" rx="1.4"/><rect x="17" y="13" width="4" height="6" rx="1.4"/></svg>',
        card: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/></svg>',
        monitor:
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M9 20h6M12 16v4"/></svg>',
    };
    TC.assetTint = {
        laptop: "tint-blue",
        phone: "tint-violet",
        audio: "tint-green",
        card: "tint-orange",
        monitor: "tint-gray",
    };

    // ---------- STATIC DATA ----------
    var TEAM = [
        {
            name: "Hamza Sheikh",
            role: "Engineering Manager",
            dept: "Product Eng",
            email: "hamza.sheikh@teamiy.com",
            phone: "+92 321 4455667",
            status: "Active",
            manager: "Director of Eng",
            joined: "03 Feb 2022",
        },
        {
            name: "Sara Ahmed",
            role: "Product Designer",
            dept: "Design",
            email: "sara.ahmed@teamiy.com",
            phone: "+92 333 1122334",
            status: "Active",
            manager: "Design Lead",
            joined: "17 Aug 2023",
        },
        {
            name: "Bilal Raza",
            role: "Backend Engineer",
            dept: "Product Eng",
            email: "bilal.raza@teamiy.com",
            phone: "+92 300 9988776",
            status: "Active",
            manager: "Hamza Sheikh",
            joined: "09 Jan 2024",
        },
        {
            name: "Fatima Noor",
            role: "QA Engineer",
            dept: "Quality",
            email: "fatima.noor@teamiy.com",
            phone: "+92 345 5566778",
            status: "Active",
            manager: "Hamza Sheikh",
            joined: "22 Mar 2024",
        },
        {
            name: "Usman Tariq",
            role: "DevOps Engineer",
            dept: "Platform",
            email: "usman.tariq@teamiy.com",
            phone: "+92 301 2233445",
            status: "Active",
            manager: "Hamza Sheikh",
            joined: "11 Jun 2023",
        },
        {
            name: "Zainab Ali",
            role: "Frontend Engineer",
            dept: "Product Eng",
            email: "zainab.ali@teamiy.com",
            phone: "+92 312 6677889",
            status: "Probation",
            manager: "Ayesha Khan",
            joined: "02 May 2026",
        },
        {
            name: "Ahmed Malik",
            role: "Data Analyst",
            dept: "Data",
            email: "ahmed.malik@teamiy.com",
            phone: "+92 308 7788990",
            status: "Active",
            manager: "BI Lead",
            joined: "28 Nov 2023",
        },
        {
            name: "Nida Iqbal",
            role: "HR Specialist",
            dept: "People",
            email: "nida.iqbal@teamiy.com",
            phone: "+92 322 3344556",
            status: "Active",
            manager: "Head of People",
            joined: "14 Jul 2022",
        },
    ];
    TC.TEAM = TEAM;

    var KEYMAP = {
        Pending: "amber",
        Approved: "green",
        Rejected: "red",
        Cancelled: "gray",
        Paid: "blue",
        Present: "green",
        Late: "amber",
        "On Leave": "violet",
        Absent: "red",
        "Checked In": "blue",
        "Checked Out": "gray",
        "In Progress": "blue",
        Completed: "green",
        "On Hold": "amber",
        "Not Started": "gray",
        Critical: "red",
        High: "orange",
        Medium: "amber",
        Low: "gray",
        Scheduled: "blue",
        Assigned: "green",
        Returned: "gray",
        "Return Pending": "amber",
        Damaged: "red",
        Maintenance: "amber",
        Urgent: "red",
        Important: "orange",
        Normal: "gray",
        Public: "blue",
        Company: "violet",
        Optional: "gray",
        Active: "green",
        Probation: "amber",
        Inactive: "gray",
        Done: "green",
        "To Do": "gray",
        "Under Review": "amber",
    };

    var TITLES = {
        dashboard: "Dashboard",
        leave: "Leave Management",
        attendance: "Attendance",
        team: "Team Sheet",
        projects: "Projects",
        assets: "Assets",
        holidays: "Company Holidays",
        notices: "Notices",
        meetings: "Team Meetings",
        tada: "TADA",
        resignation: "Resignation",
        inbox: "Inbox",
        profile: "Profile Settings",
        settings: "Settings",
    };
    TC.TITLES = TITLES;
    TC.TODAY_LONG = "Monday, 22 June 2026";

    // ---------- DEFAULT STATE ----------
    function defaultState() {
        return {
            navCollapsed: false,
            att: {
                status: "out",
                inTime: "",
                outTime: "",
                hours: "",
                inEpoch: 0,
                sessions: [],
            },
            modal: null,
            activeNotice: null,
            activeMember: null,
            selectedProject: null,
            activeTask: null,
            activeMessage: 0,
            recording: false,
            playingVoice: null,
            taskFilter: "All",
            leaveFilter: "All",
            teamQuery: "",
            profilePhoto: null,
            leaveForm: {
                type: "Annual Leave",
                mode: "Full Day",
                start: "2026-06-24",
                end: "2026-06-26",
                half: "First Half",
                fromTime: "14:00",
                toTime: "16:00",
                reason: "",
            },
            tadaForm: {
                type: "Travel",
                date: "2026-06-22",
                from: "",
                to: "",
                purpose: "",
                amount: "",
            },
            resignForm: {
                date: "2026-06-22",
                lastDay: "2026-07-22",
                reason: "Better opportunity elsewhere",
                notes: "",
            },
            newTask: {
                title: "",
                assignee: "Bilal Raza",
                due: "2026-06-30",
                priority: "Medium",
                status: "To Do",
            },
            newProject: {
                name: "",
                desc: "",
                mgr: "Hamza Sheikh",
                deadline: "2026-09-30",
                priority: "Medium",
                members: ["Ayesha Khan"],
            },
            profileForm: {
                name: "Ayesha Khan",
                email: "ayesha.khan@teamiy.com",
                phone: "+92 300 1234567",
                emName: "Imran Khan",
                emPhone: "+92 301 9876543",
                address:
                    "House 14, Street 7, DHA Phase 5, Karachi 75500, Pakistan",
                bankName: "Meezan Bank",
                accountNumber: "0123-4567890-1",
                accountType: "Savings",
            },
            resignation: null,
            leaves: [
                {
                    type: "Annual Leave",
                    mode: "Multi Day",
                    dates: "24 – 26 Jun 2026",
                    duration: "3 days",
                    reason: "Family trip to Murree",
                    status: "Pending",
                    remarks: "—",
                },
                {
                    type: "Sick Leave",
                    mode: "Full Day",
                    dates: "10 Jun 2026",
                    duration: "1 day",
                    reason: "High fever",
                    status: "Approved",
                    remarks: "Get well soon — Hamza",
                },
                {
                    type: "Short Leave",
                    mode: "Short Leave",
                    dates: "5 Jun 2026",
                    duration: "2:00–4:00 PM",
                    reason: "Doctor appointment",
                    status: "Approved",
                    remarks: "OK",
                },
                {
                    type: "Casual Leave",
                    mode: "Full Day",
                    dates: "28 May 2026",
                    duration: "1 day",
                    reason: "Personal errand",
                    status: "Rejected",
                    remarks: "Peak sprint week",
                },
                {
                    type: "Annual Leave",
                    mode: "Multi Day",
                    dates: "14 – 18 Apr 2026",
                    duration: "5 days",
                    reason: "Eid holidays",
                    status: "Approved",
                    remarks: "Enjoy!",
                },
            ],
            tadas: [
                {
                    type: "Travel",
                    route: "Karachi → Lahore",
                    date: "5 Jun 2026",
                    purpose: "Client site visit — Acme Corp",
                    amount: "PKR 18,500",
                    status: "Approved",
                    remarks: "Verified",
                },
                {
                    type: "Daily Allowance",
                    route: "Lahore",
                    date: "5 Jun 2026",
                    purpose: "On-site per diem (1 day)",
                    amount: "PKR 3,000",
                    status: "Approved",
                    remarks: "OK",
                },
                {
                    type: "Hotel",
                    route: "Lahore",
                    date: "5 Jun 2026",
                    purpose: "Overnight stay near client",
                    amount: "PKR 12,000",
                    status: "Pending",
                    remarks: "—",
                },
                {
                    type: "Fuel",
                    route: "Karachi",
                    date: "20 May 2026",
                    purpose: "Local client meetings",
                    amount: "PKR 4,200",
                    status: "Paid",
                    remarks: "Paid 24 May",
                },
                {
                    type: "Food",
                    route: "Karachi",
                    date: "15 Jun 2026",
                    purpose: "Client lunch",
                    amount: "PKR 2,800",
                    status: "Rejected",
                    remarks: "No receipt attached",
                },
            ],
            notices: [
                {
                    title: "Updated Remote Work Policy",
                    category: "HR",
                    priority: "Important",
                    date: "20 Jun 2026",
                    read: false,
                    by: "Nida Iqbal",
                    dept: "People Team",
                    preview:
                        "Hybrid schedule moves to 3 days in-office starting July. Please review the updated guidelines.",
                    content:
                        "Effective 1 July 2026, our hybrid policy moves to a minimum of 3 days in-office per week (Tuesday, Wednesday and one flexible day).\n\nManagers may approve additional remote days based on project needs. Core collaboration hours remain 11:00 AM – 4:00 PM.\n\nPlease coordinate your in-office days with your team lead by 28 June.",
                },
                {
                    title: "Office Closed for Eid al-Adha",
                    category: "Admin",
                    priority: "Urgent",
                    date: "19 Jun 2026",
                    read: false,
                    by: "Admin Office",
                    dept: "Administration",
                    preview:
                        "The office will remain closed Friday 26 to Sunday 28 June for Eid al-Adha.",
                    content:
                        "The Teamiy office will be closed from Friday 26 June through Sunday 28 June 2026 for Eid al-Adha.\n\nAll systems remain accessible remotely. On-call support follows the published roster. Normal operations resume Monday 29 June.\n\nEid Mubarak to you and your families!",
                },
                {
                    title: "Q3 Town Hall Scheduled",
                    category: "General",
                    priority: "Normal",
                    date: "18 Jun 2026",
                    read: true,
                    by: "Leadership",
                    dept: "Executive",
                    preview:
                        "Join the company-wide Q3 town hall on 1 July at 4:00 PM.",
                    content:
                        "Our Q3 Town Hall is scheduled for Tuesday 1 July 2026 at 4:00 PM (PKT).\n\nLeadership will share Q2 results, the Q3 roadmap and an open Q&A. A calendar invite with the meeting link will follow.",
                },
                {
                    title: "New Health Insurance Provider",
                    category: "HR",
                    priority: "Important",
                    date: "12 Jun 2026",
                    read: true,
                    by: "Nida Iqbal",
                    dept: "People Team",
                    preview:
                        "We are switching to ShifaCare from 1 August. New cards will be issued.",
                    content:
                        "From 1 August 2026 our group health coverage moves to ShifaCare, with an expanded hospital network and faster claims.\n\nNew insurance cards will be distributed by 25 July. Existing claims in progress will be honored under the current provider.",
                },
                {
                    title: "Parking Lot Maintenance",
                    category: "Admin",
                    priority: "Normal",
                    date: "8 Jun 2026",
                    read: true,
                    by: "Admin Office",
                    dept: "Administration",
                    preview:
                        "Basement parking will be repainted over the weekend of 14–15 June.",
                    content:
                        "The basement parking area will be repainted and resurfaced on Saturday 14 and Sunday 15 June.\n\nPlease use street parking or the adjacent plaza lot during this period. The lot reopens Monday morning.",
                },
            ],
            projects: [
                {
                    name: "Teamiy Mobile App",
                    role: "Frontend Lead",
                    desc: "React Native rebuild of the employee app with offline check-in and push notifications.",
                    progress: 68,
                    status: "In Progress",
                    deadline: "15 Aug 2026",
                    priority: "High",
                    mgr: "Hamza Sheikh",
                    members: [
                        "Ayesha Khan",
                        "Bilal Raza",
                        "Zainab Ali",
                        "Fatima Noor",
                    ],
                    tasks: [
                        {
                            title: "Offline check-in sync logic",
                            assignee: "Ayesha Khan",
                            due: "28 Jun 2026",
                            status: "In Progress",
                            priority: "High",
                            comments: [
                                {
                                    by: "Hamza Sheikh",
                                    text: "Let's make sure conflicts resolve last-write-wins for now.",
                                    time: "2 days ago",
                                },
                                {
                                    by: "Ayesha Khan",
                                    text: "Agreed. Prototyping the queue today.",
                                    time: "1 day ago",
                                },
                            ],
                        },
                        {
                            title: "Push notification permissions UX",
                            assignee: "Zainab Ali",
                            due: "01 Jul 2026",
                            status: "To Do",
                            priority: "Medium",
                            comments: [
                                {
                                    by: "Sara Ahmed",
                                    text: "Designs are in Figma — soft-ask before the OS prompt.",
                                    time: "4 hours ago",
                                },
                            ],
                        },
                        {
                            title: "Biometric login",
                            assignee: "Bilal Raza",
                            due: "20 Jun 2026",
                            status: "Done",
                            priority: "High",
                            comments: [],
                        },
                        {
                            title: "QA pass — auth flows",
                            assignee: "Fatima Noor",
                            due: "05 Jul 2026",
                            status: "To Do",
                            priority: "Medium",
                            comments: [],
                        },
                    ],
                },
                {
                    name: "Customer Portal Revamp",
                    role: "UI Engineer",
                    desc: "New self-service portal for external customers with a refreshed design system.",
                    progress: 42,
                    status: "In Progress",
                    deadline: "30 Jul 2026",
                    priority: "Critical",
                    mgr: "Sara Ahmed",
                    members: ["Ayesha Khan", "Sara Ahmed", "Bilal Raza"],
                    tasks: [
                        {
                            title: "Empty-state components",
                            assignee: "Ayesha Khan",
                            due: "24 Jun 2026",
                            status: "In Progress",
                            priority: "Critical",
                            comments: [
                                {
                                    by: "Sara Ahmed",
                                    text: "Revised Figma shared — check the new illustrations.",
                                    time: "Yesterday",
                                },
                            ],
                        },
                        {
                            title: "Billing API integration",
                            assignee: "Bilal Raza",
                            due: "02 Jul 2026",
                            status: "To Do",
                            priority: "High",
                            comments: [],
                        },
                    ],
                },
                {
                    name: "Design System 2.0",
                    role: "Contributor",
                    desc: "Token-driven component library shared across web and mobile products.",
                    progress: 25,
                    status: "On Hold",
                    deadline: "TBD",
                    priority: "Medium",
                    mgr: "Sara Ahmed",
                    members: ["Sara Ahmed", "Ayesha Khan"],
                    tasks: [
                        {
                            title: "Token naming convention",
                            assignee: "Sara Ahmed",
                            due: "TBD",
                            status: "To Do",
                            priority: "Medium",
                            comments: [],
                        },
                    ],
                },
                {
                    name: "Analytics Dashboard",
                    role: "Frontend Engineer",
                    desc: "Internal KPI dashboard for leadership with real-time charts.",
                    progress: 100,
                    status: "Completed",
                    deadline: "20 May 2026",
                    priority: "High",
                    mgr: "Ahmed Malik",
                    members: ["Ayesha Khan", "Ahmed Malik"],
                    tasks: [
                        {
                            title: "Real-time chart streaming",
                            assignee: "Ayesha Khan",
                            due: "15 May 2026",
                            status: "Done",
                            priority: "High",
                            comments: [],
                        },
                        {
                            title: "Export to PDF",
                            assignee: "Ahmed Malik",
                            due: "18 May 2026",
                            status: "Done",
                            priority: "Medium",
                            comments: [],
                        },
                    ],
                },
                {
                    name: "Internal Wiki",
                    role: "Frontend Engineer",
                    desc: "Centralized knowledge base to replace scattered docs.",
                    progress: 0,
                    status: "Not Started",
                    deadline: "12 Sep 2026",
                    priority: "Low",
                    mgr: "Nida Iqbal",
                    members: ["Ayesha Khan", "Nida Iqbal"],
                    tasks: [],
                },
            ],
            assets: [
                {
                    name: 'MacBook Pro 16" M3',
                    category: "Laptop",
                    serial: "MBP3-8842",
                    condition: "Good",
                    assigned: "12 Jan 2025",
                    brand: "Apple",
                    status: "Assigned",
                    kind: "laptop",
                },
                {
                    name: "iPhone 15",
                    category: "Mobile",
                    serial: "IP15-2231",
                    condition: "Good",
                    assigned: "04 Mar 2025",
                    brand: "Apple",
                    status: "Assigned",
                    kind: "phone",
                },
                {
                    name: "Jabra Evolve2 Headset",
                    category: "Accessory",
                    serial: "JB-77120",
                    condition: "Good",
                    assigned: "12 Jan 2025",
                    brand: "Jabra",
                    status: "Assigned",
                    kind: "audio",
                },
                {
                    name: "Access Card #0428",
                    category: "ID Card",
                    serial: "AC-0428",
                    condition: "Good",
                    assigned: "12 Jan 2025",
                    brand: "HID",
                    status: "Assigned",
                    kind: "card",
                },
                {
                    name: 'Dell UltraSharp 27"',
                    category: "Accessory",
                    serial: "DU-5510",
                    condition: "Fair",
                    assigned: "12 Jan 2025",
                    brand: "Dell",
                    status: "Returned",
                    kind: "monitor",
                },
            ],
            holidays: [
                {
                    day: "01",
                    month: "May",
                    title: "Labour Day",
                    weekday: "Friday",
                    type: "Public",
                    past: true,
                },
                {
                    day: "26",
                    month: "Jun",
                    title: "Eid al-Adha (Day 1)",
                    weekday: "Friday",
                    type: "Public",
                    past: false,
                },
                {
                    day: "14",
                    month: "Aug",
                    title: "Independence Day",
                    weekday: "Friday",
                    type: "Public",
                    past: false,
                },
                {
                    day: "10",
                    month: "Sep",
                    title: "Company Foundation Day",
                    weekday: "Thursday",
                    type: "Company",
                    past: false,
                },
                {
                    day: "09",
                    month: "Nov",
                    title: "Iqbal Day",
                    weekday: "Monday",
                    type: "Optional",
                    past: false,
                },
                {
                    day: "25",
                    month: "Dec",
                    title: "Quaid-e-Azam Day",
                    weekday: "Friday",
                    type: "Public",
                    past: false,
                },
            ],
            meetings: [
                {
                    title: "Sprint Planning — Mobile App",
                    day: "22",
                    month: "Jun",
                    time: "3:00 PM",
                    host: "Hamza Sheikh",
                    platform: "Zoom",
                    status: "Scheduled",
                    when: "upcoming",
                    link: "https://zoom.us/j/teamiy-sprint",
                },
                {
                    title: "Design Review — Customer Portal",
                    day: "23",
                    month: "Jun",
                    time: "11:00 AM",
                    host: "Sara Ahmed",
                    platform: "Google Meet",
                    status: "Scheduled",
                    when: "upcoming",
                    link: "https://meet.google.com/teamiy-design",
                },
                {
                    title: "Weekly Engineering Sync",
                    day: "24",
                    month: "Jun",
                    time: "10:00 AM",
                    host: "Hamza Sheikh",
                    platform: "Teams",
                    status: "Scheduled",
                    when: "upcoming",
                    link: "https://teams.microsoft.com/teamiy-eng",
                },
                {
                    title: "Q3 Town Hall",
                    day: "01",
                    month: "Jul",
                    time: "4:00 PM",
                    host: "Leadership",
                    platform: "Zoom",
                    status: "Scheduled",
                    when: "upcoming",
                    link: "https://zoom.us/j/teamiy-townhall",
                },
                {
                    title: "Retrospective — Sprint 14",
                    day: "19",
                    month: "Jun",
                    time: "4:30 PM",
                    host: "Hamza Sheikh",
                    platform: "Zoom",
                    status: "Completed",
                    when: "past",
                    link: "",
                },
                {
                    title: "Onboarding — Zainab Ali",
                    day: "12",
                    month: "Jun",
                    time: "2:00 PM",
                    host: "Nida Iqbal",
                    platform: "Teams",
                    status: "Completed",
                    when: "past",
                    link: "",
                },
            ],
            messages: [
                {
                    from: "Hamza Sheikh",
                    role: "Engineering Manager",
                    online: true,
                    unread: 2,
                    thread: [
                        {
                            me: false,
                            kind: "text",
                            text: "Hi Ayesha! Quick one before standup 👋",
                            time: "9:38 AM",
                        },
                        {
                            me: false,
                            kind: "text",
                            text: "Could you scope the offline check-in work for the mobile app?",
                            time: "9:39 AM",
                        },
                        {
                            me: true,
                            kind: "text",
                            text: "Morning! Sure — looking at sync logic, conflict handling and the UI states.",
                            time: "9:41 AM",
                        },
                        {
                            me: false,
                            kind: "voice",
                            dur: "0:24",
                            time: "9:42 AM",
                        },
                        {
                            me: false,
                            kind: "file",
                            fileName: "Sprint15-scope.pdf",
                            fileSize: "248 KB",
                            time: "9:42 AM",
                        },
                        {
                            me: true,
                            kind: "text",
                            text: "Got it, will send estimates by EOD.",
                            time: "9:45 AM",
                        },
                    ],
                },
                {
                    from: "Sara Ahmed",
                    role: "Product Designer",
                    online: true,
                    unread: 1,
                    thread: [
                        {
                            me: false,
                            kind: "text",
                            text: "Shared the revised Figma for the Customer Portal 🎨",
                            time: "Yesterday",
                        },
                        {
                            me: false,
                            kind: "image",
                            cap: "New empty states",
                            time: "Yesterday",
                        },
                        {
                            me: true,
                            kind: "text",
                            text: "Love the new empty states! Feasible — give me till Wednesday.",
                            time: "Yesterday",
                        },
                        {
                            me: false,
                            kind: "voice",
                            dur: "0:11",
                            time: "Yesterday",
                        },
                    ],
                },
                {
                    from: "Nida Iqbal",
                    role: "People Team",
                    online: false,
                    unread: 0,
                    thread: [
                        {
                            me: false,
                            kind: "text",
                            text: "Hi Ayesha, please confirm your dependents for the new ShifaCare policy by 25 July.",
                            time: "8:15 AM",
                        },
                        {
                            me: false,
                            kind: "file",
                            fileName: "ShifaCare-coverage.pdf",
                            fileSize: "1.2 MB",
                            time: "8:15 AM",
                        },
                        {
                            me: true,
                            kind: "text",
                            text: "Thanks Nida — no changes from last year. Confirmed ✅",
                            time: "8:30 AM",
                        },
                    ],
                },
                {
                    from: "IT Helpdesk",
                    role: "Platform",
                    online: false,
                    unread: 0,
                    thread: [
                        {
                            me: false,
                            kind: "text",
                            text: "Good news — your MacBook Pro warranty is extended through Jan 2027 at no cost.",
                            time: "Mon",
                        },
                        {
                            me: true,
                            kind: "text",
                            text: "Great, thanks for the heads up!",
                            time: "Mon",
                        },
                    ],
                },
                {
                    from: "Payroll",
                    role: "Finance",
                    online: false,
                    unread: 0,
                    thread: [
                        {
                            me: false,
                            kind: "text",
                            text: "Your June 2026 payslip is now available in the portal 💰",
                            time: "Sun",
                        },
                        {
                            me: false,
                            kind: "file",
                            fileName: "Payslip-Jun2026.pdf",
                            fileSize: "96 KB",
                            time: "Sun",
                        },
                    ],
                },
            ],
        };
    }

    // ---------- STATE + PERSISTENCE ----------
    // Only data that the user can mutate is persisted across page loads; transient
    // UI (open modal, active row, draft forms) always starts fresh from defaults.
    var PERSIST = [
        "att",
        "leaves",
        "tadas",
        "notices",
        "projects",
        "assets",
        "resignation",
        "messages",
        "profileForm",
        "profilePhoto",
    ];
    var STORE_KEY = "tc_state";
    var state;

    function loadState() {
        state = defaultState();
        try {
            var saved = JSON.parse(localStorage.getItem(STORE_KEY) || "null");
            if (saved)
                PERSIST.forEach(function (k) {
                    if (saved[k] !== undefined) state[k] = saved[k];
                });
        } catch (e) {
            /* ignore corrupt store */
        }
        TC.state = state;
    }
    function save() {
        var out = {};
        PERSIST.forEach(function (k) {
            out[k] = state[k];
        });
        try {
            localStorage.setItem(STORE_KEY, JSON.stringify(out));
        } catch (e) {
            /* ignore quota */
        }
    }
    TC.save = save;

    // ---------- HELPERS ----------
    function $(sel, root) {
        return (root || document).querySelector(sel);
    }
    TC.$ = $;
    function esc(s) {
        return String(s == null ? "" : s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }
    function initials(name) {
        return (name || "")
            .split(" ")
            .map(function (w) {
                return w[0];
            })
            .slice(0, 2)
            .join("")
            .toUpperCase();
    }
    function colorClass(name) {
        var i = TEAM.findIndex(function (t) {
            return t.name === name;
        });
        if (i < 0) i = name ? name.charCodeAt(0) % 6 : 0;
        return "av-c" + (i % 6);
    }
    function badgeClass(label) {
        return "badge-" + (KEYMAP[label] || "gray");
    }
    function badge(label, extra) {
        return (
            '<span class="badge ' +
            (extra || "") +
            " " +
            badgeClass(label) +
            '">' +
            esc(label) +
            "</span>"
        );
    }
    function avatar(name, px, sq) {
        return (
            '<div class="avatar ' +
            colorClass(name) +
            (sq ? " av-sq" : "") +
            '" style="width:' +
            px +
            "px;height:" +
            px +
            "px;font-size:" +
            (px < 30 ? 10 : px < 40 ? 13 : 15) +
            'px">' +
            initials(name) +
            "</div>"
        );
    }
    function fmtDate(d) {
        if (!d) return "";
        var dt = new Date(d + "T00:00");
        return dt.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }
    function fmtTime(d) {
        var h = d.getHours(),
            m = d.getMinutes();
        var ap = h >= 12 ? "PM" : "AM";
        h = h % 12 || 12;
        return h + ":" + String(m).padStart(2, "0") + " " + ap;
    }
    function t12(t) {
        if (!t) return "";
        var p = t.split(":").map(Number);
        var h = p[0],
            m = p[1];
        var ap = h >= 12 ? "PM" : "AM";
        h = h % 12 || 12;
        return h + ":" + String(m).padStart(2, "0") + " " + ap;
    }
    TC.esc = esc;
    TC.initials = initials;
    TC.colorClass = colorClass;
    TC.badgeClass = badgeClass;
    TC.badge = badge;
    TC.avatar = avatar;
    TC.fmtDate = fmtDate;
    TC.fmtTime = fmtTime;
    TC.t12 = t12;

    function pendingLeaveCount() {
        return state.leaves.filter(function (l) {
            return l.status === "Pending";
        }).length;
    }
    function unreadNotices() {
        return state.notices.filter(function (n) {
            return !n.read;
        }).length;
    }
    function unreadInbox() {
        return state.messages.reduce(function (n, m) {
            return n + (m.unread ? 1 : 0);
        }, 0);
    }
    TC.pendingLeaveCount = pendingLeaveCount;
    TC.unreadNotices = unreadNotices;
    TC.unreadInbox = unreadInbox;

    function todayKey() {
        var d = new Date();

        return (
            d.getFullYear() +
            "-" +
            String(d.getMonth() + 1).padStart(2, "0") +
            "-" +
            String(d.getDate()).padStart(2, "0")
        );
    }

    function fmtTimeWithMs(d) {
        var h = d.getHours();
        var m = d.getMinutes();
        var s = d.getSeconds();
        var ms = d.getMilliseconds();

        var ap = h >= 12 ? "PM" : "AM";
        h = h % 12 || 12;

        return (
            h +
            ":" +
            String(m).padStart(2, "0") +
            ":" +
            String(s).padStart(2, "0") +
            "." +
            String(ms).padStart(3, "0") +
            " " +
            ap
        );
    }

    function msToText(totalMs) {
        totalMs = Math.max(0, totalMs || 0);

        var hours = Math.floor(totalMs / 3600000);
        totalMs = totalMs % 3600000;

        var minutes = Math.floor(totalMs / 60000);
        totalMs = totalMs % 60000;

        var seconds = Math.floor(totalMs / 1000);
        var milliseconds = totalMs % 1000;

        return (
            hours +
            "h " +
            String(minutes).padStart(2, "0") +
            "m " +
            String(seconds).padStart(2, "0") +
            "s " +
            String(milliseconds).padStart(3, "0") +
            "ms"
        );
    }

    function ensureAttSessions() {
        if (!state.att) {
            state.att = {
                status: "out",
                inTime: "",
                outTime: "",
                hours: "",
                inEpoch: 0,
                sessions: [],
            };
        }

        if (!Array.isArray(state.att.sessions)) {
            state.att.sessions = [];
        }
    }

    function todayAttSessions() {
        ensureAttSessions();

        var today = todayKey();

        return state.att.sessions.filter(function (s) {
            return s.date === today;
        });
    }

    function activeAttSession() {
        var sessions = todayAttSessions();

        for (var i = sessions.length - 1; i >= 0; i--) {
            if (!sessions[i].outEpochMs) {
                return sessions[i];
            }
        }

        return null;
    }

    function totalTodayMs() {
        var total = 0;
        var now = Date.now();

        todayAttSessions().forEach(function (s) {
            if (s.outEpochMs) {
                total += s.durationMs || 0;
            } else if (s.inEpochMs) {
                total += Math.max(0, now - s.inEpochMs);
            }
        });

        return total;
    }

    function attVM() {
        ensureAttSessions();

        var active = activeAttSession();
        var sessions = todayAttSessions();
        var total = msToText(totalTodayMs());

        if (active) {
            state.att.status = "in";
            state.att.inTime = active.inTime;
            state.att.inEpoch = active.inEpochMs;
            state.att.hours = total;

            return {
                status: "in",
                statusLabel: "Checked in",
                cardValue: total,
                cardSub:
                    "Current session started at " +
                    active.inTime +
                    " · Sessions today: " +
                    sessions.length,
                btnLabel: "Check Out",
                heroLabel: "Check Out",
                summary:
                    "You checked in at " +
                    active.inTime +
                    ". Total today: " +
                    total +
                    ".",
                btnClass: "in",
            };
        }

        state.att.status = "out";
        state.att.hours = total;

        return {
            status: "out",
            statusLabel: sessions.length ? "Checked out" : "Not checked in",
            cardValue: total,
            cardSub: sessions.length
                ? sessions.length + " sessions completed today"
                : "Tap check in to start your day",
            btnLabel: "Check In",
            heroLabel: "Check In",
            summary: sessions.length
                ? "You are currently checked out. Total today: " + total + "."
                : "You haven't checked in yet — have a great day!",
            btnClass: "out",
        };
    }

    TC.attVM = attVM;

    function attHistory() {
        ensureAttSessions();

        var today = todayKey();
        var sessions = todayAttSessions();

        var rows = sessions
            .slice()
            .reverse()
            .map(function (s, index) {
                return {
                    date: "Today",
                    day: "Session " + (sessions.length - index),
                    in: s.inTime || "—",
                    out: s.outTime || "—",
                    hours: s.outEpoch ? minsToText(s.minutes) : "Running",
                    status: s.outEpoch ? "Checked Out" : "Checked In",
                };
            });

        return rows.concat([
            {
                date: "21 Jun",
                day: "Sunday",
                in: "—",
                out: "—",
                hours: "—",
                status: "Present",
            },
            {
                date: "20 Jun",
                day: "Saturday",
                in: "09:04 AM",
                out: "06:12 PM",
                hours: "9h 08m",
                status: "Present",
            },
            {
                date: "19 Jun",
                day: "Friday",
                in: "09:18 AM",
                out: "06:05 PM",
                hours: "8h 47m",
                status: "Late",
            },
            {
                date: "18 Jun",
                day: "Thursday",
                in: "08:58 AM",
                out: "06:02 PM",
                hours: "9h 04m",
                status: "Present",
            },
        ]);
    }
    TC.attHistory = attHistory;

    function toast(msg) {
        var r = $("#toastRoot");
        if (!r) return;
        r.innerHTML =
            '<div class="toast"><span class="ok">' +
            I.check.replace(
                'width="13" height="13"',
                'width="13" height="13" stroke="#fff"',
            ) +
            "</span>" +
            esc(msg) +
            "</div>";
        clearTimeout(toast._t);
        toast._t = setTimeout(function () {
            r.innerHTML = "";
        }, 2600);
    }
    TC.toast = toast;

    // ---------- NAVIGATION ----------
    function pageHref(view) {
        if (window.TEAMIY_ROUTES && window.TEAMIY_ROUTES[view]) {
            return window.TEAMIY_ROUTES[view];
        }

        if (view === "assets") return "/employee-assets";
        if (view === "team") return "/team-sheet";

        return view === "dashboard" ? "/dashboard" : "/" + view;
    }
    TC.pageHref = pageHref;

    // ---------- SHELL RENDER ----------
    function navLink(view, icon, label, badgeHtml) {
        var active = document.body.dataset.page === view;
        return (
            '<a class="nav-item' +
            (active ? " active" : "") +
            '" href="' +
            pageHref(view) +
            '">' +
            icon +
            "<span>" +
            label +
            "</span>" +
            (badgeHtml || "") +
            "</a>"
        );
    }
    function renderNav() {
        var nav = $("#nav");
        if (!nav) return;
        if (nav.querySelector(".nav-item[href]")) return;

        var plc = pendingLeaveCount(),
            unc = unreadNotices();
        var h = "";
        h += '<div class="nav-section">OVERVIEW</div>';
        h += navLink("dashboard", I.dashboard, "Dashboard");
        h += '<div class="nav-section mt">MY WORK</div>';
        h += navLink(
            "leave",
            I.leave,
            "Leave",
            plc ? '<em class="nav-badge">' + plc + "</em>" : "",
        );
        h += navLink("attendance", I.clock, "Attendance");
        h += navLink("tada", I.tada, "TADA");
        h += navLink("resignation", I.resign, "Resignation");
        h += '<div class="nav-section mt">COMPANY</div>';
        h += navLink("team", I.team, "Team Sheet");
        h += navLink("projects", I.projects, "Projects");
        h += navLink("assets", I.assets, "Assets");
        h += navLink("holidays", I.holidays, "Holidays");
        h += navLink(
            "notices",
            I.notices,
            "Notices",
            unc ? '<em class="nav-badge red">' + unc + "</em>" : "",
        );
        h += navLink("meetings", I.meetings, "Meetings");
        h += '<div class="nav-divider"></div>';
        h += navLink("settings", I.settings, "Settings");
        nav.innerHTML = h;
    }
    function renderTopAtt() {
        var b = $("#topAtt");
        if (!b) return;
        if (b.hasAttribute("data-db-attendance")) return;

        var vm = attVM();
        b.className = "att-btn " + vm.btnClass;
        var lbl = $("#topAttLabel");
        if (lbl) lbl.textContent = vm.btnLabel;
    }
    function renderTopBadges() {
        var ti = $("#topInbox"),
            tn = $("#topNotices");
        if (ti) {
            var oc = ti.querySelector(".top-count");
            if (oc) oc.remove();
            if (unreadInbox()) {
                var c = document.createElement("span");
                c.className = "top-count";
                c.textContent = unreadInbox();
                ti.appendChild(c);
            }
        }
        if (tn) {
            var od = tn.querySelector(".top-dot");
            if (od) od.remove();
            if (unreadNotices()) {
                var d = document.createElement("span");
                d.className = "top-dot";
                tn.appendChild(d);
            }
        }
    }
    function updateAttendanceLiveUI() {
        if (!state) return;

        ensureAttSessions();

        var vm = attVM();

        var attSummary = document.getElementById("attSummary");
        var heroAttBtn = document.getElementById("heroAttBtn");
        var attCardValue = document.getElementById("attCardValue");
        var attStatusBadge = document.getElementById("attStatusBadge");
        var attCardSub = document.getElementById("attCardSub");
        var cardAttBtn = document.getElementById("cardAttBtn");

        if (attSummary) attSummary.textContent = vm.summary;
        if (heroAttBtn && !heroAttBtn.hasAttribute("data-db-attendance")) {
            heroAttBtn.textContent = vm.heroLabel;
        }
        if (attCardValue) attCardValue.textContent = vm.cardValue;
        if (attStatusBadge) attStatusBadge.textContent = vm.statusLabel;
        if (attCardSub) attCardSub.textContent = vm.cardSub;
        if (cardAttBtn && !cardAttBtn.hasAttribute("data-db-attendance")) {
            cardAttBtn.textContent = vm.btnLabel;
        }

        var topAtt = document.getElementById("topAtt");
        var topAttLabel = document.getElementById("topAttLabel");

        if (topAtt && !topAtt.hasAttribute("data-db-attendance")) {
            topAtt.className = "att-btn " + vm.btnClass;
        }

        if (topAttLabel && (!topAtt || !topAtt.hasAttribute("data-db-attendance"))) {
            topAttLabel.textContent = vm.btnLabel;
        }
    }

    // ---------- VIEW + MODAL RENDER ----------
    TC.actions = {};
    TC.changeActions = {};
    TC.models = {};
    TC.modals = {};
    TC.inputHooks = [];
    TC.keydownHooks = [];
    TC._view = null;
    TC._after = null;

    function render() {
        var app = $("#app");

        if (app) {
            app.classList.toggle("nav-collapsed", state.navCollapsed);
        }

        renderNav();
        renderTopAtt();
        renderTopBadges();
        updateAttendanceLiveUI();

        var view = $("#view");

        if (view && TC._view) {
            view.innerHTML = TC._view();
        }

        renderModal();

        if (TC._after) {
            TC._after();
        }

        save();
    }
    TC.render = render;

    function renderModal() {
        var root = $("#modalRoot");
        if (!root) return;
        root.innerHTML =
            state.modal && TC.modals[state.modal]
                ? TC.modals[state.modal]()
                : "";
    }
    TC.renderModal = renderModal;
    TC.ov = function (inner) {
        return (
            '<div class="modal-overlay" data-action="overlay-close">' +
            inner +
            "</div>"
        );
    };
    TC.openModal = function (name) {
        state.modal = name;
        renderModal();
    };
    TC.closeModal = function () {
        state.modal = null;
        renderModal();
    };

    // ---------- ATTENDANCE ACTIONS ----------
    function checkIn() {
        ensureAttSessions();

        var alreadyActive = activeAttSession();

        if (alreadyActive) {
            toast("You are already checked in.");
            return;
        }

        var d = new Date();

        var session = {
            id: Date.now(),
            date: todayKey(),

            inTime: fmtTimeWithMs(d),
            outTime: "",

            inEpochMs: d.getTime(),
            outEpochMs: 0,

            durationMs: 0,
        };

        state.att.sessions.push(session);

        state.att.status = "in";
        state.att.inTime = session.inTime;
        state.att.outTime = "";
        state.att.hours = "";
        state.att.inEpoch = session.inEpochMs;

        toast("Checked in at " + session.inTime);
        render();
    }

    function checkOut() {
        ensureAttSessions();

        var active = activeAttSession();

        if (!active) {
            toast("Please check in first.");
            return;
        }

        var d = new Date();

        var durationMs = Math.max(0, d.getTime() - active.inEpochMs);

        active.outTime = fmtTimeWithMs(d);
        active.outEpochMs = d.getTime();
        active.durationMs = durationMs;

        state.att.status = "out";
        state.att.outTime = active.outTime;
        state.att.hours = msToText(totalTodayMs());

        toast("Checked out — " + msToText(durationMs) + " logged");
        render();
    }

    TC.checkIn = checkIn;
    TC.checkOut = checkOut;

    // ---------- SHARED ACTIONS ----------
    Object.assign(TC.actions, {
        "toggle-nav": function () {
            state.navCollapsed = !state.navCollapsed;
            render();
        },
        logout: function () {
            localStorage.removeItem("tc_loggedIn");
            location.href = "/login";
        },
        "att-toggle": function () {
            ensureAttSessions();

            if (activeAttSession()) {
                checkOut();
            } else {
                checkIn();
            }
        },
        nav: function (el) {
            var v = el.getAttribute("data-view");
            if (v) location.href = pageHref(v);
        },
        "close-modal": function () {
            TC.closeModal();
        },
        "overlay-close": function (el, e) {
            if (e.target === el) {
                state.modal = null;
                state.activeTask = null;
                renderModal();
            }
        },
    });

    // ---------- GENERIC MODEL BINDING ----------
    function handleModel(model, value, rerender) {
        if (TC.models[model]) {
            TC.models[model](value);
            return;
        }
        var parts = model.split(".");
        if (parts.length === 2 && state[parts[0]]) {
            state[parts[0]][parts[1]] = value;
            if (rerender) renderModal();
        }
    }
    TC.handleModel = handleModel;

    // ---------- EVENT WIRING ----------
    document.addEventListener("click", function (e) {
        var el = e.target.closest("[data-action]");
        if (!el) return;
        var name = el.getAttribute("data-action");
        var fn = TC.actions[name];
        if (fn) fn(el, e, el.getAttribute("data-idx"));
    });

    document.addEventListener("change", function (e) {
        var el = e.target.closest("[data-action]");
        if (el) {
            var a = el.getAttribute("data-action");
            if (TC.changeActions[a]) {
                TC.changeActions[a](el, e);
                return;
            }
        }
        var m = e.target.getAttribute && e.target.getAttribute("data-model");
        if (m)
            handleModel(
                m,
                e.target.value,
                e.target.getAttribute("data-rerender"),
            );
    });

    document.addEventListener("input", function (e) {
        var m = e.target.getAttribute && e.target.getAttribute("data-model");
        if (m) handleModel(m, e.target.value, null);
        for (var i = 0; i < TC.inputHooks.length; i++) TC.inputHooks[i](e);
    });

    document.addEventListener("keydown", function (e) {
        for (var i = 0; i < TC.keydownHooks.length; i++) TC.keydownHooks[i](e);
    });

    // ---------- BOOT ----------
    // viewFn -> returns the #view innerHTML for this page.
    // opts.after -> optional callback run after every render (e.g. inbox autoscroll).
    TC.boot = function (viewFn, opts) {
        opts = opts || {};
        var page = document.body.dataset.page;
        loadState();
        TC._view = viewFn || null;
        TC._after = opts.after || null;
        render();
    };
    setInterval(function () {
        if (window.TC && TC.state) {
            updateAttendanceLiveUI();
        }
    }, 100);
})();
