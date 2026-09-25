import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

Chart.defaults.font.family = "'Saira', sans-serif";
Chart.defaults.color = '#6c757d';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 16;

const COLORS = {
    primary: '#0d6efd',
    success: '#198754',
    warning: '#ffc107',
    info: '#0dcaf0',
    danger: '#dc3545',
    secondary: '#6c757d',
    light: '#f8f9fa',
    dark: '#212529',
};

const GRADIENTS = {
    primary: null,
    success: null,
    info: null,
    warning: null,
};

function initGradients(ctx) {
    if (!GRADIENTS.primary) {
        GRADIENTS.primary = ctx.createLinearGradient(0, 0, 0, 300);
        GRADIENTS.primary.addColorStop(0, 'rgba(13, 110, 253, 0.4)');
        GRADIENTS.primary.addColorStop(1, 'rgba(13, 110, 253, 0)');

        GRADIENTS.success = ctx.createLinearGradient(0, 0, 0, 300);
        GRADIENTS.success.addColorStop(0, 'rgba(25, 135, 84, 0.4)');
        GRADIENTS.success.addColorStop(1, 'rgba(25, 135, 84, 0)');

        GRADIENTS.info = ctx.createLinearGradient(0, 0, 0, 300);
        GRADIENTS.info.addColorStop(0, 'rgba(13, 202, 240, 0.4)');
        GRADIENTS.info.addColorStop(1, 'rgba(13, 202, 240, 0)');

        GRADIENTS.warning = ctx.createLinearGradient(0, 0, 0, 300);
        GRADIENTS.warning.addColorStop(0, 'rgba(255, 193, 7, 0.4)');
        GRADIENTS.warning.addColorStop(1, 'rgba(255, 193, 7, 0)');
    }
}

function getChartOptions(title = '', showLegend = false) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: showLegend,
                position: 'top',
                labels: {
                    font: { size: 11, family: "'Saira', sans-serif" },
                    usePointStyle: true,
                    pointStyle: 'circle',
                },
            },
            tooltip: {
                backgroundColor: 'rgba(33, 37, 41, 0.95)',
                titleFont: { size: 12, weight: '600' },
                bodyFont: { size: 11 },
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
            },
        },
        scales: {
            x: {
                grid: { display: false, drawBorder: false },
                ticks: { font: { size: 10 }, color: '#adb5bd', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 },
            },
            y: {
                grid: { color: 'rgba(108, 117, 125, 0.1)', drawBorder: false },
                ticks: { font: { size: 10 }, color: '#adb5bd', stepSize: 10 },
                beginAtZero: true,
            },
        },
        interaction: { mode: 'index', intersect: false },
        animation: { duration: 750, easing: 'easeOutQuart' },
    };
}

let viewsChart = null;
let usersChart = null;
let categoriesChart = null;
let statusChart = null;
let reviewsChart = null;
let feedbackChart = null;
let ratingsChart = null;

function renderViewsChart(data, period = 'daily') {
    const ctx = document.getElementById('viewsChart')?.getContext('2d');
    if (!ctx) return;

    initGradients(ctx);

    if (viewsChart) viewsChart.destroy();

    // data can be an object with period keys or a single period data
    const periodData = data[period] || data;

    viewsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: periodData.labels || [],
            datasets: [{
                label: 'Views',
                data: periodData.data || [],
                borderColor: COLORS.primary,
                backgroundColor: GRADIENTS.primary,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointBackgroundColor: COLORS.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }],
        },
        options: getChartOptions('Views Trend'),
    });
}

function renderUsersChart(data, period = 'daily') {
    const ctx = document.getElementById('usersChart')?.getContext('2d');
    if (!ctx) return;

    initGradients(ctx);

    if (usersChart) usersChart.destroy();

    // data can be an object with period keys or a single period data
    const periodData = data[period] || data;

    usersChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: periodData.labels || [],
            datasets: [{
                label: 'New Users',
                data: periodData.data || [],
                backgroundColor: GRADIENTS.success,
                borderColor: COLORS.success,
                borderWidth: 1.5,
                borderRadius: 4,
                borderSkipped: false,
            }],
        },
        options: getChartOptions('User Growth'),
    });
}

function renderCategoriesChart(data) {
    const ctx = document.getElementById('categoriesChart')?.getContext('2d');
    if (!ctx) return;

    if (categoriesChart) categoriesChart.destroy();

    const labels = Object.keys(data);
    const values = Object.values(data);
    const bgColors = [
        'rgba(13, 110, 253, 0.8)',
        'rgba(25, 135, 84, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(13, 202, 240, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(108, 117, 125, 0.8)',
        'rgba(111, 66, 193, 0.8)',
        'rgba(253, 126, 20, 0.8)',
    ];

    categoriesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: bgColors.slice(0, labels.length),
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { font: { size: 11 }, usePointStyle: true, pointStyle: 'circle', padding: 12 },
                },
                tooltip: {
                    backgroundColor: 'rgba(33, 37, 41, 0.95)',
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 11 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.raw / total) * 100).toFixed(1);
                            return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                        },
                    },
                },
            },
            cutout: '65%',
            animation: { animateRotate: true, animateScale: true, duration: 1000, easing: 'easeOutQuart' },
        },
    });
}

function renderStatusChart(data) {
    const ctx = document.getElementById('statusChart')?.getContext('2d');
    if (!ctx) return;

    initGradients(ctx);

    if (statusChart) statusChart.destroy();

    const labels = Object.keys(data).map(s => s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' '));
    const values = Object.values(data);

    statusChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Count',
                data: values,
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                ].slice(0, labels.length),
                borderColor: [
                    COLORS.primary,
                    COLORS.success,
                    COLORS.warning,
                    COLORS.danger,
                    COLORS.secondary,
                ].slice(0, labels.length),
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: getChartOptions('Content by Status'),
    });
}

function renderReviewsChart(data) {
    const ctx = document.getElementById('reviewsChart')?.getContext('2d');
    if (!ctx) return;

    if (reviewsChart) reviewsChart.destroy();

    const labels = Object.keys(data).map(s => s.charAt(0).toUpperCase() + s.slice(1));
    const values = Object.values(data);
    const colors = {
        pending: COLORS.warning,
        approved: COLORS.success,
        rejected: COLORS.danger,
        spam: COLORS.secondary,
    };

    reviewsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: labels.map(l => colors[l.toLowerCase()] || COLORS.primary),
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(33, 37, 41, 0.95)',
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.raw / total) * 100).toFixed(1);
                            return `${ctx.label}: ${ctx.raw} (${pct}%)`;
                        },
                    },
                },
            },
            cutout: '70%',
        },
    });
}

function renderFeedbackChart(data) {
    const ctx = document.getElementById('feedbackChart')?.getContext('2d');
    if (!ctx) return;

    if (feedbackChart) feedbackChart.destroy();

    const types = Object.keys(data);
    const allStatuses = ['pending', 'in_progress', 'resolved', 'closed'];
    const statusLabels = allStatuses.map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));

    const datasets = types.map((type, i) => {
        const colors = [
            'rgba(13, 110, 253, 0.8)',
            'rgba(25, 135, 84, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(220, 53, 69, 0.8)',
        ];
        return {
            label: type.charAt(0).toUpperCase() + type.slice(1),
            data: allStatuses.map(s => data[type]?.[s] || 0),
            backgroundColor: colors[i % colors.length],
            borderColor: colors[i % colors.length].replace('0.8', '1'),
            borderWidth: 1.5,
            borderRadius: 4,
        };
    });

    feedbackChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: statusLabels, datasets },
        options: {
            ...getChartOptions('Feedback Volume'),
            scales: {
                ...getChartOptions().scales,
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(108, 117, 125, 0.1)' } },
            },
        },
    });
}

function renderRatingsChart(data) {
    const ctx = document.getElementById('ratingsChart')?.getContext('2d');
    if (!ctx) return;

    if (ratingsChart) ratingsChart.destroy();

    const labels = Object.keys(data).map(r => `${r}★`);
    const values = Object.values(data);

    ratingsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Count',
                data: values,
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: COLORS.warning,
                borderWidth: 1.5,
                borderRadius: 4,
                borderSkipped: false,
            }],
        },
        options: {
            ...getChartOptions('Ratings Distribution'),
            indexAxis: 'y',
            scales: {
                x: { grid: { color: 'rgba(108, 117, 125, 0.1)' }, beginAtZero: true, ticks: { font: { size: 10 } } },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } },
            },
        },
    });
}

// Track current period for each chart
let currentPeriods = {
    views: 'daily',
    users: 'daily',
};

function initCharts() {
    const data = window.analyticsData || {};

    renderViewsChart(data.views, currentPeriods.views);
    renderUsersChart(data.users, currentPeriods.users);
    renderCategoriesChart(data.categories);
    renderStatusChart(data.status);
    renderReviewsChart(data.reviews);
    renderFeedbackChart(data.feedback);
    renderRatingsChart(data.ratings);
}

// Period switcher for chart-specific buttons
document.querySelectorAll('[data-chart]').forEach(btn => {
    btn.addEventListener('click', function() {
        const chartName = this.dataset.chart;
        const period = this.dataset.period;
        
        // Only handle period switching for charts that support it
        if (period && currentPeriods[chartName] !== undefined) {
            currentPeriods[chartName] = period;
        }
        
        document.querySelectorAll(`[data-chart="${chartName}"]`).forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Re-render with selected period
        initCharts();
    });
});

// Top-level period filter (30 Days, 90 Days, 1 Year) - applies to all period-aware charts
document.querySelectorAll('[data-range]').forEach(btn => {
    btn.addEventListener('click', function() {
        const range = parseInt(this.dataset.range);
        let period = 'daily';
        
        // Map range to period
        if (range >= 365) period = 'monthly';
        else if (range >= 90) period = 'weekly';
        
        // Update all period-aware charts
        Object.keys(currentPeriods).forEach(key => {
            currentPeriods[key] = period;
        });
        
        // Update UI for top-level buttons
        document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Also update chart-specific period buttons to match
        document.querySelectorAll('[data-chart][data-period]').forEach(b => {
            if (b.dataset.period === period) {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });
        
        // Re-render with selected period
        initCharts();
    });
});

document.addEventListener('DOMContentLoaded', initCharts);

if (typeof Livewire !== 'undefined') {
    document.addEventListener('livewire:navigated', initCharts);
}