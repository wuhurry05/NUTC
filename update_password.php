<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "login");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "連接失敗：" . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$newPassword = $data['newPassword'];

// 密碼加密
$hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

// 更新密碼
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    if (password_verify($newPassword, $hashed_password)) {
        echo json_encode(["success" => true, "message" => "密碼已成功更新"]);
    }else {
        echo json_encode(["success" => false, "message" => "密碼無法更新"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "更新密碼失敗：" . $stmt->error]);
}

$stmt->close();
$conn->close();
?>