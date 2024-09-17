<?php
header('Content-Type: application/json');

// 資料庫連接參數
$host = 'localhost'; // 資料庫主機
$dbname = 'login'; // 資料庫名稱
$username = 'root'; // 資料庫使用者名稱
$password = ''; // 資料庫密碼

try {
    // 建立資料庫連接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 查詢使用者資料
    $stmt = $pdo->prepare("SELECT id, username, account, level, birthday, phone, email FROM users");
    $stmt->execute();

    // 獲取結果
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 返回 JSON 格式的資料
    echo json_encode($users);
} catch (PDOException $e) {
    // 錯誤處理
    echo json_encode(['error' => $e->getMessage()]);
}
?>