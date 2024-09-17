<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header("Content-Type: application/json");

// 錯誤處理函數
function handleError($message, $errorInfo = null) {
    $logMessage = "Error in send_reset_email.php: " . $message . ($errorInfo ? " Details: " . json_encode($errorInfo) : "");
    error_log($logMessage);
    file_put_contents('reset_password_errors.log', date('[Y-m-d H:i:s] ') . $logMessage . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => $message]);
    exit();
}

// 數據庫連接
try {
    $conn = new mysqli("localhost", "root", "", "login");
    if ($conn->connect_error) {
        handleError("數據庫連接失敗", $conn->connect_error);
    }
} catch (Exception $e) {
    handleError("數據庫連接異常", $e->getMessage());
}

// 獲取並驗證輸入
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$username || !$email) {
    handleError("請提供有效的用戶名和電子郵件地址");
}

// 檢查帳號是否存在，並驗證電子郵件
try {
    // 首先檢查帳號是否存在於資料庫中
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        handleError("此帳號不存在");
    }

    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $registeredEmail = $user['email'];

    // 然後檢查輸入的郵箱是否與註冊時的郵箱相符
    if ($email !== $registeredEmail) {
        handleError("輸入的電子郵件與此帳號註冊時的郵箱不符");
    }

} catch (Exception $e) {
    handleError("查詢用戶時發生錯誤", $e->getMessage());
}

// 生成驗證碼 (更長且更安全)
$verificationCode = sprintf("%06d", mt_rand(0, 999999));
$expiryTime = date('Y-m-d H:i:s', strtotime('+7 hour'));

// 儲存驗證碼到數據庫
try {
    $stmt = $conn->prepare("INSERT INTO password_reset (email, verification_code, expiry_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE verification_code = ?, expiry_time = ?");
    $stmt->bind_param("sssss", $email, $verificationCode, $expiryTime, $verificationCode, $expiryTime);
    if (!$stmt->execute()) {
        handleError("儲存驗證碼失敗", $stmt->error);
    }
} catch (Exception $e) {
    handleError("儲存驗證碼時發生錯誤", $e->getMessage());
}

// 發送郵件
$mail = new PHPMailer(true);

try {
    // 伺服器設置
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USERNAME') ?: 'g0980012@gmail.com'; // 使用環境變量
    $mail->Password   = getenv('SMTP_PASSWORD') ?: 'vfip luor nwfx krbw'; // 使用環境變量
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // SSL 設定
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // 設置字符編碼
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // 收件人
    $mail->setFrom('g0980012@gmail.com', '=?UTF-8?B?'.base64_encode('智慧曬衣小幫手').'?=');
    $mail->addAddress($email);

    // 內容
    $mail->isHTML(true);
    $mail->Subject = '=?UTF-8?B?'.base64_encode('重設密碼驗證碼').'?=';
    $mail->Body    = "您的驗證碼是: <b>{$verificationCode}</b><br>此驗證碼將在一小時後過期。";

    $mail->send();
    echo json_encode(["success" => true, "message" => "驗證碼已發送到您的郵箱"]);
} catch (Exception $e) {
    handleError("郵件發送失敗", $mail->ErrorInfo);
}

$stmt->close();
$conn->close();
/*error.log
error_log("Setting expiry time to: " . $expiryTime);
error_log("Generated verification code for {$email}: {$verificationCode}");*/

?>