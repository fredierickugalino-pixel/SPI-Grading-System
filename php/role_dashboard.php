<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$expectedRole = $dashboardRole ?? "";
$currentRole = strtolower((string) ($_SESSION["role"] ?? ""));
if (empty($_SESSION["user_id"]) || $currentRole !== $expectedRole) {
    header("Location: ../index.php");
    exit();
}

$isAdmin = $expectedRole === "admin";
$roleLabel = $isAdmin ? "Administrator" : "Teacher";
$firstName = htmlspecialchars((string) ($_SESSION["first_name"] ?? $roleLabel), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
$email = htmlspecialchars((string) ($_SESSION["email"] ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
$initial = strtoupper(substr((string) ($_SESSION["first_name"] ?? $roleLabel), 0, 1));
$cards = $isAdmin ? [
    ["♙", "Student accounts", "Review student accounts and their registration details."],
    ["♧", "Teacher accounts", "Manage teacher access to the grading system."],
    ["▤", "Subjects and tracks", "Keep subject and track information organized."],
    ["⚙", "Grading weights", "Review assessment weights used to compute grades."],
    ["▧", "Reports", "Prepare grading summaries and report card information."],
    ["⌁", "Activity", "Review important changes made in the system."],
] : [
    ["▦", "My classes", "Choose a class and subject assigned to you."],
    ["✎", "Enter grades", "Record written work, performance task, and assessment scores."],
    ["▤", "Class records", "Review your students’ grading records."],
    ["▧", "Reports", "Prepare a subject grade summary for your class."],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#174b2d">
    <title><?= $roleLabel ?> Dashboard | St. Peregrine Institute</title>
    <link rel="stylesheet" href="../php/dashboard.css">
</head>
<body>
    <aside class="sidebar">
        <a class="brand" href="#overview"><img src="../logo.png" alt=""><span><strong>ST. PEREGRINE</strong><small>INSTITUTE</small></span></a>
        <p class="nav-heading"><?= strtoupper($roleLabel) ?> PORTAL</p>
        <nav aria-label="Main navigation">
            <a class="nav-link active" href="#overview"><span>⌂</span>Overview</a>
            <a class="nav-link" href="#workspace"><span><?= $isAdmin ? "⚙" : "▤" ?></span><?= $isAdmin ? "Management" : "Teaching workspace" ?></a>
            <a class="nav-link" href="#account"><span>♙</span>My account</a>
        </nav>
        <div class="sidebar-bottom"><a class="nav-link logout" href="../logout.php"><span>↪</span>Log out</a></div>
    </aside>

    <div class="page-shell">
        <header class="topbar">
            <div class="breadcrumbs">Grading System <span>/</span> <strong><?= $roleLabel ?></strong></div>
            <div class="avatar" aria-hidden="true"><?= htmlspecialchars($initial, ENT_QUOTES, "UTF-8") ?></div>
        </header>
        <main class="main-content" id="overview">
            <section class="welcome">
                <p class="eyebrow"><?= strtoupper($roleLabel) ?> PORTAL</p>
                <h1>Welcome, <?= $firstName ?>!</h1>
                <p class="muted"><?= $isAdmin ? "Manage grading system information and school accounts." : "Review your classes and manage your students’ grades." ?></p>
            </section>
            <section class="summary" aria-label="Dashboard summary">
                <article><span class="summary-icon">▤</span><div><small>Workspace</small><strong><?= $isAdmin ? "Administration" : "Teacher tools" ?></strong></div></article>
                <article><span class="summary-icon">✉</span><div><small>Signed in as</small><strong class="email"><?= $email ?></strong></div></article>
            </section>
            <section class="workspace" id="workspace">
                <div class="section-heading"><div><p class="eyebrow">QUICK OVERVIEW</p><h2><?= $isAdmin ? "Administration" : "Teaching workspace" ?></h2><p class="muted">Your main grading system areas are listed below.</p></div></div>
                <div class="card-grid">
                    <?php foreach ($cards as [$icon, $title, $description]): ?>
                        <article class="module-card"><span class="module-icon" aria-hidden="true"><?= $icon ?></span><h3><?= $title ?></h3><p><?= $description ?></p></article>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="account panel" id="account"><div><p class="eyebrow">ACCOUNT</p><h2><?= $firstName ?></h2><p class="muted"><?= $email ?></p></div><a class="button" href="../logout.php">Log out</a></section>
            <footer>© <?= date("Y") ?> St. Peregrine Institute <span>Online Grading System</span></footer>
        </main>
    </div>
</body>
</html>
