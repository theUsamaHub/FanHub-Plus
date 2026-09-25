import {
    Chart,
    ArcElement,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
);

const PALETTE = [
    '#FF922E',
    '#FFD65A',
    '#F45132',
    '#C9B6F5',
    '#7F5AF0',
    '#2CE8C7',
    '#FF3E9A',
    '#181320',
];

function isLight() {
    return document.documentElement.dataset.theme === 'light'
        || document.body.dataset.theme === 'light';
}

function theme() {
    const light = isLight();
    return {
        text: light ? '#1A1730' : '#F4F2FF',
        muted: light ? '#6B649F' : '#A6A0C7',
        grid: light ? 'rgba(26, 23, 48, 0.10)' : 'rgba(244, 242, 255, 0.10)',
        tooltipBg: light ? '#181320' : '#241C30',
        tooltipText: '#FFF9F2',
    };
}

function baseOptions(extra = {}) {
    const t = theme();
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: t.muted,
                    font: { family: 'Rajdhani, Saira, sans-serif', size: 12, weight: '600' },
                    boxWidth: 10,
                    boxHeight: 10,
                    usePointStyle: true,
                    pointStyle: 'circle',
                },
            },
            tooltip: {
                backgroundColor: t.tooltipBg,
                titleColor: t.tooltipText,
                bodyColor: t.tooltipText,
                padding: 10,
                cornerRadius: 10,
                titleFont: { family: 'Rajdhani, Saira, sans-serif', size: 13, weight: '700' },
                bodyFont: { family: 'Saira, sans-serif', size: 12 },
            },
        },
        ...extra,
    };
}

function doughnut(el, rows) {
    return new Chart(el, {
        type: 'doughnut',
        data: {
            labels: rows.map((r) => r.label),
            datasets: [{
                data: rows.map((r) => r.count),
                backgroundColor: PALETTE,
                borderColor: 'transparent',
                borderWidth: 0,
                hoverOffset: 8,
            }],
        },
        options: baseOptions({
            cutout: '62%',
            plugins: {
                ...baseOptions().plugins,
                legend: { ...baseOptions().plugins.legend, position: 'right' },
            },
        }),
    });
}

function hbar(el, rows) {
    const t = theme();
    return new Chart(el, {
        type: 'bar',
        data: {
            labels: rows.map((r) => r.label),
            datasets: [{
                data: rows.map((r) => r.count),
                backgroundColor: PALETTE[0],
                borderRadius: 8,
                barThickness: 14,
            }],
        },
        options: baseOptions({
            indexAxis: 'y',
            plugins: {
                ...baseOptions().plugins,
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { color: t.grid, drawBorder: false },
                    ticks: {
                        color: t.muted,
                        font: { family: 'Saira, sans-serif', size: 11 },
                        precision: 0,
                    },
                },
                y: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        color: t.muted,
                        font: { family: 'Rajdhani, sans-serif', size: 11, weight: '600' },
                    },
                },
            },
        }),
    });
}

function area(el, rows) {
    const t = theme();
    return new Chart(el, {
        type: 'line',
        data: {
            labels: rows.map((r) => r.label),
            datasets: [{
                label: 'New users',
                data: rows.map((r) => r.users),
                borderColor: PALETTE[0],
                backgroundColor: 'rgba(255, 146, 46, 0.16)',
                fill: true,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: PALETTE[0],
                borderWidth: 2.5,
            }],
        },
        options: baseOptions({
            plugins: {
                ...baseOptions().plugins,
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        color: t.muted,
                        maxTicksLimit: 8,
                        font: { family: 'Saira, sans-serif', size: 10 },
                    },
                },
                y: {
                    grid: { color: t.grid, drawBorder: false },
                    ticks: {
                        color: t.muted,
                        precision: 0,
                        font: { family: 'Saira, sans-serif', size: 11 },
                    },
                },
            },
        }),
    });
}

function bars(el, rows, color = PALETTE[4]) {
    const t = theme();
    return new Chart(el, {
        type: 'bar',
        data: {
            labels: rows.map((r) => r.label),
            datasets: [{
                data: rows.map((r) => r.count),
                backgroundColor: rows.map((_, i) => PALETTE[i % PALETTE.length]),
                borderRadius: 10,
                maxBarThickness: 42,
            }],
        },
        options: baseOptions({
            plugins: {
                ...baseOptions().plugins,
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        color: t.muted,
                        font: { family: 'Rajdhani, sans-serif', size: 11, weight: '600' },
                    },
                },
                y: {
                    grid: { color: t.grid, drawBorder: false },
                    ticks: {
                        color: t.muted,
                        precision: 0,
                        font: { family: 'Saira, sans-serif', size: 11 },
                    },
                },
            },
        }),
    });
}

function boot() {
    const host = document.getElementById('fh-dashboard-data');
    if (!host) return;

    let data;
    try {
        data = JSON.parse(host.textContent || '{}');
    } catch (e) {
        return;
    }

    const mount = (id, fn, rows) => {
        const el = document.getElementById(id);
        if (!el || !Array.isArray(rows) || rows.length === 0) return;
        fn(el, rows);
    };

    mount('chart-users-role', doughnut, data.usersByRole);
    mount('chart-content-type', bars, data.contentByType);
    mount('chart-chatbot-questions', hbar, data.chatbotTopQuestions);
    mount('chart-user-growth', area, data.growth);
    mount('chart-content-status', doughnut, data.contentByStatus);
    mount('chart-reviews-status', bars, data.reviewsByStatus);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
