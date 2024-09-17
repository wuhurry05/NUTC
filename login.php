<?php
// 設置響應頭
header("Content-Type: application/json");

// 連接到數據庫
$conn = new mysqli("localhost", "root", "", "login");

// 檢查連接
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "連接失敗：" . $conn->connect_error]));
}

// 獲取 POST 數據
$data = json_decode(file_get_contents("php://input"), true);
$account = $data['account'];
$password = $data['password'];

// 準備 SQL 語句
$stmt = $conn->prepare("SELECT id, username, password, level FROM users WHERE account = ?");
$stmt->bind_param("s", $account);

// 執行查詢
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // 判斷用戶等級
        $level = $user['level'];
        if ($level == 'admin') {
            echo json_encode([
                "success" => true,
                "message" => "登入成功",
                "username" => $user['username'],
                "redirect" => "back.html" // 管理員跳轉的頁面
            ]);
        } else {
            echo json_encode([
                "success" => true,
                "message" => "登入成功",
                "username" => $user['username'],
                "redirect" => "dashboard.html" // 一般用戶跳轉的頁面
            ]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "密碼錯誤"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "用戶不存在"]);
}

$stmt->close();
$conn->close();
?>
