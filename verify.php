<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "login");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "連接失敗：" . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => true, "message" => "驗證成功"]);
} else {
    echo json_encode(["success" => false, "message" => "電子郵件不正確"]);
}

$stmt->close();
$conn->close();
?>
