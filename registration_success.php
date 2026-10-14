<?php
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Location: register.html", true, 303);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url=index.php">
    <title>Registration Successful | St. Peregrine Institute</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <main class="auth-card register-card success-card" aria-live="polite">
        <img src="logo.png" alt="St. Peregrine Institute Logo" class="school-logo">
        <div class="success-mark" aria-hidden="true">✓</div>
        <h1>REGISTRATION SUCCESSFUL</h1>
        <p class="subtitle">Your student account has been created successfully.</p>
        <p class="redirect-countdown">Taking you to login in <strong id="countdown">3</strong> seconds.</p>
        <a class="submit-button success-link" href="index.php">GO TO LOGIN NOW</a>
    </main>
    <script>
        let seconds = 3;
        const countdown = document.getElementById("countdown");
        const timer = window.setInterval(() => {
            seconds -= 1;
            countdown.textContent = String(seconds);
            if (seconds <= 0) {
                window.clearInterval(timer);
                window.location.replace("index.php");
            }
        }, 1000);
    </script>
</body>
</html>
