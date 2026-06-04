<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--rose:#b76e79;--rose-dark:#8f4f59;--ink:#24191b;--muted:#7a6f72;--bg:#f6f4f2;--card:#fff;--border:#eadfe2;--shadow:0 24px 60px rgba(35,23,26,.12)}
body{min-height:100vh;margin:0;background:radial-gradient(circle at 12% 12%,rgba(183,110,121,.18),transparent 32%),linear-gradient(135deg,#fff7f8,#f6f4f2);font-family:'Inter','Poppins','Segoe UI',sans-serif;color:var(--ink);display:flex;align-items:center;justify-content:center;padding:18px}
.admin-login-card{width:100%;max-width:430px;background:rgba(255,255,255,.88);backdrop-filter:blur(16px);border:1px solid var(--border);border-radius:28px;box-shadow:var(--shadow);padding:34px;animation:fadeUp .45s ease both}.brand-mark{width:58px;height:58px;border-radius:20px;background:linear-gradient(135deg,var(--rose),#f0bbc3);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:1.45rem;margin:0 auto 14px}.admin-login-card h3{font-weight:850;text-align:center;margin-bottom:6px}.admin-login-card p{text-align:center;color:var(--muted);margin-bottom:25px}.form-control{height:48px;border-radius:14px;border:1px solid var(--border);box-shadow:none}.form-control:focus{border-color:var(--rose);box-shadow:0 0 0 .2rem rgba(183,110,121,.14)}.btn-admin{height:48px;border-radius:14px;border:0;background:linear-gradient(135deg,var(--rose),var(--rose-dark));color:#fff;font-weight:800;width:100%;transition:.22s}.btn-admin:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(183,110,121,.28);color:#fff}.admin-login-alert .alert{border-radius:14px;border:0}@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<div class="admin-login-card">
    <div class="brand-mark">M</div>
    <h3>Admin Login</h3>
    <p>Sign in to manage MyShop.</p>
    <div id="adminLoginAlert" class="admin-login-alert"></div>
    <form id="adminLoginForm">
        <input type="email" name="email" class="form-control mb-3" placeholder="Admin Email" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <button type="submit" class="btn btn-admin">Login</button>
    </form>
</div>

<script>
const API_URL = window.location.origin + '/' + window.location.pathname.split('/').filter(Boolean)[0] + '/backend/api/';

function showLoginAlert(message, type = 'danger') {
    const box = document.getElementById('adminLoginAlert');
    box.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    setTimeout(() => box.innerHTML = '', 2500);
}

document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const payload = {
        email: formData.get('email'),
        password: formData.get('password')
    };
    try {
        const response = await fetch(API_URL + 'admin/login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status) {
            localStorage.setItem('admin_token', result.data.token);
            showLoginAlert('Login successful', 'success');
            setTimeout(() => window.location.href = 'dashboard.php', 700);
            return;
        }
        showLoginAlert(result.message || 'Login failed');
    } catch (error) {
        showLoginAlert('Something went wrong');
    }
});
</script>
</body>
</html>
