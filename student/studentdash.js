const subjects = [
    { subject: "Personal Development", track: "Core Subjects" },
    { subject: "Physical Science", track: "Core Subjects" },
    { subject: "Physical Education and Health 3", track: "Core Subjects" },
    { subject: "Contemporary Philippine Arts from the Regions", track: "Core Subjects" },
    { subject: "Servant Leadership", track: "Core Subjects" },
    { subject: "Practical Research 2", track: "Core Subjects" },
    { subject: "Filipino sa Piling Larang", track: "Core Subjects" },
    { subject: "Creative Language", track: "HUMSS" },
    { subject: "Philippine Government", track: "HUMSS" },
    { subject: "Contact Center Services 3", track: "ICT" },
    { subject: "Technical Drafting 3", track: "ICT" },
    { subject: "Fundamentals of Accountancy, Business, and Management", track: "ABM" },
    { subject: "Business Finance", track: "ABM" },
    { subject: "General Chemistry", track: "STEM" },
    { subject: "General Physics", track: "STEM" },
    { subject: "Cookery", track: "HE" },
    { subject: "Food and Beverage Services", track: "HE" }
];
const teacherNames = ["Ms. A. Reyes", "Mr. D. Santos", "Mrs. L. Cruz", "Mr. J. Garcia", "Ms. M. Lopez", "Mrs. C. Ramos", "Mr. P. Flores", "Ms. R. Dela Cruz"];
const initialGrades = [95, 92, 96, 93, 94, 91, 90, 92, 94, 93, 91, 95, 90, 92, 94, 96, 93];
const terms = {
    1: { grades: subjects.map((item, index) => ({ ...item, teacher: teacherNames[index % teacherNames.length], grade: String(initialGrades[index]), status: "Passed" })), schedule: [
        { time: "8:00 AM", end: "9:00 AM", subject: "Personal Development", room: "Room 204 · Ms. Reyes", style: "english" },
        { time: "9:15 AM", end: "10:15 AM", subject: "Physical Science", room: "Science Lab · Mrs. Cruz", style: "science" },
        { time: "10:30 AM", end: "11:30 AM", subject: "Practical Research 2", room: "Room 301 · Mr. Santos", style: "math" },
        { time: "1:00 PM", end: "2:00 PM", subject: "Physical Education and Health 3", room: "Gymnasium · Mrs. Ramos", style: "pe" }
    ] },
    2: { grades: subjects.map((item, index) => ({ ...item, teacher: teacherNames[index % teacherNames.length], grade: "—", status: "Pending" })), schedule: [
        { time: "8:00 AM", end: "9:00 AM", subject: "Personal Development", room: "Room 204 · Ms. Reyes", style: "english" },
        { time: "9:15 AM", end: "10:15 AM", subject: "Physical Science", room: "Science Lab · Mrs. Cruz", style: "science" },
        { time: "10:30 AM", end: "11:30 AM", subject: "Practical Research 2", room: "Room 301 · Mr. Santos", style: "math" },
        { time: "1:00 PM", end: "2:00 PM", subject: "Physical Education and Health 3", room: "Gymnasium · Mrs. Ramos", style: "pe" }
    ] }
};
let selectedTerm = "1";
let activeRole = "student";
const $ = (id) => document.getElementById(id);

const roleConfig = {
    student: { label: "Student", portal: "STUDENT PORTAL", name: `Welcome, ${document.body.dataset.studentName || "Student"}!`, sub: "TVL–ICT · Shared core and ICT subjects for this semester.", initials: "S", nav: [["overview", "Overview", "⌂"], ["subjects", "Subjects", "▤"]] },
    teacher: { label: "Teacher", portal: "TEACHER PORTAL", name: "Welcome, Ms. Reyes", sub: "Select a class, review records, and enter student scores.", initials: "AR", nav: [["overview", "Overview", "⌂"], ["teacherTools", "Encode scores", "✎"], ["teacherTools", "View class record", "▤"]] },
    admin: { label: "Admin", portal: "ADMINISTRATION", name: "Welcome, Administrator", sub: "Manage accounts, grading setup, and school reports.", initials: "AD", nav: [["overview", "Overview", "⌂"], ["adminTools", "Accounts", "♙"], ["adminTools", "Grading setup", "⚙"], ["adminTools", "Reports & logs", "▧"]] }
};

function renderRole() {
    const config = roleConfig[activeRole];
    $("crumbRole").textContent = config.label;
    $("portalLabel").textContent = config.portal;
    $("welcomeTitle").textContent = config.name;
    $("welcomeSub").textContent = config.sub;
    $("userAvatar").textContent = config.initials;
    $("roleNav").replaceChildren(...config.nav.map(([target, label, icon], index) => {
        const link = document.createElement("a");
        link.className = `nav-link${index === 0 ? " active" : ""}`;
        link.href = `#${target}`;
        if (index === 0) link.setAttribute("aria-current", "page");
        link.innerHTML = `<span class="nav-icon" aria-hidden="true">${icon}</span>${label}`;
        link.addEventListener("click", (event) => {
            if (activeRole === "student") { event.preventDefault(); showStudentView(target); }
            closeMenu();
        });
        return link;
    }));
    $("studentContent").hidden = activeRole !== "student";
    $("statsGrid").hidden = activeRole !== "student";
    renderTools();
    if (activeRole === "student") { renderStudent(); showStudentView("overview"); }
}

function renderTools() {
    const host = $("roleTools");
    host.replaceChildren();
    if (activeRole === "student") { host.hidden = true; return; }
    host.hidden = false;
    const heading = document.createElement("div");
    heading.className = "tools-heading";
    heading.innerHTML = `<div><p class="eyebrow">${activeRole === "admin" ? "SYSTEM MANAGEMENT" : "CLASSROOM TOOLS"}</p><h2>${activeRole === "admin" ? "Administration workspace" : "Teacher workspace"}</h2><p class="muted">Choose an action to open its sample front-end screen.</p></div>`;
    host.append(heading);
    if (activeRole === "admin") {
        const cards = [
            ["Manage teacher accounts", "Create and maintain teacher access", "Add teacher"],
            ["Manage student accounts", "Review student account list", "Add student"],
            ["Configure grading weights", "Set assessment percentage weights", "Configure weights"],
            ["Manage subjects and sections", "Organize Grade 12 subjects", "Manage subjects"],
            ["View activity logs", "See recent system actions", "Open activity log"],
            ["Generate reports", "Prepare class and grade summaries", "Generate report"]
        ];
        const grid = document.createElement("div");
        grid.className = "module-grid";
        cards.forEach(([title, description, action]) => {
            const card = document.createElement("article"); card.className = "module-card";
            card.innerHTML = `<span class="module-mark">▤</span><h3>${title}</h3><p>${description}</p><button type="button" class="small-action">${action} <span aria-hidden="true">→</span></button>`;
            card.querySelector("button").addEventListener("click", () => openAdminAction(title, action));
            grid.append(card);
        });
        host.append(grid);
    } else {
        const card = document.createElement("section"); card.className = "panel teacher-panel";
        card.innerHTML = `<div class="panel-heading"><div><h2>Class scorebook</h2><p class="muted">Select a subject and section to encode or review scores.</p></div><span class="today-pill">GRADE 12</span></div><div class="teacher-controls"><label>Subject<select id="teacherSubject"></select></label><label>Section<select id="classSelect"><option>12 - HUMSS</option><option>12 - STEM</option><option>12 - ABM</option><option>12 - ICT</option><option>12 - HE</option></select></label></div><div class="score-entry"><label for="scoreInput">Assessment score (%)</label><input id="scoreInput" type="number" min="0" max="100" placeholder="Enter score from 0 to 100"><button type="button" class="primary-action" id="saveScore">Encode score</button><button type="button" class="secondary-action" id="viewRecord">View class record</button></div><p class="inline-feedback" id="teacherFeedback" aria-live="polite">Sample class: Grade 12 · First Semester. Encoded scores update this demo only.</p><div class="class-record" id="classRecord" hidden></div>`;
        const select = card.querySelector("#teacherSubject");
        subjects.forEach((item, index) => { const option = document.createElement("option"); option.value = String(index); option.textContent = item.subject; select.append(option); });
        card.querySelector("#saveScore").addEventListener("click", saveScore);
        card.querySelector("#viewRecord").addEventListener("click", () => { $("classRecord").hidden = false; renderRecord(); });
        host.append(card);
    }
}

function openAdminAction(title, action) {
    const old = document.querySelector(".admin-action-panel"); if (old) old.remove();
    const panel = document.createElement("section"); panel.className = "panel admin-action-panel";
    const data = title.includes("grading") ? `<label>Classwork <input type="number" min="0" max="100" value="40"></label><label>Performance tasks <input type="number" min="0" max="100" value="40"></label><label>Quarterly assessment <input type="number" min="0" max="100" value="20"></label>` : title.includes("subjects") ? `<label>Subject / section name <input id="adminEntry" type="text" placeholder="e.g. General Biology"></label><label>Track<select><option>Core Subjects</option><option>HUMSS</option><option>ICT</option><option>ABM</option><option>STEM</option><option>HE</option></select></label>` : title.includes("reports") ? `<label>Report type<select><option>Grade summary</option><option>Class list</option><option>Subject performance</option></select></label><label>Semester<select><option>First Semester</option><option>Second Semester</option></select></label>` : title.includes("logs") ? `<p class="log-row">Today · Admin opened the administration workspace</p><p class="log-row">Today · Sample grades loaded for Grade 12</p><p class="log-row">Today · Semester set to First Semester</p>` : `<label>Full name <input id="adminEntry" type="text" placeholder="Enter name"></label><label>LRN / Email <input type="text" placeholder="Enter student LRN or email"></label><label>Section<select><option>12 - HUMSS</option><option>12 - STEM</option><option>12 - ABM</option><option>12 - ICT</option><option>12 - HE</option></select></label>`;
    panel.innerHTML = `<div class="panel-heading"><div><h2>${title}</h2><p class="muted">Sample ${title.toLowerCase()} screen · demo data only</p></div><button type="button" class="close-panel" aria-label="Close panel">×</button></div><div class="admin-form">${data}<button class="primary-action" type="button" id="adminSave">${action}</button><p class="inline-feedback" id="adminFeedback" aria-live="polite"></p></div>`;
    panel.querySelector(".close-panel").addEventListener("click", () => panel.remove());
    panel.querySelector("#adminSave").addEventListener("click", () => {
        const inputs = [...panel.querySelectorAll("input[type=text]")];
        const value = inputs[0]?.value.trim();
        const feedback = panel.querySelector("#adminFeedback");
        if (inputs.length && !value) { feedback.textContent = "Enter a name or subject to continue."; inputs[0].focus(); return; }
        if (title.includes("weights")) {
            const weights = [...panel.querySelectorAll('input[type="number"]')].map((input) => Number(input.value));
            feedback.textContent = weights.reduce((sum, weight) => sum + weight, 0) === 100 ? "Weights total 100%. Saved in this demo." : "Weights should add up to 100%. Adjust the values.";
            return;
        }
        if (title.includes("reports")) { feedback.textContent = "Your sample report is ready in this demo workspace."; return; }
        if (title.includes("logs")) { feedback.textContent = "Showing recent demo activity."; return; }
        feedback.textContent = `“${value}” added to this temporary demo screen.`;
    });
    $("roleTools").append(panel);
    panel.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function renderStudent() {
    const term = terms[selectedTerm];
    const studentGrades = term.grades.filter((item) => item.track === "Core Subjects" || item.track === "ICT");
    $("semesterValue").textContent = selectedTerm === "1" ? "First Semester" : "Second Semester";
    $("semesterNote").textContent = "Selected grading period";
    $("gradesBody").replaceChildren(...studentGrades.map((item) => {
        const row = document.createElement("tr");
        const values = [item.subject, item.track, item.grade];
        values.forEach((value, index) => { const cell = document.createElement("td"); cell.textContent = value; if (index === 2 && item.status === "Passed") cell.className = "grade"; row.append(cell); });
        const statusCell = document.createElement("td"); const badge = document.createElement("span"); badge.className = item.status === "Passed" ? "status" : "status pending"; badge.textContent = item.status; statusCell.append(badge); row.append(statusCell);
        return row;
    }));
    $("scheduleList").replaceChildren(...term.schedule.map((item) => {
        const entry = document.createElement("article"); entry.className = "schedule-item";
        const time = document.createElement("div"); time.className = "schedule-time"; const start = document.createElement("strong"); start.textContent = item.time; time.append(start, document.createTextNode(item.end));
        const details = document.createElement("div"); details.className = `class-info ${item.style}`; const subject = document.createElement("strong"); subject.textContent = item.subject; const room = document.createElement("span"); room.textContent = item.room; details.append(subject, room); entry.append(time, details); return entry;
    }));
}

function showStudentView(view) {
    const isSubjects = view === "subjects";
    $("welcomeRow").hidden = isSubjects;
    $("statsGrid").hidden = isSubjects;
    $("studentContent").hidden = !isSubjects;
    $("crumbRole").textContent = isSubjects ? "Subjects" : "Overview";
    document.querySelectorAll("#roleNav .nav-link").forEach((link) => {
        const active = link.getAttribute("href") === `#${view}`;
        link.classList.toggle("active", active);
        if (active) link.setAttribute("aria-current", "page");
        else link.removeAttribute("aria-current");
    });
    if (isSubjects) { $("welcomeTitle").textContent = "My Subjects"; $("welcomeSub").textContent = "View your subject grades and class schedule."; }
    else { $("welcomeTitle").textContent = roleConfig.student.name; $("welcomeSub").textContent = roleConfig.student.sub; }
}

function saveScore() {
    const score = Number($("scoreInput").value);
    if (!$("scoreInput").value || score < 0 || score > 100) { $("teacherFeedback").textContent = "Enter a valid score between 0 and 100."; $("scoreInput").focus(); return; }
    const index = Number($("teacherSubject").value);
    terms[selectedTerm].grades[index].grade = String(Math.round(score));
    terms[selectedTerm].grades[index].status = score >= 75 ? "Passed" : "Not Passed";
    $("teacherFeedback").textContent = `Score saved for ${subjects[index].subject} · ${$("classSelect").value}. The sample grade record was updated.`;
    renderRecord();
}
function renderRecord() {
    const record = $("classRecord");
    const rows = terms[selectedTerm].grades.slice(0, 8);
    record.innerHTML = `<h3>Class record · ${$("classSelect").value}</h3><div class="table-wrap"><table><thead><tr><th>Subject</th><th>Teacher</th><th>Sample grade</th><th>Status</th></tr></thead><tbody>${rows.map((item) => `<tr><td>${item.subject}</td><td>${item.teacher}</td><td>${item.grade}</td><td>${item.status}</td></tr>`).join("")}</tbody></table></div><p class="table-note">Auto-computed grade preview uses entered percentage as a sample final grade.</p>`;
}

function ascii(value) {
    return String(value).normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^\x20-\x7E]/g, "-");
}

function pdfText(value) {
    return ascii(value).replace(/\\/g, "\\\\").replace(/\(/g, "\\(").replace(/\)/g, "\\)");
}

function makePdfBytes(imageBytes, imageWidth, imageHeight, pdfContent) {
    const encoder = new TextEncoder();
    const asBytes = (value) => encoder.encode(value);
    const contentBytes = asBytes(pdfContent);
    const objects = [
        asBytes("<< /Type /Catalog /Pages 2 0 R >>"),
        asBytes("<< /Type /Pages /Kids [3 0 R] /Count 1 >>"),
        asBytes("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> /XObject << /Logo 5 0 R >> >> /Contents 6 0 R >>"),
        asBytes("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>"),
        joinBytes([asBytes(`<< /Type /XObject /Subtype /Image /Width ${imageWidth} /Height ${imageHeight} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${imageBytes.length} >>\nstream\n`), imageBytes, asBytes("\nendstream")]),
        joinBytes([asBytes(`<< /Length ${contentBytes.length} >>\nstream\n`), contentBytes, asBytes("\nendstream")])
    ];
    const chunks = [asBytes("%PDF-1.4\n")];
    const offsets = [0];
    let byteLength = chunks[0].length;
    objects.forEach((object, index) => {
        const prefix = asBytes(`${index + 1} 0 obj\n`);
        const suffix = asBytes("\nendobj\n");
        offsets.push(byteLength);
        chunks.push(prefix, object, suffix);
        byteLength += prefix.length + object.length + suffix.length;
    });
    const xrefOffset = byteLength;
    let xref = `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
    offsets.slice(1).forEach((offset) => { xref += `${String(offset).padStart(10, "0")} 00000 n \n`; });
    xref += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`;
    chunks.push(asBytes(xref));
    return joinBytes(chunks);
}

function joinBytes(chunks) {
    const output = new Uint8Array(chunks.reduce((total, chunk) => total + chunk.length, 0));
    let position = 0;
    chunks.forEach((chunk) => { output.set(chunk, position); position += chunk.length; });
    return output;
}

async function downloadReportCard() {
    const button = $("downloadPdf");
    button.disabled = true;
    button.textContent = "Preparing PDF...";
    try {
        const term = terms[selectedTerm];
        const reportGrades = term.grades.filter((item) => item.track === "Core Subjects" || item.track === "ICT");
        const passingGrades = reportGrades.filter((item) => item.status === "Passed");
        const average = passingGrades.length ? (passingGrades.reduce((sum, item) => sum + Number(item.grade), 0) / passingGrades.length).toFixed(1) : "Pending";
        const semester = selectedTerm === "1" ? "First Semester" : "Second Semester";
        const commands = [];
        const text = (x, y, size, value, font = "F1") => commands.push(`0 0 0 rg BT /${font} ${size} Tf ${x} ${y} Td (${pdfText(value)}) Tj ET`);
        commands.push("q 72 0 0 72 50 735 cm /Logo Do Q");
        text(143, 784, 17, "ST. PEREGRINE INSTITUTE");
        text(143, 764, 9, "GRADE 12 - STUDENT REPORT CARD");
        commands.push("0.09 0.29 0.18 RG 1.5 w 50 724 m 545 724 l S");
        text(50, 697, 10, "Student: Jamie Dela Cruz");
        text(320, 697, 10, "Track: TVL-ICT");
        text(50, 678, 10, `Semester: ${semester}`);
        text(320, 678, 10, "School Year: 2025-2026");
        text(50, 643, 13, "SUBJECT GRADES");
        commands.push("0.91 0.95 0.92 rg 48 613 499 20 re f");
        text(54, 620, 8, "SUBJECT");
        text(348, 620, 8, "SUBJECT GROUP");
        text(455, 620, 8, "GRADE");
        text(502, 620, 8, "STATUS");
        let y = 601;
        reportGrades.forEach((item, index) => {
            const rowY = y - index * 29;
            commands.push(`0.83 0.87 0.84 RG 48 ${rowY - 8} m 547 ${rowY - 8} l S`);
            text(54, rowY, 8, item.subject);
            text(348, rowY, 8, item.track === "Core Subjects" ? "Core" : item.track);
            text(455, rowY, 8, item.grade);
            text(502, rowY, 8, item.status);
        });
        const bottom = y - reportGrades.length * 29 - 12;
        commands.push(`0.55 0.62 0.56 RG 1 w 315 ${bottom} m 547 ${bottom} l S`);
        text(365, bottom - 24, 11, "FINAL AVERAGE");
        text(493, bottom - 26, 15, average);
        text(50, 50, 8, "Sample front-end report card - not an official school record.");

        const logo = new Image();
        logo.src = window.STUDENTDASH_LOGO_DATA;
        await new Promise((resolve, reject) => { logo.onload = resolve; logo.onerror = reject; });
        const canvas = document.createElement("canvas");
        canvas.width = logo.naturalWidth;
        canvas.height = logo.naturalHeight;
        const context = canvas.getContext("2d");
        context.fillStyle = "#ffffff";
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(logo, 0, 0);
        const base64 = canvas.toDataURL("image/jpeg", 0.88).split(",")[1];
        const binary = atob(base64);
        const imageBytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
        const pdf = makePdfBytes(imageBytes, canvas.width, canvas.height, commands.join("\n"));
        const url = URL.createObjectURL(new Blob([pdf], { type: "application/pdf" }));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `Jamie-Dela-Cruz-${semester.replace(/\s/g, "-")}-Report-Card.pdf`);
        document.body.append(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 10000);
    } catch (error) {
        button.textContent = error.message || "PDF download failed - retry";
        button.disabled = false;
        return;
    }
    button.textContent = "PDF downloaded";
    window.setTimeout(() => { button.textContent = "Download PDF report card"; button.disabled = false; }, 2200);
}

function closeMenu() { $("sidebar").classList.remove("open"); $("scrim").classList.remove("visible"); $("menuToggle").setAttribute("aria-expanded", "false"); }
$("menuToggle").addEventListener("click", () => { const open = $("sidebar").classList.toggle("open"); $("scrim").classList.toggle("visible", open); $("menuToggle").setAttribute("aria-expanded", String(open)); });
$("scrim").addEventListener("click", closeMenu);
$("downloadPdf").addEventListener("click", downloadReportCard);
document.querySelector(".notice-dismiss").addEventListener("click", () => { document.querySelector(".notice").hidden = true; });
renderRole();


