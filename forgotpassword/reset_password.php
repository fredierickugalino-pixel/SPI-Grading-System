<?php
ini_set("session.use_strict_mode", "1");
session_set_cookie_params(["httponly" => true, "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off", "samesite" => "Lax"]);
session_start();
if (empty($_SESSION["password_reset_csrf"])) {
    $_SESSION["password_reset_csrf"] = bin2hex(random_bytes(32));
}
function passwordResetEscape(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); }

$error = "";
$success = false;
$resetId = (int) ($_SESSION["password_reset_id"] ?? 0);
$authorized = !empty($_SESSION["password_reset_verified"]) && $resetId > 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["password_reset_csrf"], $_POST["csrf_token"] ?? "")) {
        $error = "Your session expired. Refresh the page and try again.";
    } elseif (!$authorized) {
        $error = "Confirm a valid reset PIN first.";
    } else {
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";
        require_once __DIR__ . "/../php/password_rules.php";
        if (!passwordMeetsRequirements($password)) {
            $error = "Use at least 8 characters with a lowercase letter, an uppercase letter, and a special character.";
        } elseif ($password !== $confirmPassword) {
            $error = "The passwords do not match.";
        } else {
            try {
                require __DIR__ . "/../php/database.php";
                $conn->begin_transaction();
                $lookup = $conn->prepare("SELECT user_id FROM password_reset_pins WHERE reset_id = ? AND verified_at IS NOT NULL AND used_at IS NULL AND expires_at > NOW() LIMIT 1 FOR UPDATE");
                $lookup->bind_param("i", $resetId);
                $lookup->execute();
                $reset = $lookup->get_result()->fetch_assoc();
                $lookup->close();

                if (!$reset) {
                    $conn->rollback();
                    $error = "Your confirmed PIN expired. Request a new one.";
                    $authorized = false;
                    unset($_SESSION["password_reset_id"], $_SESSION["password_reset_email"], $_SESSION["password_reset_verified"]);
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateUser = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $updateUser->bind_param("si", $passwordHash, $reset["user_id"]);
                    $updateUser->execute();
                    $updateUser->close();

                    $consume = $conn->prepare("UPDATE password_reset_pins SET used_at = NOW() WHERE reset_id = ? AND used_at IS NULL");
                    $consume->bind_param("i", $resetId);
                    $consume->execute();
                    $consume->close();
                    $conn->commit();
                    unset($_SESSION["password_reset_id"], $_SESSION["password_reset_email"], $_SESSION["password_reset_verified"]);
                    $authorized = false;
                    $success = true;
                }
                $conn->close();
            } catch (Throwable $exception) {
                if (isset($conn) && $conn instanceof mysqli) {
                    try { $conn->rollback(); } catch (Throwable $ignored) {}
                    $conn->close();
                }
                error_log("Password reset completion error: " . $exception->getMessage());
                $error = "Password reset is temporarily unavailable. Please try again.";
            }
        }
    }
}

if ($authorized && !$success) {
    try {
        require __DIR__ . "/../php/database.php";
        $check = $conn->prepare("SELECT reset_id FROM password_reset_pins WHERE reset_id = ? AND verified_at IS NOT NULL AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
        $check->bind_param("i", $resetId);
        $check->execute();
        $authorized = (bool) $check->get_result()->fetch_assoc();
        $check->close();
        $conn->close();
        if (!$authorized) {
            unset($_SESSION["password_reset_id"], $_SESSION["password_reset_email"], $_SESSION["password_reset_verified"]);
            if ($error === "") { $error = "Your confirmed PIN expired. Request a new one."; }
        }
    } catch (Throwable $exception) {
        if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
        error_log("Password reset page error: " . $exception->getMessage());
        $authorized = false;
        $error = "Password reset is temporarily unavailable. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Choose New Password | St. Peregrine Institute</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <main class="auth-card">
        <img src="../logo.png" alt="St. Peregrine Institute Logo" class="school-logo">
        <?php if ($success): ?>
            <h1>PASSWORD UPDATED</h1>
            <div class="message success" role="status">Your password has been changed. You can now sign in with your new password.</div>
            <p class="auth-switch"><a href="../index.php">Return to login</a></p>
        <?php else: ?>
            <h1>CHOOSE A NEW PASSWORD</h1>
            <p class="subtitle">Your PIN is confirmed. Set a new password for your account.</p>
            <?php if ($error !== ""): ?><div class="message error" role="alert"><?= passwordResetEscape($error) ?></div><?php endif; ?>
            <?php if ($authorized): ?>
                <form method="POST" action="reset_password.php">
                    <input type="hidden" name="csrf_token" value="<?= passwordResetEscape($_SESSION["password_reset_csrf"]) ?>">
                    <label for="password">New Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}" title="At least 8 characters, one lowercase letter, one uppercase letter, and one special character." autocomplete="new-password" required>
                        <button type="button" class="show-password" data-target="password" aria-controls="password">Show</button>
                    </div>
                    <ul class="password-checklist" id="password-checklist" aria-label="Password requirements">
                        <li data-rule="length">At least 8 characters</li>
                        <li data-rule="lowercase">One lowercase letter</li>
                        <li data-rule="uppercase">One uppercase letter</li>
                        <li data-rule="special">One special character</li>
                    </ul>
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                        <button type="button" class="show-password" data-target="confirm_password" aria-controls="confirm_password">Show</button>
                    </div>
                    <p class="field-message" id="confirm-status" aria-live="polite"></p>
                    <button type="submit" class="submit-button">CHANGE PASSWORD</button>
                </form>
            <?php else: ?>
                <p class="auth-switch"><a href="forgot_password.php">Request another reset PIN</a></p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <script src="../script.js"></script>
</body>
</html>
