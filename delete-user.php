<?php
// 設定資料庫連接參數
$host = 'localhost'; // 資料庫主機
$dbname = 'login'; // 資料庫名稱
$username = 'root'; // 資料庫使用者名稱
$password = ''; // 資料庫密碼

// 建立資料庫連接
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫連接失敗: ' . $e->getMessage()]);
    exit;
}

// 獲取使用者姓名
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameToDelete = $_POST['username'] ?? '';

    if ($usernameToDelete) {
        // 準備 SQL 刪除語句
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = :username");
        $stmt->bindParam(':username', $usernameToDelete);

        // 執行刪除操作
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => '使用者已刪除']);
            } else {
                echo json_encode(['status' => 'error', 'message' => '未找到使用者']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => '刪除失敗']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '未提供使用者姓名']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '不支援的請求方法']);
}

?>