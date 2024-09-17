<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "login");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "連接失敗：" . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "無法解析 JSON 數據"]);
    exit();
}

$username = $data['username'] ?? null;
$account = $data['account'] ?? null;
$password = $data['password'] ?? null;
$birthday = $data['birthday'] ?? null;
$phone = $data['phone'] ?? null;
$email = $data['email'] ?? null;

if (!$username || !$account || !$password || !$birthday || !$phone || !$email) {
    echo json_encode(["success" => false, "message" => "所有欄位均為必填"]);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL 準備失敗：" . $conn->error]);
    exit();
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "使用者名稱已存在，不能重複註冊"]);
    $stmt->close();
    $conn->close();
    exit();
}

// 檢查帳號是否已存在
$stmt = $conn->prepare("SELECT id FROM users WHERE account = ?");
$stmt->bind_param("s", $account);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "帳號已存在"]);
    $stmt->close();
    $conn->close();
    exit();
}

// 檢查電話是否已存在
$stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "電話已存在"]);
    $stmt->close();
    $conn->close();
    exit();
}

// 檢查電子郵件是否已存在
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "電子郵件已存在"]);
    $stmt->close();
    $conn->close();
    exit();
}


// 密碼加密
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 插入新使用者資料
$stmt = $conn->prepare("INSERT INTO users (username, account, password, birthday, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL 準備失敗：" . $conn->error]);
    exit();
}
$stmt->bind_param("ssssss", $username, $account, $hashed_password, $birthday, $phone, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "註冊成功"]);
} else {
    echo json_encode(["success" => false, "message" => "註冊失敗：" . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
