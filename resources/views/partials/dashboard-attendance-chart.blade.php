@if($showAttendanceChart ?? false)
    @php
        $dashboardChartLabels = [
            'present' => __('pages.dashboard.present'),
            'late' => __('pages.dashboard.late'),
            'permission' => __('pages.dashboard.permission'),
            'absent' => __('pages.dashboard.absent'),
            'employees' => __('pages.dashboard.chart_employees'),
            'total' => __('pages.dashboard.chart_total'),
        ];
    @endphp

    <div
        id="dashboard-chart-root"
        class="panel mt-8 overflow-hidden"
        data-chart='@json($attendanceChart)'
        data-labels='@json($dashboardChartLabels)'
    >
        <div class="dashboard-section-head border-b-2 px-4 py-4 sm:px-6">
            <h2 class="dashboard-section-title">{{ __('pages.dashboard.chart_title') }}</h2>
            <p class="dashboard-section-subtitle mt-1">{{ __('pages.dashboard.chart_subtitle') }}</p>
        </div>

        <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="relative h-72 w-full sm:h-80">
                    <canvas id="attendance-weekly-chart" aria-label="{{ __('pages.dashboard.chart_title') }}"></canvas>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div>
                    <h3 class="dashboard-section-title text-base">{{ __('app.today') }}</h3>
                    <p class="dashboard-section-subtitle mt-0.5 text-sm">
                        {{ $attendanceChart['today']['total_employees'] }} {{ __('app.employees_count') }}
                    </p>
                </div>

                <div class="relative mx-auto h-48 w-full max-w-xs sm:h-52">
                    <canvas id="attendance-today-chart" aria-label="{{ __('app.today') }}"></canvas>
                </div>

                <ul class="grid grid-cols-2 gap-3 text-sm">
                    <li class="dashboard-mini-stat dashboard-mini-stat--present">
                        <p class="dashboard-mini-stat__label">{{ __('pages.dashboard.present') }}</p>
                        <p class="dashboard-mini-stat__value">{{ $attendanceChart['today']['masuk'] }}</p>
                    </li>
                    <li class="dashboard-mini-stat dashboard-mini-stat--late">
                        <p class="dashboard-mini-stat__label">{{ __('pages.dashboard.late') }}</p>
                        <p class="dashboard-mini-stat__value">{{ $attendanceChart['today']['telat'] }}</p>
                    </li>
                    <li class="dashboard-mini-stat dashboard-mini-stat--permission">
                        <p class="dashboard-mini-stat__label">{{ __('pages.dashboard.permission') }}</p>
                        <p class="dashboard-mini-stat__value">{{ $attendanceChart['today']['izin'] }}</p>
                    </li>
                    <li class="dashboard-mini-stat dashboard-mini-stat--absent">
                        <p class="dashboard-mini-stat__label">{{ __('pages.dashboard.absent') }}</p>
                        <p class="dashboard-mini-stat__value">{{ $attendanceChart['today']['ga_masuk'] }}</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/dashboard-chart.js')
    @endpush
@endif
