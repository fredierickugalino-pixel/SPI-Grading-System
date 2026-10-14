<?php
declare(strict_types=1);

function logMailerIssue(string $message): void
{
    $message = preg_replace("/[\r\n]+/", " ", $message) ?? "Email delivery error.";
    $line = date("c") . " " . $message . PHP_EOL;
    @file_put_contents(__DIR__ . "/mail_errors.log", $line, FILE_APPEND | LOCK_EX);
    error_log($message);
}

function sendSystemEmail(string $email, string $name, string $subject, string $htmlBody, string $plainBody): bool
{
    $sourcePath = __DIR__ . "/../PHPMailer/src/";
    foreach (["Exception.php", "PHPMailer.php", "SMTP.php"] as $file) {
        if (!is_file($sourcePath . $file)) {
            logMailerIssue("PHPMailer source file is missing: " . $sourcePath . $file);
            return false;
        }
        require_once $sourcePath . $file;
    }

    $configPath = __DIR__ . "/mail_config.local.php";
    if (!is_file($configPath)) {
        logMailerIssue("SMTP configuration is missing from php/mail_config.local.php.");
        return false;
    }
    $config = require $configPath;
    if (!is_array($config) || empty($config["host"]) || empty($config["username"]) ||
        empty($config["password"]) || empty($config["from_email"]) ||
        str_contains((string) $config["host"], "example.com")) {
        logMailerIssue("SMTP is not configured in php/mail_config.local.php.");
        return false;
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) $config["host"];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $config["username"];
        $smtpPassword = (string) $config["password"];
        if (strtolower($mail->Host) === "smtp.gmail.com") {
            // Google displays app passwords in groups separated by spaces.
            $smtpPassword = preg_replace("/\s+/", "", $smtpPassword);
        }
        $mail->Password = $smtpPassword;
        $mail->Port = (int) ($config["port"] ?? 587);
        $mail->SMTPSecure = ($config["encryption"] ?? "tls") === "ssl"
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = "UTF-8";
        $mail->setFrom((string) $config["from_email"], (string) ($config["from_name"] ?? "St. Peregrine Institute"));
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainBody;
        $mail->send();
        return true;
    } catch (Throwable $error) {
        logMailerIssue("Email delivery failed: " . $error->getMessage());
        return false;
    }
}
