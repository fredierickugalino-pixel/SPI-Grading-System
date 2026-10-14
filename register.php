<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

include("php/database.php");
require_once __DIR__ . "/php/password_rules.php";

$lrn = trim($_POST["lrn"] ?? "");
$first_name = trim($_POST["first_name"] ?? "");
$middle_name = trim($_POST["middle_name"] ?? "");
$last_name = trim($_POST["last_name"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

if (!preg_match("/^[0-9]{1,20}$/", $lrn)) {
    exit("Enter an LRN containing 1 to 20 digits. <a href='register.html'>Go back</a>");
}

if ($first_name === "" || $last_name === "" || strlen($first_name) > 50 || strlen($middle_name) > 50 || strlen($last_name) > 50) {
    exit("Enter a first and last name. Names must be 50 characters or fewer. <a href='register.html'>Go back</a>");
}

if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\\.com$/", $email)) {
    exit("Enter a valid Gmail address ending in @gmail.com. <a href='register.html'>Go back</a>");
}

if (!passwordMeetsRequirements($password)) {
    exit("Password must have at least 8 characters, one lowercase letter, one uppercase letter, and one special character. <a href='register.html'>Go back</a>");
}

if ($password !== $confirm_password) {
    exit("Passwords do not match. <a href='register.html'>Go back</a>");
}

$check = $conn->prepare("SELECT user_id, email, lrn FROM users WHERE LOWER(email) = ? OR lrn = ? LIMIT 1");
$check->bind_param("ss", $email, $lrn);
$check->execute();
$existing_user = $check->get_result()->fetch_assoc();
$check->close();

if ($existing_user) {
    if (strtolower($existing_user["email"]) === $email) {
        exit("That email is already registered. <a href='register.html'>Go back</a>");
    }
    exit("That LRN is already registered. <a href='register.html'>Go back</a>");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$role = "student";

try {
    $conn->begin_transaction();

    $sql = $conn->prepare("INSERT INTO users (lrn, first_name, middle_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("sssssss", $lrn, $first_name, $middle_name, $last_name, $email, $password_hash, $role);
    $sql->execute();
    $new_user_id = $conn->insert_id;
    $sql->close();

    $student = $conn->prepare("INSERT INTO students (user_id) VALUES (?)");
    $student->bind_param("i", $new_user_id);
    $student->execute();
    $student->close();

    $conn->commit();
    $conn->close();
    header("Location: registration_success.php", true, 303);
    exit();
} catch (Throwable $error) {
    $conn->rollback();
    if ((int) $error->getCode() === 1062) {
        echo "That LRN or email is already registered. <a href='register.html'>Go back</a>";
    } else {
        error_log("Registration error: " . $error->getMessage());
        echo "Registration failed. Please try again later.";
    }
}

$conn->close();
?>
