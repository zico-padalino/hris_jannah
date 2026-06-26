import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const BAR_RADIUS = 10;

const chartPalette = {
    masuk: { from: '#22c55e', to: '#16a34a', border: '#15803d' },
    telat: { from: '#FBB931', to: '#EC6014', border: '#c8510f' },
    izin: { from: '#38bdf8', to: '#0ea5e9', border: '#0284c7' },
    ga_masuk: { from: '#cbd5e1', to: '#94a3b8', border: '#64748b' },
};

const chartFont = {
    family: "'Sora', ui-sans-serif, system-ui, sans-serif",
    size: 12,
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
        text: dark ? '#e2e8f0' : '#1e293b',
        textMuted: dark ? '#94a3b8' : '#64748b',
        grid: dark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(148, 163, 184, 0.22)',
        doughnutBorder: dark ? '#1a2332' : '#ffffff',
        tooltipBg: dark ? 'rgba(30, 41, 59, 0.96)' : 'rgba(255, 255, 255, 0.98)',
        tooltipText: dark ? '#f1f5f9' : '#0f172a',
        tooltipBorder: dark ? 'rgba(148, 163, 184, 0.25)' : 'rgba(226, 232, 240, 0.9)',
    };
}

function stackBorderRadius(position) {
    const flat = { topLeft: 0, topRight: 0, bottomLeft: 0, bottomRight: 0 };
    const top = { topLeft: BAR_RADIUS, topRight: BAR_RADIUS, bottomLeft: 0, bottomRight: 0 };
    const bottom = { topLeft: 0, topRight: 0, bottomLeft: BAR_RADIUS, bottomRight: BAR_RADIUS };

    if (position === 'first') {
        return bottom;
    }

    if (position === 'last') {
        return top;
    }

    return flat;
}

function verticalGradient(ctx, area, palette) {
    const gradient = ctx.createLinearGradient(0, area.bottom, 0, area.top);
    gradient.addColorStop(0, palette.to);
    gradient.addColorStop(1, palette.from);

    return gradient;
}

function applyBarGradients(chart) {
    const { ctx, chartArea } = chart;

    if (!chartArea) {
        return;
    }

    const keys = ['masuk', 'telat', 'izin', 'ga_masuk'];

    chart.data.datasets.forEach((dataset, index) => {
        dataset.backgroundColor = verticalGradient(ctx, chartArea, chartPalette[keys[index]]);
        dataset.hoverBackgroundColor = verticalGradient(ctx, chartArea, chartPalette[keys[index]]);
    });
}

function applyDoughnutGradients(chart) {
    const { ctx, chartArea } = chart;

    if (!chartArea) {
        return;
    }

    const keys = ['masuk', 'telat', 'izin', 'ga_masuk'];
    const centerX = (chartArea.left + chartArea.right) / 2;
    const centerY = (chartArea.top + chartArea.bottom) / 2;
    const radius = Math.min(chartArea.right - chartArea.left, chartArea.bottom - chartArea.top) / 2;

    chart.data.datasets[0].backgroundColor = keys.map((key) => {
        const gradient = ctx.createRadialGradient(centerX, centerY, radius * 0.2, centerX, centerY, radius);
        gradient.addColorStop(0, chartPalette[key].from);
        gradient.addColorStop(1, chartPalette[key].to);

        return gradient;
    });
}

function getCanvasPointerPosition(chart, nativeEvent) {
    const rect = chart.canvas.getBoundingClientRect();
    const clientX = nativeEvent.touches?.[0]?.clientX ?? nativeEvent.clientX;
    const clientY = nativeEvent.touches?.[0]?.clientY ?? nativeEvent.clientY;

    return {
        x: clientX - rect.left,
        y: clientY - rect.top,
    };
}

function showPressHoldTooltipAt(chart, nativeEvent, mode = 'index', intersect = false) {
    const position = getCanvasPointerPosition(chart, nativeEvent);
    const elements = chart.getElementsAtEventForMode(
        { x: position.x, y: position.y, native: nativeEvent },
        mode,
        { intersect },
        false
    );

    if (elements.length === 0) {
        hidePressHoldTooltip(chart);

        return;
    }

    chart.setActiveElements(elements);
    chart.tooltip.setActiveElements(elements, position);
    chart.update('none');
}

function hidePressHoldTooltip(chart) {
    chart.setActiveElements([]);
    chart.tooltip.setActiveElements([]);
    chart.update('none');
}

function detachPressHoldTooltip(canvas) {
    canvas?._pressHoldAbort?.abort();
    canvas._pressHoldAbort = null;
}

function attachPressHoldTooltip(chart, canvas, { mode = 'index', intersect = false } = {}) {
    detachPressHoldTooltip(canvas);

    const abort = new AbortController();
    const { signal } = abort;

    canvas._pressHoldAbort = abort;

    const onPress = (event) => {
        event.preventDefault();
        showPressHoldTooltipAt(chart, event, mode, intersect);
    };

    const onRelease = () => {
        hidePressHoldTooltip(chart);
    };

    canvas.addEventListener('mousedown', onPress, { signal });
    canvas.addEventListener('touchstart', onPress, { passive: false, signal });
    canvas.addEventListener('mouseup', onRelease, { signal });
    canvas.addEventListener('mouseleave', onRelease, { signal });
    canvas.addEventListener('touchend', onRelease, { signal });
    canvas.addEventListener('touchcancel', onRelease, { signal });
}

function destroyDashboardCharts() {
    detachPressHoldTooltip(document.getElementById('attendance-weekly-chart'));
    detachPressHoldTooltip(document.getElementById('attendance-today-chart'));
    weeklyChart?.destroy();
    todayChart?.destroy();
    weeklyChart = null;
    todayChart = null;
}

function tooltipOptions(theme) {
    return {
        enabled: true,
        backgroundColor: theme.tooltipBg,
        titleColor: theme.tooltipText,
        bodyColor: theme.tooltipText,
        borderColor: theme.tooltipBorder,
        borderWidth: 1,
        cornerRadius: 12,
        padding: { top: 10, right: 14, bottom: 10, left: 14 },
        boxPadding: 6,
        boxWidth: 10,
        boxHeight: 10,
        usePointStyle: true,
        titleFont: { ...chartFont, size: 13, weight: '700' },
        bodyFont: { ...chartFont, size: 12 },
        footerFont: { ...chartFont, size: 12, weight: '700' },
        footerColor: theme.textMuted,
        displayColors: true,
    };
}

function legendOptions(theme) {
    return {
        position: 'bottom',
        align: 'center',
        labels: {
            usePointStyle: true,
            pointStyle: 'circle',
            boxWidth: 8,
            boxHeight: 8,
            padding: 20,
            font: { ...chartFont, size: 13, weight: '600' },
            color: theme.text,
        },
    };
}

function baseOptions(isStacked = false, chartLabels = {}) {
    const theme = chartTheme();

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 900,
            easing: 'easeOutQuart',
        },
        interaction: {
            mode: isStacked ? 'index' : 'nearest',
            intersect: false,
        },
        plugins: {
            legend: legendOptions(theme),
            tooltip: {
                ...tooltipOptions(theme),
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
                      border: { display: false },
                      grid: { display: false },
                      ticks: {
                          font: { ...chartFont, weight: '600' },
                          color: theme.textMuted,
                          padding: 8,
                      },
                  },
                  y: {
                      stacked: true,
                      beginAtZero: true,
                      border: { display: false, dash: [4, 4] },
                      grid: {
                          color: theme.grid,
                          drawTicks: false,
                      },
                      ticks: {
                          stepSize: 1,
                          font: chartFont,
                          color: theme.textMuted,
                          padding: 10,
                      },
                      title: {
                          display: true,
                          text: chartLabels.employees ?? 'Employees',
                          font: { ...chartFont, weight: '700' },
                          color: theme.textMuted,
                          padding: { bottom: 8 },
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
        izin: chartLabels.permission ?? 'Izin',
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
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderSkipped: false,
                        borderRadius: stackBorderRadius('first'),
                        barPercentage: 0.62,
                        categoryPercentage: 0.72,
                    },
                    {
                        label: seriesLabels.telat,
                        data: data.series.telat,
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderSkipped: false,
                        borderRadius: stackBorderRadius('middle'),
                        barPercentage: 0.62,
                        categoryPercentage: 0.72,
                    },
                    {
                        label: seriesLabels.izin,
                        data: data.series.izin,
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderSkipped: false,
                        borderRadius: stackBorderRadius('middle'),
                        barPercentage: 0.62,
                        categoryPercentage: 0.72,
                    },
                    {
                        label: seriesLabels.ga_masuk,
                        data: data.series.ga_masuk,
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderSkipped: false,
                        borderRadius: stackBorderRadius('last'),
                        barPercentage: 0.62,
                        categoryPercentage: 0.72,
                    },
                ],
            },
            options: {
                ...baseOptions(true, chartLabels),
                events: [],
                hover: {
                    enabled: false,
                },
                plugins: {
                    ...baseOptions(true, chartLabels).plugins,
                    legend: {
                        display: false,
                    },
                },
            },
            plugins: [
                {
                    id: 'weeklyBarGradients',
                    beforeDatasetsDraw(chart) {
                        applyBarGradients(chart);
                    },
                },
            ],
        });

        attachPressHoldTooltip(weeklyChart, weeklyCanvas, { mode: 'index', intersect: false });
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
                            chartPalette.masuk.from,
                            chartPalette.telat.from,
                            chartPalette.izin.from,
                            chartPalette.ga_masuk.from,
                        ],
                        borderColor: theme.doughnutBorder,
                        borderWidth: 4,
                        spacing: 3,
                        borderRadius: 8,
                        hoverOffset: 10,
                        hoverBorderWidth: 4,
                    },
                ],
            },
            options: {
                ...baseOptions(false, chartLabels),
                cutout: '62%',
                events: [],
                hover: {
                    enabled: false,
                },
                plugins: {
                    ...baseOptions(false, chartLabels).plugins,
                    legend: {
                        display: false,
                    },
                },
            },
            plugins: [
                {
                    id: 'todayDoughnutGradients',
                    beforeDatasetsDraw(chart) {
                        applyDoughnutGradients(chart);
                    },
                },
            ],
        });

        attachPressHoldTooltip(todayChart, todayCanvas, { mode: 'nearest', intersect: true });
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
