<?php
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, max-age=0");

$email = strtolower(trim($_GET["email"] ?? ""));
if ($email === "" || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["available" => false, "error" => "invalid_email"]);
    exit;
}

try {
    require __DIR__ . "/database.php";
    $statement = $conn->prepare("SELECT user_id FROM users WHERE LOWER(email) = ? LIMIT 1");
    $statement->bind_param("s", $email);
    $statement->execute();
    $statement->store_result();
    echo json_encode(["available" => $statement->num_rows === 0]);
    $statement->close();
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(["available" => false, "error" => "check_failed"]);
}
