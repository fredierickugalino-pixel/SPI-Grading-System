<?php
ini_set("session.use_strict_mode", "1");
session_set_cookie_params(["httponly" => true, "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off", "samesite" => "Lax"]);
session_start();
if (empty($_SESSION["password_reset_csrf"])) {
    $_SESSION["password_reset_csrf"] = bin2hex(random_bytes(32));
}
function verifyEscape(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); }

$error = (string) ($_SESSION["verify_pin_error"] ?? "");
$notice = (string) ($_SESSION["verify_pin_notice"] ?? "");
unset($_SESSION["verify_pin_error"], $_SESSION["verify_pin_notice"]);
$resetId = (int) ($_SESSION["password_reset_id"] ?? 0);
$pageValid = $resetId > 0 && !empty($_SESSION["password_reset_email"]) && empty($_SESSION["password_reset_verified"]);
$resetEmail = (string) ($_SESSION["password_reset_email"] ?? "");
$emailParts = explode("@", $resetEmail, 2);
$maskedEmail = count($emailParts) === 2
    ? substr($emailParts[0], 0, 1) . str_repeat("*", max(3, strlen($emailParts[0]) - 1)) . "@" . $emailParts[1]
    : "your email address";
if ($resetId > 0 && !empty($_SESSION["password_reset_verified"])) {
    header("Location: reset_password.php", true, 303);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["password_reset_csrf"], $_POST["csrf_token"] ?? "")) {
        $error = "Your session expired. Refresh the page and try again.";
    } elseif ($resetId < 1 || empty($_SESSION["password_reset_email"])) {
        $error = "Request a reset PIN first.";
        $pageValid = false;
    } else {
        $pin = trim($_POST["pin"] ?? "");
        try {
            require __DIR__ . "/../php/database.php";
            $lookup = $conn->prepare("SELECT pin_hash, attempts FROM password_reset_pins WHERE reset_id = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
            $lookup->bind_param("i", $resetId);
            $lookup->execute();
            $reset = $lookup->get_result()->fetch_assoc();
            $lookup->close();

            if (!preg_match("/^[0-9]{6}$/", $pin) || !$reset || (int) $reset["attempts"] >= 5 || !password_verify($pin, $reset["pin_hash"])) {
                if ($reset && (int) $reset["attempts"] < 5) {
                    $attempt = $conn->prepare("UPDATE password_reset_pins SET attempts = attempts + 1 WHERE reset_id = ?");
                    $attempt->bind_param("i", $resetId);
                    $attempt->execute();
                    $attempt->close();
                    if ((int) $reset["attempts"] + 1 >= 5) {
                        $expire = $conn->prepare("UPDATE password_reset_pins SET used_at = NOW() WHERE reset_id = ?");
                        $expire->bind_param("i", $resetId);
                        $expire->execute();
                        $expire->close();
                    }
                }
                $_SESSION["verify_pin_error"] = "That PIN is incorrect or expired. Check the code and try again.";
                $conn->close();
                header("Location: verify_reset_pin.php", true, 303);
                exit();
            }

            $markVerified = $conn->prepare("UPDATE password_reset_pins SET verified_at = NOW() WHERE reset_id = ? AND used_at IS NULL AND expires_at > NOW()");
            $markVerified->bind_param("i", $resetId);
            $markVerified->execute();
            $markVerified->close();
            $conn->close();
            $_SESSION["password_reset_verified"] = true;
            header("Location: reset_password.php", true, 303);
            exit();
        } catch (Throwable $exception) {
            if (isset($conn) && $conn instanceof mysqli) {
                try { $conn->rollback(); } catch (Throwable $ignored) {}
                $conn->close();
            }
            error_log("Password reset PIN verification error: " . $exception->getMessage());
            $error = "PIN confirmation is temporarily unavailable. Please try again.";
        }
    }
}

if ($pageValid) {
    try {
        require __DIR__ . "/../php/database.php";
        $check = $conn->prepare("SELECT attempts FROM password_reset_pins WHERE reset_id = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
        $check->bind_param("i", $resetId);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();
        $conn->close();
        if (!$row || (int) $row["attempts"] >= 5) {
            $pageValid = false;
            unset($_SESSION["password_reset_id"], $_SESSION["password_reset_email"], $_SESSION["password_reset_verified"]);
            $error = "Your PIN expired or has too many attempts. Request a new one.";
        }
    } catch (Throwable $exception) {
        if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
        error_log("Password reset PIN page error: " . $exception->getMessage());
        $pageValid = false;
        $error = "PIN confirmation is temporarily unavailable. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Confirm Reset PIN | St. Peregrine Institute</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <main class="auth-card">
        <img src="../logo.png" alt="St. Peregrine Institute Logo" class="school-logo">
        <h1>CONFIRM YOUR PIN</h1>
        <p class="subtitle">Enter the 6-digit code sent to <strong><?= verifyEscape($maskedEmail) ?></strong>. Check your spam folder if you don’t see it.</p>
        <?php if ($error !== ""): ?><div class="message error" role="alert"><?= verifyEscape($error) ?></div><?php endif; ?>
        <?php if ($notice !== ""): ?><div class="message success" role="status"><?= verifyEscape($notice) ?></div><?php endif; ?>
        <?php if ($pageValid): ?>
            <form method="POST" action="verify_reset_pin.php">
                <input type="hidden" name="csrf_token" value="<?= verifyEscape($_SESSION["password_reset_csrf"]) ?>">
                <label for="pin">Email PIN</label>
                <input type="text" id="pin" name="pin" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Enter the 6-digit PIN" required autofocus>
                <button type="submit" class="submit-button">CONFIRM PIN</button>
            </form>
            <form method="POST" action="forgot_password.php" class="resend-form">
                <input type="hidden" name="csrf_token" value="<?= verifyEscape($_SESSION["password_reset_csrf"]) ?>">
                <input type="hidden" name="email" value="<?= verifyEscape((string) $_SESSION["password_reset_email"]) ?>">
                <button type="submit" class="submit-button">SEND ANOTHER PIN</button>
            </form>
        <?php else: ?>
            <p class="auth-switch"><a href="forgot_password.php">Request a new PIN</a></p>
        <?php endif; ?>
        <p class="auth-switch"><a href="../index.php">Return to login</a></p>
    </main>
</body>
</html>
