document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('resetPasswordForm');

    if (!form) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (!token) {
        showResetPasswordAlert('Invalid reset link');
        form.classList.add('d-none');
        return;
    }

    document.getElementById('resetToken').value = token;

    form.addEventListener('submit', resetPassword);
});

function showResetPasswordAlert(message, type = 'danger') {

    const alertBox = document.getElementById('resetPasswordAlert');

    if (!alertBox) {
        alert(message);
        return;
    }

    alertBox.innerHTML = `
        <div class="alert alert-${type}">
            ${message}
        </div>
    `;
}

async function resetPassword(e) {

    e.preventDefault();

    const token = document.getElementById('resetToken').value;
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();

    if (!newPassword || !confirmPassword) {
        showResetPasswordAlert('All fields are required');
        return;
    }

    if (newPassword.length < 6) {
        showResetPasswordAlert('Password must be at least 6 characters');
        return;
    }

    if (newPassword !== confirmPassword) {
        showResetPasswordAlert('Passwords do not match');
        return;
    }

    try {
        const response = await fetch(
            API_URL + 'auth/reset-password.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            }
        );

        const result = await response.json();

        showResetPasswordAlert(
            result.message,
            result.status ? 'success' : 'danger'
        );

        if (result.status) {
            document.getElementById('resetPasswordForm').reset();

            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        }

    } catch (error) {
        console.log(error);
        showResetPasswordAlert('Password reset failed');
    }
}