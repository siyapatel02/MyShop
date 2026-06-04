document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            try {
                const data = {
                    email: loginForm.querySelector('input[name="email"]').value.trim(),
                    password: loginForm.querySelector('input[name="password"]').value
                };
                const response = await fetch(API_URL + 'auth/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
                const result = await response.json();
                if (result.status) {
                    localStorage.setItem('token', result.data.token);
                    localStorage.setItem('user', JSON.stringify(result.data.user));
                    if (typeof showToast === 'function') showToast(result.message, 'success'); else alert(result.message);
                    setTimeout(() => window.location.href = 'home.php', 450);
                } else {
                    if (typeof showToast === 'function') showToast(result.message, 'danger'); else alert(result.message);
                }
            } catch (error) {
                console.log(error);
                if (typeof showToast === 'function') showToast('Login failed', 'danger'); else alert('Login Failed');
            }
        });
    }

    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('registerPassword');
    if (passwordInput) {
        passwordInput.addEventListener('input', () => showPasswordRulePopup(passwordInput.value));
        passwordInput.addEventListener('blur', () => hidePasswordRulePopup(900));
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const password = registerForm.querySelector('input[name="password"]').value;
            const validation = validateStrongPassword(password);
            showPasswordRulePopup(password, true);
            if (!validation.valid) {
                if (typeof showToast === 'function') showToast('Please follow all password rules', 'warning'); else alert('Please follow all password rules');
                return;
            }
            try {
                const data = {
                    name: registerForm.querySelector('input[name="name"]').value.trim(),
                    email: registerForm.querySelector('input[name="email"]').value.trim(),
                    password
                };
                const response = await fetch(API_URL + 'auth/register.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
                const result = await response.json();
                if (typeof showToast === 'function') showToast(result.message, result.status ? 'success' : 'danger'); else alert(result.message);
                if (result.status) setTimeout(() => window.location.href = 'login.php', 650);
            } catch (error) {
                console.log(error);
                if (typeof showToast === 'function') showToast('Registration failed', 'danger'); else alert('Registration Failed');
            }
        });
    }
});

function validateStrongPassword(password) {
    const rules = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    return { rules, valid: Object.values(rules).every(Boolean) };
}

const passwordRuleLabels = {
    length: 'At least 8 characters',
    upper: 'One uppercase letter',
    lower: 'One lowercase letter',
    number: 'One number',
    special: 'One special symbol'
};
let passwordPopupTimer = null;

function showPasswordRulePopup(password, keepOpen = false) {
    const box = document.getElementById('passwordRules');
    if (!box) return;
    const validation = validateStrongPassword(password);
    const missing = Object.entries(validation.rules).filter(([, ok]) => !ok).map(([rule]) => passwordRuleLabels[rule]);

    clearTimeout(passwordPopupTimer);

    if (!password || validation.valid) {
        box.classList.remove('show');
        box.innerHTML = '';
        return;
    }

    box.innerHTML = `<strong>Password missing:</strong>${missing.map(item => `<span><i class="fa-regular fa-circle-xmark"></i>${item}</span>`).join('')}`;
    box.classList.add('show');

    if (!keepOpen) hidePasswordRulePopup(1800);
}

function hidePasswordRulePopup(delay = 1200) {
    const box = document.getElementById('passwordRules');
    if (!box) return;
    clearTimeout(passwordPopupTimer);
    passwordPopupTimer = setTimeout(() => box.classList.remove('show'), delay);
}

function updatePasswordRules(password) {
    showPasswordRulePopup(password, true);
}
