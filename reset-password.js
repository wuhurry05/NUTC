document.addEventListener('DOMContentLoaded', () => {
    console.log("DOMContentLoaded event fired");
    initializeResetPassword();
});

function initializeResetPassword() {
    console.log("Initializing reset password");
    const resetButton = document.getElementById('reset-password-button');
    const sendVerificationButton = document.getElementById('send-verification-code');
    
    if (sendVerificationButton) {
        console.log("Send verification button found, adding click event listener");
        sendVerificationButton.addEventListener('click', sendVerificationCode);
    } else {
        console.error('Send verification button not found');
    }

    if (resetButton) {
        console.log("Reset button found, adding click event listener");
        resetButton.addEventListener('click', resetPassword);
    } else {
        console.error('Reset password button not found');
    }

    // Initialize password toggle functionality
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', togglePasswordVisibility);
    });
}

function sendVerificationCode() {
    const username = document.getElementById('reset-username').value;
    const email = document.getElementById('reset-email').value;
    const messageElement = document.getElementById('forgot-message');
    
    if (!username || !email) {
        showMessage(messageElement, "請輸入帳號和電子郵件地址", 'error');
        return;
    }

    fetch('send_reset_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username: username, email: email }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(messageElement, data.message, 'success');
            document.getElementById('verification-code-section').style.display = 'block';
        } else {
            showMessage(messageElement, data.message, 'error');
            document.getElementById('verification-code-section').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(messageElement, "發送驗證碼時發生錯誤，請稍後再試", 'error');
        document.getElementById('verification-code-section').style.display = 'none';
    });
}

function showMessage(element, message, type) {
    if (element) {
        element.textContent = message;
        element.className = 'message ' + type;
        element.style.display = 'block';
    } else {
        console.error("Message element not found");
    }
}

function resetPassword() {
    console.log("resetPassword function called");

    const elements = {
        email: document.getElementById('reset-email'),
        verificationCode: document.getElementById('reset-verification-code'),
        newPassword: document.getElementById('reset-new-password'),
        confirmPassword: document.getElementById('reset-confirm-password')
    };

    console.log("Found elements:", elements);

    const missingElements = Object.entries(elements)
        .filter(([key, value]) => !value)
        .map(([key]) => key);

    if (missingElements.length > 0) {
        console.error("Missing elements:", missingElements);
        showResetPasswordMessage(`找不到所有必要的輸入欄位，請稍後再試。缺少: ${missingElements.join(', ')}`, 'error');
        return;
    }

    const email = elements.email.value;
    const verificationCode = elements.verificationCode.value;
    const newPassword = elements.newPassword.value;
    const confirmPassword = elements.confirmPassword.value;

    if (newPassword !== confirmPassword) {
        showResetPasswordMessage("新密碼和確認密碼不匹配", 'error');
        return;
    }

    fetch('reset_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email: email,
            verificationCode: verificationCode,
            newPassword: newPassword
        }),
    })
    .then(response => response.text())
    .then(text => {
        console.log("Raw server response:", text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("JSON parse error:", e);
            throw new Error("Server response is not valid JSON");
        }
    })
    .then(data => {
        if (data.success) {
            showResetPasswordMessage(data.message, 'success');
            setTimeout(() => {
                closeForgotPasswordModal();
                // Optionally redirect to login page or show login form
            }, 3000);
        } else {
            showResetPasswordMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResetPasswordMessage("重置密碼時發生錯誤，請稍後再試", 'error');
    });
}

function showResetPasswordMessage(message, type) {
    console.log("Showing message:", message, "Type:", type);
    const messageElement = document.getElementById('reset-password-message');
    if (messageElement) {
        messageElement.textContent = message;
        messageElement.className = 'message ' + type;
    } else {
        console.error("Message element not found");
    }
}

function togglePasswordVisibility(event) {
    const passwordInput = event.target.previousElementSibling;
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    event.target.src = type === 'password' ? 'eye-off-icon.svg' : 'eye-icon.svg';
}

function closeForgotPasswordModal() {
    const modal = document.getElementById('forgot-password-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}