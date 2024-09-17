<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $username = $input['username'];
    $account = $input['account'];
    $level = $input['level'];
    $birthday = $input['birthday'];
    $phone = $input['phone'];
    $email = $input['email'];

    // 資料庫連接
    $conn = new mysqli('localhost', 'root', '', 'login'); // 替換為實際資料庫連接信息
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE users SET account=?, level=?, birthday=?, phone=?, email=? WHERE username=?");
    $stmt->bind_param("ssssss", $account, $level, $birthday, $phone, $email, $username);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '更新失敗，請稍後再試。']);
    }

    $stmt->close();
    $conn->close();
}
?>
