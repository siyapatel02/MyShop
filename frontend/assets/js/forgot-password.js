document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgotPasswordForm');

    if (!form) {
        return;
    }

    form.addEventListener('submit', sendForgotPasswordLink);
});

function showForgotPasswordAlert(message, type = 'danger') {

    const alertBox = document.getElementById('forgotPasswordAlert');

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

async function sendForgotPasswordLink(e) {

    e.preventDefault();

    const email = document.getElementById('forgotEmail').value.trim();

    if (!email) {
        showForgotPasswordAlert('Email is required');
        return;
    }

    try {
        const response = await fetch(
            API_URL + 'auth/forgot-password.php',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email
                })
            }
        );

        const result = await response.json();

        showForgotPasswordAlert(
            result.message,
            result.status ? 'success' : 'danger'
        );

        if (result.status) {
            document.getElementById('forgotPasswordForm').reset();
        }

    } catch (error) {
        console.log(error);
        showForgotPasswordAlert('Failed to send reset link');
    }
}