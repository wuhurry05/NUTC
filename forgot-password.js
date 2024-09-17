function sendVerificationCode() {
    const email = document.getElementById('forgot-email').value;

    fetch('send_reset_email.php', {  // 修改這裡，使用正確的 PHP 文件
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email }),
    })
    .then(response => response.json())  // 直接解析 JSON
    .then(data => {
        console.log('Response:', data); // 記錄響應以便調試
        const verificationCodeSection = document.getElementById('verification-code-section');
        const forgotMessage = document.getElementById('forgot-message');

        if (data.success) {
            if (verificationCodeSection) {
                verificationCodeSection.style.display = 'block';
            } else {
                console.error('Element with ID "verification-code-section" not found.');
            }

            if (forgotMessage) {
                forgotMessage.textContent = data.message;
            } else {
                console.error('Element with ID "forgot-message" not found.');
            }
        } else {
            if (forgotMessage) {
                forgotMessage.textContent = data.message;
            } else {
                console.error('Element with ID "forgot-message" not found.');
            }
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        const forgotMessage = document.getElementById('forgot-message');
        if (forgotMessage) {
            forgotMessage.textContent = '發送驗證碼時出錯，請稍後再試。';
        }
    });
}