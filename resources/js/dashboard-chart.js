import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const chartPalette = {
    masuk: { bg: 'rgba(5, 150, 105, 0.85)', border: '#047857' },
    telat: { bg: 'rgba(234, 88, 12, 0.85)', border: '#c2410c' },
    izin: { bg: 'rgba(2, 132, 199, 0.85)', border: '#0369a1' },
    ga_masuk: { bg: 'rgba(100, 116, 139, 0.85)', border: '#475569' },
};

const chartFont = {
    family: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif",
    size: 13,
    weight: '600',
};

let weeklyChart = null;
let todayChart = null;

function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function chartTheme() {
    const dark = isDarkMode();

    return {
        text: dark ? '#e2e8f0' : '#0f172a',
        textMuted: dark ? '#94a3b8' : '#334155',
        grid: dark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(100, 116, 139, 0.2)',
        doughnutBorder: dark ? '#151d2e' : '#ffffff',
        tooltipBg: dark ? '#1e293b' : '#ffffff',
        tooltipText: dark ? '#e2e8f0' : '#0f172a',
    };
}

function destroyDashboardCharts() {
    weeklyChart?.destroy();
    todayChart?.destroy();
    weeklyChart = null;
    todayChart = null;
}

function baseOptions(isStacked = false, chartLabels = {}) {
    const theme = chartTheme();

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 14,
                    boxHeight: 14,
                    padding: 16,
                    font: { ...chartFont, size: 14, weight: '700' },
                    color: theme.text,
                },
            },
            tooltip: {
                titleFont: { ...chartFont, weight: '700' },
                bodyFont: chartFont,
                padding: 12,
                backgroundColor: theme.tooltipBg,
                titleColor: theme.tooltipText,
                bodyColor: theme.tooltipText,
                callbacks: isStacked
                    ? {
                          footer: (items) => {
                              const total = items.reduce((sum, item) => sum + (item.parsed.y ?? 0), 0);
                              return `${chartLabels.total ?? 'Total'}: ${total}`;
                          },
                      }
                    : undefined,
            },
        },
        scales: isStacked
            ? {
                  x: {
                      stacked: true,
                      grid: { display: false },
                      ticks: { font: chartFont, color: theme.textMuted },
                  },
                  y: {
                      stacked: true,
                      beginAtZero: true,
                      grid: { color: theme.grid },
                      ticks: {
                          stepSize: 1,
                          font: chartFont,
                          color: theme.textMuted,
                      },
                      title: {
                          display: true,
                          text: chartLabels.employees ?? 'Employees',
                          font: { ...chartFont, weight: '700' },
                          color: theme.textMuted,
                      },
                  },
              }
            : undefined,
    };
}

function readChartPayload() {
    const root = document.getElementById('dashboard-chart-root');

    if (!root) {
        return null;
    }

    try {
        return {
            data: JSON.parse(root.dataset.chart ?? 'null'),
            chartLabels: JSON.parse(root.dataset.labels ?? '{}'),
        };
    } catch (e) {
        console.error('Dashboard chart: invalid payload', e);

        return null;
    }
}

function initDashboardCharts() {
    const payload = readChartPayload();

    if (!payload?.data) {
        return;
    }

    const { data, chartLabels } = payload;

    destroyDashboardCharts();

    const theme = chartTheme();
    const seriesLabels = {
        masuk: chartLabels.present ?? 'Present',
        telat: chartLabels.late ?? 'Late',
        izin: chartLabels.permission ?? 'Permission',
        ga_masuk: chartLabels.absent ?? 'Absent',
    };

    const weeklyCanvas = document.getElementById('attendance-weekly-chart');
    if (weeklyCanvas) {
        weeklyChart = new Chart(weeklyCanvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: seriesLabels.masuk,
                        data: data.series.masuk,
                        backgroundColor: chartPalette.masuk.bg,
                        borderColor: chartPalette.masuk.border,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: seriesLabels.telat,
                        data: data.series.telat,
                        backgroundColor: chartPalette.telat.bg,
                        borderColor: chartPalette.telat.border,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: seriesLabels.izin,
                        data: data.series.izin,
                        backgroundColor: chartPalette.izin.bg,
                        borderColor: chartPalette.izin.border,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: seriesLabels.ga_masuk,
                        data: data.series.ga_masuk,
                        backgroundColor: chartPalette.ga_masuk.bg,
                        borderColor: chartPalette.ga_masuk.border,
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: baseOptions(true, chartLabels),
        });
    }

    const todayCanvas = document.getElementById('attendance-today-chart');
    if (todayCanvas) {
        const today = data.today;
        todayChart = new Chart(todayCanvas, {
            type: 'doughnut',
            data: {
                labels: [seriesLabels.masuk, seriesLabels.telat, seriesLabels.izin, seriesLabels.ga_masuk],
                datasets: [
                    {
                        data: [today.masuk, today.telat, today.izin, today.ga_masuk],
                        backgroundColor: [
                            chartPalette.masuk.bg,
                            chartPalette.telat.bg,
                            chartPalette.izin.bg,
                            chartPalette.ga_masuk.bg,
                        ],
                        borderColor: theme.doughnutBorder,
                        borderWidth: 3,
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                ...baseOptions(false, chartLabels),
                cutout: '58%',
                plugins: {
                    ...baseOptions(false, chartLabels).plugins,
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }
}

function bootDashboardCharts() {
    const run = () => window.requestAnimationFrame(initDashboardCharts);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
        run();
    }

    window.addEventListener('theme:changed', () => {
        window.setTimeout(initDashboardCharts, 60);
    });
}

bootDashboardCharts();
