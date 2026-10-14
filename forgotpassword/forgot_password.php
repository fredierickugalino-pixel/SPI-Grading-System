<?php
ini_set("session.use_strict_mode", "1");
session_set_cookie_params(["httponly" => true, "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off", "samesite" => "Lax"]);
session_start();
if (empty($_SESSION["password_reset_csrf"])) {
    $_SESSION["password_reset_csrf"] = bin2hex(random_bytes(32));
}

function forgotEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

$error = (string) ($_SESSION["forgot_password_error"] ?? "");
unset($_SESSION["forgot_password_error"]);
$emailValue = (string) ($_SESSION["forgot_password_email_value"] ?? "");
unset($_SESSION["forgot_password_email_value"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["password_reset_csrf"], $_POST["csrf_token"] ?? "")) {
        $_SESSION["forgot_password_error"] = "Your session expired. Refresh the page and try again.";
        header("Location: forgot_password.php", true, 303);
        exit();
    }

    $email = strtolower(trim($_POST["email"] ?? ""));
    $_SESSION["forgot_password_email_value"] = $email;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $_SESSION["forgot_password_error"] = "Enter a valid email address.";
        header("Location: forgot_password.php", true, 303);
        exit();
    }

    try {
        require __DIR__ . "/../php/database.php";
        $userQuery = $conn->prepare("SELECT user_id, first_name, email FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $userQuery->bind_param("s", $email);
        $userQuery->execute();
        $user = $userQuery->get_result()->fetch_assoc();
        $userQuery->close();

        if (!$user) {
            unset($_SESSION["password_reset_id"], $_SESSION["password_reset_email"], $_SESSION["password_reset_verified"]);
            $_SESSION["forgot_password_error"] = "That email address is not registered.";
            $conn->close();
            header("Location: forgot_password.php", true, 303);
            exit();
        }

        $recentQuery = $conn->prepare("SELECT reset_id FROM password_reset_pins WHERE user_id = ? AND used_at IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE) ORDER BY reset_id DESC LIMIT 1");
        $recentQuery->bind_param("i", $user["user_id"]);
        $recentQuery->execute();
        $recentRequest = $recentQuery->get_result()->fetch_assoc();
        $recentQuery->close();

        if ($recentRequest) {
            $_SESSION["password_reset_id"] = (int) $recentRequest["reset_id"];
            $_SESSION["password_reset_email"] = (string) $user["email"];
            $_SESSION["password_reset_verified"] = false;
            $_SESSION["verify_pin_notice"] = "A PIN was sent recently. Check your email for the latest code.";
            $conn->close();
            header("Location: verify_reset_pin.php", true, 303);
            exit();
        }

        $pin = (string) random_int(100000, 999999);
        $pinHash = password_hash($pin, PASSWORD_DEFAULT);
        $conn->begin_transaction();
        $invalidate = $conn->prepare("UPDATE password_reset_pins SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL");
        $invalidate->bind_param("i", $user["user_id"]);
        $invalidate->execute();
        $invalidate->close();

        $insert = $conn->prepare("INSERT INTO password_reset_pins (user_id, pin_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        $insert->bind_param("is", $user["user_id"], $pinHash);
        $insert->execute();
        $resetId = (int) $conn->insert_id;
        $insert->close();
        $conn->commit();

        require_once __DIR__ . "/../php/mailer.php";
        $safeName = forgotEscape((string) $user["first_name"]);
        $safePin = forgotEscape($pin);
        $sent = sendSystemEmail(
            (string) $user["email"],
            (string) $user["first_name"],
            "Your password reset PIN",
            "<p>Hello " . $safeName . ",</p><p>Your password reset PIN is <strong style=\"font-size:24px;letter-spacing:4px\">" . $safePin . "</strong>.</p><p>Enter this code on the password recovery page within 10 minutes. If you did not request a reset, ignore this email.</p>",
            "Your password reset PIN is {$pin}. Enter it on the password recovery page within 10 minutes. If you did not request a reset, ignore this email."
        );
        if (!$sent) {
            $invalidate = $conn->prepare("UPDATE password_reset_pins SET used_at = NOW() WHERE reset_id = ?");
            $invalidate->bind_param("i", $resetId);
            $invalidate->execute();
            $invalidate->close();
            $_SESSION["forgot_password_error"] = "We couldn't send the PIN. Check the mail settings and try again.";
            $conn->close();
            header("Location: forgot_password.php", true, 303);
            exit();
        }

        $_SESSION["password_reset_id"] = $resetId;
        $_SESSION["password_reset_email"] = (string) $user["email"];
        $_SESSION["password_reset_verified"] = false;
        $_SESSION["verify_pin_notice"] = "We sent a PIN to your email. It expires in 10 minutes.";
        $conn->close();
        header("Location: verify_reset_pin.php", true, 303);
        exit();
    } catch (Throwable $exception) {
        if (isset($conn) && $conn instanceof mysqli) {
            try { $conn->rollback(); } catch (Throwable $ignored) {}
            $conn->close();
        }
            if (function_exists("logMailerIssue")) {
                logMailerIssue("Password reset PIN request error: " . $exception->getMessage());
            } else {
                error_log("Password reset PIN request error: " . $exception->getMessage());
            }
        $_SESSION["forgot_password_error"] = "Password reset is temporarily unavailable. Please try again later.";
        header("Location: forgot_password.php", true, 303);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Forgot Password | St. Peregrine Institute</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <main class="auth-card">
        <img src="../logo.png" alt="St. Peregrine Institute Logo" class="school-logo">
        <h1>FORGOT PASSWORD</h1>
        <p class="subtitle">Enter the email connected to your account.</p>
        <?php if ($error !== ""): ?><div class="message error" role="alert"><?= forgotEscape($error) ?></div><?php endif; ?>
        <form method="POST" action="forgot_password.php">
            <input type="hidden" name="csrf_token" value="<?= forgotEscape($_SESSION["password_reset_csrf"]) ?>">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" maxlength="100" autocomplete="email" value="<?= forgotEscape($emailValue) ?>" placeholder="Enter your account email" required>
            <button type="submit" class="submit-button">CONTINUE</button>
        </form>
        <p class="auth-switch"><a href="../index.php">Return to login</a></p>
    </main>
</body>
</html>
