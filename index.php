<?php
ini_set("session.use_strict_mode", "1");
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);
session_start();

if (!isset($_SESSION["login_csrf"])) {
    $_SESSION["login_csrf"] = bin2hex(random_bytes(32));
}

$error = "";
$showCodeForm = !empty($_SESSION["pending_student_id"]);
$notice = "";

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function clearPendingLogin(): void
{
    unset(
        $_SESSION["pending_student_id"],
        $_SESSION["pending_email"],
        $_SESSION["pending_name"],
        $_SESSION["pending_pin_hash"],
        $_SESSION["pending_pin_expires"],
        $_SESSION["pending_pin_attempts"]
    );
}

function sendStudentPin(string $email, string $name, string $pin): bool
{
    require_once __DIR__ . "/php/mailer.php";
    $safeName = escapeHtml($name);
    $safePin = escapeHtml($pin);
    return sendSystemEmail(
        $email,
        $name,
        "Your student login PIN",
        "<p>Hello " . $safeName . ",</p><p>Your student login PIN is <strong style=\"font-size:24px;letter-spacing:4px\">" . $safePin . "</strong>.</p><p>This code expires in 5 minutes. If you did not request it, you can ignore this email.</p>",
        "Your student login PIN is {$pin}. It expires in 5 minutes."
    );
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["login_csrf"], $_POST["csrf_token"] ?? "")) {
        $error = "Your session expired. Refresh the page and try again.";
    } elseif (($_POST["action"] ?? "login") === "verify_pin") {
        if (empty($_SESSION["pending_student_id"])) {
            $error = "Please sign in again to request a new PIN.";
            $showCodeForm = false;
        } elseif (time() > (int) $_SESSION["pending_pin_expires"]) {
            clearPendingLogin();
            $showCodeForm = false;
            $error = "That PIN expired. Sign in again to receive a new one.";
        } elseif ((int) $_SESSION["pending_pin_attempts"] >= 5) {
            clearPendingLogin();
            $showCodeForm = false;
            $error = "Too many incorrect PIN attempts. Sign in again to receive a new one.";
        } else {
            $pin = trim($_POST["pin"] ?? "");
            if (!preg_match("/^[0-9]{6}$/", $pin) || !password_verify($pin, $_SESSION["pending_pin_hash"])) {
                $_SESSION["pending_pin_attempts"]++;
                $error = "The PIN is incorrect. Please try again.";
                $showCodeForm = true;
            } else {
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int) $_SESSION["pending_student_id"];
                $_SESSION["email"] = $_SESSION["pending_email"];
                $_SESSION["role"] = "student";
                $_SESSION["first_name"] = $_SESSION["pending_name"];
                clearPendingLogin();
                header("Location: student/studentdash.php");
                exit();
            }
        }
    } else {
        clearPendingLogin();
        $loginId = trim($_POST["login_id"] ?? "");
        $password = $_POST["password"] ?? "";

        try {
            require __DIR__ . "/php/database.php";
            $statement = $conn->prepare("SELECT user_id, lrn, first_name, email, password, role FROM users WHERE LOWER(email) = LOWER(?) OR lrn = ? LIMIT 1");
            $statement->bind_param("ss", $loginId, $loginId);
            $statement->execute();
            $user = $statement->get_result()->fetch_assoc();
            $statement->close();
            $conn->close();

            if (!$user || !password_verify($password, $user["password"])) {
                $error = "Invalid LRN/email or password.";
            } elseif (!in_array(strtolower($user["role"]), ["student", "teacher", "admin"], true)) {
                $error = "This account has an invalid role. Please contact the administrator.";
            } elseif (strtolower($user["role"]) !== "student") {
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int) $user["user_id"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = strtolower($user["role"]);
                $_SESSION["first_name"] = $user["first_name"];
                $staffDashboard = $_SESSION["role"] === "teacher"
                    ? "teacher/teacherdash.php"
                    : "admin/admindash.php";
                header("Location: " . $staffDashboard);
                exit();
            } else {
                $pin = (string) random_int(100000, 999999);
                session_regenerate_id(true);
                $_SESSION["login_csrf"] = bin2hex(random_bytes(32));
                $_SESSION["pending_student_id"] = (int) $user["user_id"];
                $_SESSION["pending_email"] = $user["email"];
                $_SESSION["pending_name"] = $user["first_name"];
                $_SESSION["pending_pin_hash"] = password_hash($pin, PASSWORD_DEFAULT);
                $_SESSION["pending_pin_expires"] = time() + 300;
                $_SESSION["pending_pin_attempts"] = 0;

                if (sendStudentPin($user["email"], $user["first_name"], $pin)) {
                    $notice = "We sent a 6-digit PIN to your student email. It expires in 5 minutes.";
                    $showCodeForm = true;
                } else {
                    clearPendingLogin();
                    $error = "We could not send the PIN. Check the mail configuration and try again.";
                    $showCodeForm = false;
                }
            }
        } catch (Throwable $exception) {
            error_log("Login error: " . $exception->getMessage());
            $error = "Login is temporarily unavailable. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | St. Peregrine Institute</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-card">
        <img src="logo.png" alt="St. Peregrine Institute Logo" class="school-logo">
        <h1>ST. PEREGRINE INSTITUTE</h1>
        <p class="subtitle">Online Grading System</p>

        <?php if ($error !== ""): ?>
            <div class="message error" role="alert"><?= escapeHtml($error) ?></div>
        <?php endif; ?>
        <?php if ($notice !== ""): ?>
            <div class="message success" role="status"><?= escapeHtml($notice) ?></div>
        <?php endif; ?>

        <?php if ($showCodeForm): ?>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="verify_pin">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION["login_csrf"]) ?>">
                <label for="pin">Email PIN</label>
                <input type="text" id="pin" name="pin" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Enter the 6-digit PIN" required autofocus>
                <button type="submit" class="submit-button">VERIFY AND CONTINUE</button>
            </form>
            <p class="auth-switch"><a href="logout.php">Cancel and return to login</a></p>
        <?php else: ?>
            <form method="POST" action="index.php">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION["login_csrf"]) ?>">
                <label for="login_id">LRN or Email Address</label>
                <input type="text" id="login_id" name="login_id" placeholder="Enter your LRN or email" autocomplete="username" required>
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    <button type="button" class="show-password" data-target="password">Show</button>
                </div>
                <div class="login-options"><a class="forgot-link" href="forgotpassword/forgot_password.php">Forgot password?</a></div>
                <button type="submit" class="submit-button">LOGIN</button>
            </form>
            <p class="auth-switch">New student? <a href="register.html">Register here</a></p>
        <?php endif; ?>
    </main>
    <script src="script.js"></script>
</body>
</html>
