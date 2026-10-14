<?php
ini_set("session.use_strict_mode", "1");
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);
session_start();
if (empty($_SESSION["user_id"]) || strtolower($_SESSION["role"] ?? "") !== "student") {
    header("Location: ../index.php");
    exit();
}
$studentName = htmlspecialchars((string) ($_SESSION["first_name"] ?? "Student"), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
$studentInitial = htmlspecialchars(strtoupper(substr((string) ($_SESSION["first_name"] ?? "S"), 0, 1)), ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#174b2d">
    <title>Student Dashboard | St. Peregrine Institute</title>
    <link rel="stylesheet" href="studentdash.css">
    <script src="studentdash-logo.js"></script>
    <script src="studentdash.js" defer></script>
</head>
<body data-student-name="<?= $studentName ?>">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="#overview"><img src="../logo.png" alt=""><span><strong>ST. PEREGRINE</strong><small>INSTITUTE</small></span></a>
        <p class="nav-heading" id="portalLabel">STUDENT PORTAL</p>
        <nav aria-label="Main navigation" id="roleNav"></nav>
        <div class="sidebar-bottom">
            <div class="help-card"><span class="help-icon">?</span><strong>Demo system</strong><p>Sample screens for the grading system. Data is stored only in this browser session.</p></div>
            <a class="nav-link logout" href="../logout.php"><span class="nav-icon">↪</span>Log out</a>
        </div>
    </aside>

    <div class="page-shell">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" type="button" aria-label="Open navigation" aria-expanded="false">☰</button>
            <div class="breadcrumbs">Grading System <span>/</span> <strong id="crumbRole">Student</strong></div>
            <div class="topbar-right"><span class="school-year">S.Y. 2025–2026</span><div class="avatar" id="userAvatar" aria-hidden="true"><?= $studentInitial ?></div></div>
        </header>

        <main class="main-content" id="overview">
            <section class="welcome-row" id="welcomeRow">
                <div><p class="eyebrow">GRADE 12 · SCHOOL YEAR 2025–2026</p><h1 id="welcomeTitle">Welcome, <?= $studentName ?>!</h1><p class="muted" id="welcomeSub">Here’s a snapshot of your academic progress.</p></div>
            </section>

            <section class="role-tools" id="roleTools" aria-live="polite"></section>

            <section class="stats-grid" id="statsGrid" aria-label="Academic summary">
                <article class="stat-card"><span class="stat-icon green">◷</span><div><p>Current semester</p><strong class="stat-text" id="semesterValue">First Semester</strong><small id="semesterNote">Current grading period</small></div></article>
                <article class="stat-card"><span class="stat-icon blue">▦</span><div><p>School year</p><strong class="stat-text" id="schoolYearValue">2025–2026</strong><small>Grade 12 · TVL–ICT</small></div></article>
            </section>

            <div class="content-grid" id="studentContent" hidden>
                <section class="panel grades-panel" id="grades"><div class="panel-heading"><div><h2>Subject grades</h2><p class="muted">TVL–ICT subjects · shared core + ICT</p></div><button class="primary-action" id="downloadPdf">Download report card</button></div>
                    <div class="table-wrap"><table id="gradesTable"><thead><tr><th scope="col">Subject</th><th scope="col">Subject group</th><th scope="col">Grade</th><th scope="col">Status</th></tr></thead><tbody id="gradesBody"></tbody></table></div><p class="table-note">Grades are examples for demonstration, not official records.</p>
                </section>
                <section class="panel schedule-panel" id="schedule"><div class="panel-heading"><div><h2>Today’s schedule</h2><p class="muted">Sample timetable</p></div><span class="today-pill">TODAY</span></div><div class="schedule-list" id="scheduleList"></div><a class="schedule-link" href="#schedule">View full schedule <span aria-hidden="true">→</span></a></section>
            </div>

            <section class="notice" aria-label="Announcement"><span class="notice-icon">i</span><div><strong>Semester assessment reminder</strong><p>Check with your subject teachers for upcoming assessment dates and coverage.</p></div><button type="button" class="notice-dismiss" aria-label="Dismiss reminder">×</button></section>
            <footer>© 2025 St. Peregrine Institute <span>Front-end prototype · No backend connected</span></footer>
        </main>
    </div>
    <div class="scrim" id="scrim"></div>
</body>
</html>
