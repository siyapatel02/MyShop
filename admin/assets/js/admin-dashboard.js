const token = localStorage.getItem('admin_token');

if (!token) {
    window.location.href = 'login.php';
}

function adminCssVar(name) {
    return getComputedStyle(document.body).getPropertyValue(name).trim();
}

function chartColors() {
    return {
        text: adminCssVar('--admin-text') || '#24191b',
        muted: adminCssVar('--admin-muted') || '#7a6f72',
        border: adminCssVar('--admin-border') || '#eadfe2',
        primary: adminCssVar('--admin-primary') || '#b76e79',
        primaryDark: adminCssVar('--admin-primary-dark') || '#8f4f59'
    };
}

fetch(ADMIN_API_URL + 'admin/dashboard.php', {
    headers: {
        Authorization: 'Bearer ' + token
    }
})
.then(response => response.json())
.then(result => {
    if (!result.status) {
        if (window.showAdminToast) showAdminToast(result.message, 'danger');
        else alert(result.message);
        return;
    }

    const cards = result.data.cards;
    document.getElementById('totalUsers').innerText = cards.users;
    document.getElementById('totalProducts').innerText = cards.products;
    document.getElementById('totalOrders').innerText = cards.orders;
    document.getElementById('totalSales').innerText = '₹' + Number(cards.sales).toFixed(2);

    renderOrderStatusChart(result.data.charts.order_status);
    renderMonthlySalesChart(result.data.charts.monthly_sales);
})
.catch(error => {
    console.log(error);
    if (window.showAdminToast) showAdminToast('Dashboard load failed', 'danger');
    else alert('Dashboard load failed');
});

function renderOrderStatusChart(data) {
    const ctx = document.getElementById('orderStatusChart');
    if (!ctx) return;

    const c = chartColors();
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Shipped', 'Delivered'],
            datasets: [{
                data: [data.pending, data.shipped, data.delivered],
                backgroundColor: [c.primary, c.primaryDark, '#d9a3ac'],
                borderColor: adminCssVar('--admin-card') || '#ffffff',
                borderWidth: 3
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: { color: c.text, font: { weight: '600' } }
                }
            },
            cutout: '58%'
        }
    });
}

function renderMonthlySalesChart(data) {
    const ctx = document.getElementById('monthlySalesChart');
    if (!ctx) return;

    const labels = data.map(item => item.month);
    const sales = data.map(item => Number(item.sales));
    const c = chartColors();

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: sales,
                backgroundColor: c.primary,
                borderColor: c.primaryDark,
                borderWidth: 1,
                borderRadius: 10
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: { color: c.text, font: { weight: '600' } }
                }
            },
            scales: {
                x: {
                    ticks: { color: c.muted },
                    grid: { color: c.border }
                },
                y: {
                    ticks: { color: c.muted },
                    grid: { color: c.border }
                }
            }
        }
    });
}
