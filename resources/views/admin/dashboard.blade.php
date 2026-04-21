@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
<section class="card patient-hero">
    <div>
        <p class="hero-kicker">Admin Console</p>
        <h2>Clinic Operations Overview</h2>
        <p>Monitor appointments, manage users, and keep daily operations flowing smoothly.</p>
    </div>
</section>

<section class="grid cards-4">
    <article class="card"><h3>Total Patients</h3><p class="kpi-value">{{ $stats['total_patients'] }}</p></article>
    <article class="card dentist-kpi-card">
        <h3>Total Dentists</h3>
        <div
            class="dentist-kpi js-dentist-kpi"
            tabindex="0"
            role="button"
            aria-expanded="false"
            aria-controls="dentist-kpi-tooltip"
            aria-label="Hover or click to view dentist names and specializations"
        >
            <p class="kpi-value">{{ $stats['total_dentists'] }}</p>
            <div class="dentist-kpi-tooltip" id="dentist-kpi-tooltip" role="tooltip">
                @forelse ($dentists as $dentist)
                    <div class="dentist-kpi-item">
                        <strong>{{ $dentist->name }}</strong>
                        <span>{{ $dentist->dentistProfile?->specialty ?? 'General Dentistry' }}</span>
                    </div>
                @empty
                    <p class="dentist-kpi-empty">No dentists available.</p>
                @endforelse
            </div>
        </div>
    </article>
    <article class="card pending-kpi-card">
        <h3>Today Appointments</h3>
        <div
            class="reservation-kpi js-reservation-kpi"
            tabindex="0"
            role="button"
            aria-expanded="false"
            aria-controls="today-kpi-tooltip"
            aria-label="Hover or click to view today appointment details"
        >
            <p class="kpi-value">{{ $stats['today_appointments'] }}</p>
            <div class="reservation-kpi-tooltip" id="today-kpi-tooltip" role="tooltip">
                @forelse ($todayAppointments as $appointment)
                    <div class="reservation-kpi-item">
                        <strong>{{ $appointment->patient->name }}</strong>
                        <span>
                            Dr. {{ $appointment->dentist->name }}
                            <small>{{ $appointment->scheduled_for->format('M d, Y h:i A') }}</small>
                        </span>
                    </div>
                @empty
                    <p class="reservation-kpi-empty">No patients scheduled for today.</p>
                @endforelse
            </div>
        </div>
    </article>
    <article class="card pending-kpi-card">
        <h3>Upcoming Appointments</h3>
        <div
            class="reservation-kpi js-reservation-kpi"
            tabindex="0"
            role="button"
            aria-expanded="false"
            aria-controls="upcoming-kpi-tooltip"
            aria-label="Hover or click to view upcoming appointment details"
        >
            <p class="kpi-value">{{ $stats['upcoming_appointments'] }}</p>
            <div class="reservation-kpi-tooltip" id="upcoming-kpi-tooltip" role="tooltip">
                @forelse ($upcomingAppointments as $appointment)
                    <div class="reservation-kpi-item">
                        <strong>{{ $appointment->patient->name }}</strong>
                        <span>
                            Dr. {{ $appointment->dentist->name }}
                            <small>{{ $appointment->scheduled_for->format('M d, Y h:i A') }}</small>
                        </span>
                    </div>
                @empty
                    <p class="reservation-kpi-empty">No upcoming appointments.</p>
                @endforelse
            </div>
        </div>
    </article>
</section>

<section
    class="card service-analytics-card"
    id="service-analytics-card"
    data-service-chart='@json($serviceChartData)'
    data-service-period-label="{{ $servicePeriodLabel }}"
>
    <div class="service-analytics-head">
        <div>
            <h2>Dental Clinic Services Utilization</h2>
            <p>Frequency of services rendered, with color coding by service category.</p>
            <p class="service-analytics-period">Reporting period: {{ $servicePeriodLabel }}</p>
        </div>

        <div class="service-analytics-controls">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="service-period-form">
                <label for="service_period">Filter Period</label>
                <select id="service_period" name="service_period">
                    @foreach ($servicePeriodOptions as $periodValue => $periodText)
                        <option value="{{ $periodValue }}" @selected($selectedServicePeriod === $periodValue)>{{ $periodText }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="trend_period" value="{{ $selectedTrendPeriod }}">
                <button type="submit" class="btn btn-ghost">Apply</button>
            </form>

            @if (count($serviceChartData) > 0)
                <div class="service-export-actions">
                    <button type="button" class="btn btn-ghost service-export-btn" id="export-service-chart">Export PNG</button>
                    <button type="button" class="btn btn-ghost service-export-btn" id="export-service-csv">Export CSV</button>
                </div>
            @endif
        </div>
    </div>

    @if (count($serviceChartData) > 0)
        <div class="service-analytics-canvas-wrap">
            <canvas id="service-frequency-chart" aria-label="Bar graph of dental clinic service frequency" role="img"></canvas>
        </div>

        <div class="service-legend" aria-label="Service category legend">
            @foreach ($categoryColors as $category => $color)
                <span class="service-legend-item">
                    <span class="service-legend-swatch" data-color="{{ $color }}"></span>
                    <span>{{ $category }}</span>
                </span>
            @endforeach
        </div>
    @else
        <p class="service-analytics-empty">No service data available yet. Once appointments are booked with services, this chart will display utilization trends.</p>
    @endif
</section>

<section
    class="card reservation-trend-card"
    id="reservation-trend-card"
    data-reservation-trend='@json($onlineReservationTrendData)'
    data-trend-period-label="{{ $trendPeriodLabel }}"
    data-reservation-x-axis="{{ $onlineReservationXAxisLabel }}"
>
    <div class="reservation-trend-head">
        <div>
            <h2>Online Reservation Patient Trend</h2>
            <p>Number of online reservations versus completed appointments over {{ strtolower($trendPeriodLabel) }}.</p>
        </div>

        <div class="reservation-trend-controls">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="service-period-form">
                <label for="trend_period">Filter Period</label>
                <select id="trend_period" name="trend_period">
                    @foreach ($servicePeriodOptions as $periodValue => $periodText)
                        <option value="{{ $periodValue }}" @selected($selectedTrendPeriod === $periodValue)>{{ $periodText }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="service_period" value="{{ $selectedServicePeriod }}">
                <button type="submit" class="btn btn-ghost">Apply</button>
            </form>

            @if (count($onlineReservationTrendData) > 0)
                <button type="button" class="btn btn-ghost service-export-btn" id="export-reservation-trend-csv">Export Trend CSV</button>
            @endif
        </div>
    </div>

    @if (count($onlineReservationTrendData) > 0)
        <div class="reservation-trend-canvas-wrap">
            <canvas id="reservation-trend-chart" aria-label="Line graph of online patient reservations over time" role="img"></canvas>
        </div>
    @else
        <p class="service-analytics-empty">No reservation records available for the selected period.</p>
    @endif
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggles = Array.from(document.querySelectorAll('.js-dentist-kpi, .js-reservation-kpi'));
    const analyticsCard = document.getElementById('service-analytics-card');
    const chartCanvas = document.getElementById('service-frequency-chart');
    const exportButton = document.getElementById('export-service-chart');
    const exportCsvButton = document.getElementById('export-service-csv');
    const reservationTrendCard = document.getElementById('reservation-trend-card');
    const reservationTrendCanvas = document.getElementById('reservation-trend-chart');

    if (!toggles.length) {
        return;
    }

    const closeAll = function () {
        toggles.forEach((toggle) => {
            toggle.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', function (event) {
            event.stopPropagation();

            const isOpen = toggle.classList.contains('is-open');
            closeAll();

            if (!isOpen) {
                toggle.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });

        toggle.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            toggle.click();
        });
    });

    document.addEventListener('click', function (event) {
        if (!toggles.some((toggle) => toggle.contains(event.target))) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    if (!analyticsCard || !chartCanvas || typeof window.Chart === 'undefined') {
        return;
    }

    let serviceChartData = [];
    try {
        serviceChartData = JSON.parse(analyticsCard.getAttribute('data-service-chart') || '[]');
    } catch (error) {
        serviceChartData = [];
    }

    if (!serviceChartData.length) {
        return;
    }

    Array.from(document.querySelectorAll('.service-legend-swatch[data-color]')).forEach((swatch) => {
        const color = swatch.getAttribute('data-color');
        if (color) {
            swatch.style.backgroundColor = color;
        }
    });

    if (window.ChartDataLabels) {
        window.Chart.register(window.ChartDataLabels);
    }

    const labels = serviceChartData.map((item) => item.service);
    const frequencies = serviceChartData.map((item) => Number(item.count || 0));
    const colors = serviceChartData.map((item) => item.color || '#157F7B');
    const categories = serviceChartData.map((item) => item.category || 'General');
    const servicePeriodLabel = analyticsCard.getAttribute('data-service-period-label') || 'Selected Period';

    const serviceChart = new window.Chart(chartCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Service Frequency',
                    data: frequencies,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 54,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    right: 12,
                },
            },
            scales: {
                x: {
                    ticks: {
                        color: '#325462',
                        maxRotation: 35,
                        minRotation: 20,
                    },
                    grid: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: 'Types of Dental Services',
                        color: '#21414e',
                        font: {
                            weight: '700',
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#325462',
                    },
                    grid: {
                        color: 'rgba(49, 93, 109, 0.13)',
                    },
                    title: {
                        display: true,
                        text: 'Number of Patients / Service Frequency',
                        color: '#21414e',
                        font: {
                            weight: '700',
                        },
                    },
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: 'Service Frequency by Category and Service Name (' + servicePeriodLabel + ')',
                    color: '#163a45',
                    font: {
                        size: 16,
                        weight: '700',
                    },
                    padding: {
                        bottom: 12,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const category = categories[context.dataIndex] || 'General';
                            return 'Category: ' + category + ' | Frequency: ' + context.raw;
                        },
                    },
                },
                datalabels: {
                    color: '#163a45',
                    anchor: 'end',
                    align: 'top',
                    offset: 2,
                    font: {
                        weight: '700',
                    },
                    formatter: function (value) {
                        return value;
                    },
                },
            },
        },
    });

    if (exportButton) {
        exportButton.addEventListener('click', function () {
            const tempCanvas = document.createElement('canvas');
            const context = tempCanvas.getContext('2d');

            if (!context) {
                return;
            }

            tempCanvas.width = chartCanvas.width;
            tempCanvas.height = chartCanvas.height;

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            context.drawImage(chartCanvas, 0, 0);

            const periodSegment = servicePeriodLabel.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            const now = new Date();
            const stamp = now.getFullYear().toString() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0');
            const link = document.createElement('a');
            link.href = tempCanvas.toDataURL('image/png');
            link.download = 'service-frequency-' + (periodSegment || 'period') + '-' + stamp + '.png';
            link.click();

            // Keep the chart responsive after export in older browsers.
            serviceChart.resize();
        });
    }

    if (exportCsvButton) {
        exportCsvButton.addEventListener('click', function () {
            const rows = [
                ['Service Name', 'Category', 'Frequency'],
                ...serviceChartData.map((item) => [item.service || '', item.category || 'General', String(item.count || 0)]),
            ];

            const csvContent = rows
                .map((row) => row.map((value) => '"' + String(value).replace(/"/g, '""') + '"').join(','))
                .join('\r\n');

            const periodSegment = servicePeriodLabel.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            const now = new Date();
            const stamp = now.getFullYear().toString() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0');
            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'service-frequency-' + (periodSegment || 'period') + '-' + stamp + '.csv';
            link.click();
            URL.revokeObjectURL(url);
        });
    }

    if (!reservationTrendCard || !reservationTrendCanvas || typeof window.Chart === 'undefined') {
        return;
    }

    let reservationTrendData = [];
    try {
        reservationTrendData = JSON.parse(reservationTrendCard.getAttribute('data-reservation-trend') || '[]');
    } catch (error) {
        reservationTrendData = [];
    }

    if (!reservationTrendData.length) {
        return;
    }

    const trendLabels = reservationTrendData.map((item) => item.label);
    const trendCounts = reservationTrendData.map((item) => Number(item.count || 0));
    const trendCompletedCounts = reservationTrendData.map((item) => Number(item.completed_count || 0));
    const trendPeriodLabel = reservationTrendCard.getAttribute('data-trend-period-label') || 'Selected Period';
    const trendXAxisLabel = reservationTrendCard.getAttribute('data-reservation-x-axis') || 'Time Period';
    const trendCsvButton = document.getElementById('export-reservation-trend-csv');

    new window.Chart(reservationTrendCanvas, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Online Reservations',
                    data: trendCounts,
                    borderColor: '#1A7F9C',
                    backgroundColor: 'rgba(26, 127, 156, 0.16)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#1A7F9C',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'Completed Appointments',
                    data: trendCompletedCounts,
                    borderColor: '#2C8C6B',
                    backgroundColor: 'rgba(44, 140, 107, 0.10)',
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2C8C6B',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    borderWidth: 3,
                    tension: 0.35,
                    fill: false,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: {
                        color: '#325462',
                        maxRotation: 30,
                        minRotation: 0,
                    },
                    grid: {
                        color: 'rgba(49, 93, 109, 0.08)',
                    },
                    title: {
                        display: true,
                        text: trendXAxisLabel,
                        color: '#21414e',
                        font: {
                            weight: '700',
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#325462',
                    },
                    grid: {
                        color: 'rgba(49, 93, 109, 0.14)',
                    },
                    title: {
                        display: true,
                        text: 'Number of Patients (Online Reservations)',
                        color: '#21414e',
                        font: {
                            weight: '700',
                        },
                    },
                },
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#2a4f5d',
                        boxWidth: 14,
                        usePointStyle: true,
                        pointStyle: 'circle',
                    },
                },
                title: {
                    display: true,
                    text: 'Online Patient Reservations vs Completed Appointments (' + trendPeriodLabel + ')',
                    color: '#163a45',
                    font: {
                        size: 16,
                        weight: '700',
                    },
                    padding: {
                        bottom: 12,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + context.raw;
                        },
                    },
                },
                datalabels: {
                    color: '#1b4f61',
                    align: 'top',
                    anchor: 'end',
                    offset: 2,
                    font: {
                        weight: '700',
                    },
                    display: function (context) {
                        return context.datasetIndex === 0;
                    },
                    formatter: function (value) {
                        return value;
                    },
                },
            },
        },
    });

    if (trendCsvButton) {
        trendCsvButton.addEventListener('click', function () {
            const trendRows = [
                [trendXAxisLabel, 'Online Reservations', 'Completed Appointments'],
                ...reservationTrendData.map((item) => [item.label || '', String(item.count || 0), String(item.completed_count || 0)]),
            ];

            const trendCsvContent = trendRows
                .map((row) => row.map((value) => '"' + String(value).replace(/"/g, '""') + '"').join(','))
                .join('\r\n');

            const periodSegment = trendPeriodLabel.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            const now = new Date();
            const stamp = now.getFullYear().toString() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0');
            const blob = new Blob(['\uFEFF' + trendCsvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'online-reservation-trend-' + (periodSegment || 'period') + '-' + stamp + '.csv';
            link.click();
            URL.revokeObjectURL(url);
        });
    }
});
</script>
@endsection
