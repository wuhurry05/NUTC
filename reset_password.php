<?php
// 開啟錯誤顯示（開發時期使用）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to log errors
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'reset_password_errors.log');
}

try {
    // Log the raw inputs
    logError("Raw input: " . file_get_contents("php://input"));

    // 獲取 POST 請求的數據
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Log the decoded data
    logError("Decoded data: " . json_encode($data));

    $email = $data['email'] ?? '';
    $verificationCode = $data['verificationCode'] ?? '';
    $newPassword = $data['newPassword'] ?? '';

    // Log the extracted values
    logError("Extracted email: " . $email);
    logError("Extracted verificationCode: " . $verificationCode);
    logError("Extracted newPassword: " . (empty($newPassword) ? 'empty' : 'not empty'));

    // Validate input
    if (empty($email) || empty($verificationCode) || empty($newPassword)) {
        throw new Exception("缺少必要的信息");
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "login");
    if ($conn->connect_error) {
        throw new Exception("數據庫連接失敗: " . $conn->connect_error);
    }

    // Check if the verification code is valid and not expired
    $stmt = $conn->prepare("SELECT * FROM password_reset WHERE email = ? AND verification_code = ? AND expiry_time > NOW()");
    $stmt->bind_param("ss", $email, $verificationCode);
    $stmt->execute();
    $result = $stmt->get_result();

    // Log the query result
    logError("Query result rows: " . $result->num_rows);

    if ($result->num_rows === 0) {
        // Additional debug query
        $debugStmt = $conn->prepare("SELECT * FROM password_reset WHERE email = ?");
        $debugStmt->bind_param("s", $email);
        $debugStmt->execute();
        $debugResult = $debugStmt->get_result();
        
        if ($debugResult->num_rows > 0) {
            $row = $debugResult->fetch_assoc();
            logError("Found reset entry: " . json_encode($row));
            if ($row['verification_code'] !== $verificationCode) {
                logError("Verification code mismatch. Expected: " . $row['verification_code'] . ", Received: " . $verificationCode);
            }
            if (strtotime($row['expiry_time']) < time()) {
                logError("Verification code expired. Expiry time: " . $row['expiry_time'] . ", Current time: " . date('Y-m-d H:i:s'));
            }
        } else {
            logError("No reset entry found for email: " . $email);
        }
        
        throw new Exception("無效或過期的驗證碼");
    }

    // Update the user's password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $email);
    
    if ($updateStmt->execute()) {
        // Delete the used verification code
        $deleteStmt = $conn->prepare("DELETE FROM password_reset WHERE email = ?");
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();

        logError("Password updated successfully for email: " . $email);
        echo json_encode(["success" => true, "message" => "密碼重置成功"]);
    } else {
        throw new Exception("更新密碼失敗");
    }

} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($deleteStmt)) $deleteStmt->close();
    if (isset($conn)) $conn->close();
}
?>