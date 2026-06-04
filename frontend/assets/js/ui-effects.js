document.addEventListener('DOMContentLoaded', () => {
    if (window.AOS) {
        AOS.init({ duration: 750, once: true, offset: 90, easing: 'ease-out-cubic' });
    }
    ensureToastStack();
});

function ensureToastStack() {
    if (!document.getElementById('toastStack')) {
        const stack = document.createElement('div');
        stack.id = 'toastStack';
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }
}

function showToast(message, type = 'success') {
    ensureToastStack();
    const stack = document.getElementById('toastStack');
    const toast = document.createElement('div');
    toast.className = `app-toast ${type}`;
    const icon = type === 'success' ? 'fa-circle-check' : type === 'danger' ? 'fa-circle-exclamation' : type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-info';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i><span>${message}</span>`;
    stack.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'toastOut .25s ease forwards';
        setTimeout(() => toast.remove(), 260);
    }, 2800);
}
