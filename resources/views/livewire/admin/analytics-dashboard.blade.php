<div class="py-6 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header & Filters -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('Analytics Dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Overview of attendance performance and statistics') }}
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="w-full sm:w-40">
                    <x-tom-select wire:model.live="month" placeholder="{{ __('Select Month') }}" class="w-full">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                        @endforeach
                    </x-tom-select>
                </div>
                <div class="w-full sm:w-32">
                     <x-tom-select wire:model.live="year" placeholder="{{ __('Select Year') }}" class="w-full">
                        @foreach(range(date('Y')-1, date('Y')) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </x-tom-select>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Line Chart (Trend) -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Attendance Trend') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Daily attendance over the month') }}</p>
                    </div>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Division Performance -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Division Performance') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total present count by division') }}</p>
                    </div>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="divisionChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
               <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Distribution') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Overall status breakdown') }}</p>
                    </div>
                </div>
                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

             <!-- Late Severity -->
             <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                     <div>
                         <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Late Severity') }}</h3>
                         <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('How late are employees?') }}</p>
                     </div>
                 </div>
                 <div class="relative h-64 w-full flex justify-center">
                     <canvas id="lateChart"></canvas>
                 </div>
             </div>

             <!-- Absence Reasons -->
             <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                     <div>
                         <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Absence Reasons') }}</h3>
                         <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Breakdown of non-presence') }}</p>
                     </div>
                 </div>
                 <div class="relative h-64 w-full flex justify-center">
                     <canvas id="absentChart"></canvas>
                 </div>
             </div>
        </div>

        <!-- Leaderboards Grid -->
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 px-1">{{ __('Top Performers') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Top Diligent -->
            <div class="bg-gradient-to-br from-white to-green-50/50 dark:from-gray-800 dark:to-green-900/10 p-6 rounded-3xl shadow-xl border border-green-100 dark:border-green-900/30">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-green-100 text-green-600 dark:bg-green-900/50 dark:text-green-400 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Most Diligent') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('Earliest avg arrival') }}</p>
                    </div>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-green-100 dark:divide-gray-700">
                        @forelse($topDiligent as $index => $employee)
                            <li class="py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 relative">
                                        <img class="w-10 h-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800" src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}">
                                        @if($index < 3)
                                            <span class="absolute -top-1 -right-1 text-sm filter drop-shadow-md">
                                                {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                                            {{ $employee->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate dark:text-gray-400">
                                            {{ $employee->jobTitle?->name ?? 'Employee' }}
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ gmdate('H:i', $employee->avg_check_in) }}
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-6 italic">{{ __('No data available') }}</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Top Late -->
            <div class="bg-gradient-to-br from-white to-red-50/50 dark:from-gray-800 dark:to-red-900/10 p-6 rounded-3xl shadow-xl border border-red-100 dark:border-red-900/30">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400 rounded-2xl">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Most Late') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('Highest late count') }}</p>
                    </div>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-red-100 dark:divide-gray-700">
                        @forelse($topLate as $index => $employee)
                            <li class="py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 relative">
                                        <img class="w-10 h-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800" src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}">
                                        @if($index < 3)
                                             <span class="absolute -top-1 -right-1 text-sm">⚠️</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                                            {{ $employee->name }}
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        {{ $employee->late_count }}x
                                    </div>
                                </div>
                            </li>
                        @empty
                             <div class="text-center py-6">
                                <span class="text-4xl">🎉</span>
                                <p class="text-sm text-gray-500 mt-2">{{ __('Everyone is on time!') }}</p>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Top Early Leavers -->
            <div class="bg-gradient-to-br from-white to-amber-50/50 dark:from-gray-800 dark:to-amber-900/10 p-6 rounded-3xl shadow-xl border border-amber-100 dark:border-amber-900/30">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Early Leavers') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('Checkout before time') }}</p>
                    </div>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-amber-100 dark:divide-gray-700">
                        @forelse($topEarlyLeavers as $index => $employee)
                            <li class="py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 relative">
                                        <img class="w-10 h-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800" src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                                            {{ $employee->name }}
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                        {{ $employee->early_leave_count }}x
                                    </div>
                                </div>
                            </li>
                        @empty
                            <div class="text-center py-6">
                                <span class="text-4xl">👏</span>
                                <p class="text-sm text-gray-500 mt-2">{{ __('Full attendance!') }}</p>
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <div id="analytics-chart-script">
    <script>
        document.addEventListener('livewire:navigated', () => {
             let myTrendChart = null;
             let myStatusChart = null;
             let myDivisionChart = null;
             let myLateChart = null;
             let myAbsentChart = null;

             const translations = {
                'present': '{{ __("Present") }}',
                'late': '{{ __("Late") }}',
                'sick': '{{ __("Sick") }}',
                'excused': '{{ __("Excused") }}',
                'absent': '{{ __("Absent") }}',
                'alpha': '{{ __("Alpha") }}'
             };

             const translateStatus = (key) => translations[key.toLowerCase()] || key;

             const initCharts = (trendData, metricsData, divisionData, lateData, absentData) => {
                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js not loaded yet, retrying...');
                    setTimeout(() => initCharts(trendData, metricsData, divisionData, lateData, absentData), 100);
                    return;
                }

                // Destroy existing charts if needed
                if (myTrendChart) myTrendChart.destroy();
                if (myStatusChart) myStatusChart.destroy();
                if (myDivisionChart) myDivisionChart.destroy();
                if (myLateChart) myLateChart.destroy();
                if (myAbsentChart) myAbsentChart.destroy();

                // Trend Chart
                const ctxTrend = document.getElementById('trendChart');
                if (ctxTrend) {
                    const gradientPresent = ctxTrend.getContext('2d').createLinearGradient(0, 0, 0, 400);
                    gradientPresent.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                    gradientPresent.addColorStop(1, 'rgba(16, 185, 129, 0)');

                    myTrendChart = new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: trendData.labels,
                            datasets: [
                                {
                                    label: '{{ __("Present") }}',
                                    data: trendData.present,
                                    borderColor: '#10B981', 
                                    backgroundColor: gradientPresent,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointHoverRadius: 6
                                },
                                {
                                    label: '{{ __("Late") }}',
                                    data: trendData.late,
                                    borderColor: '#EF4444', 
                                    borderDash: [5, 5],
                                    tension: 0.4,
                                    pointRadius: 0,
                                    fill: false
                                },
                                {
                                    label: '{{ __("Absent") }}',
                                    data: trendData.absent,
                                    borderColor: '#F59E0B',
                                    tension: 0.4,
                                    pointRadius: 0,
                                    fill: false
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                             plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end',
                                    labels: { usePointStyle: true, boxWidth: 6 }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                    titleColor: '#1F2937',
                                    bodyColor: '#4B5563',
                                    borderColor: '#E5E7EB',
                                    borderWidth: 1,
                                    padding: 10,
                                    displayColors: true,
                                    usePointStyle: true
                                }
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { grid: { borderDash: [2, 4], color: '#f3f4f6' }, beginAtZero: true }
                            }
                        }
                    });
                }

                 // Division Chart (Bar)
                 const ctxDivision = document.getElementById('divisionChart');
                 if (ctxDivision) {
                     myDivisionChart = new Chart(ctxDivision, {
                         type: 'bar',
                         data: {
                             labels: divisionData.labels,
                             datasets: [{
                                 label: '{{ __("Present Count") }}',
                                 data: divisionData.data,
                                 backgroundColor: '#3B82F6',
                                 borderRadius: 6,
                             }]
                         },
                         options: {
                             responsive: true,
                             maintainAspectRatio: false,
                             plugins: { legend: { display: false } }, // Hide legend as only 1 dataset
                             scales: {
                                 y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                                 x: { grid: { display: false } }
                             }
                         }
                     });
                 }

                // Status Chart
                const ctxStatus = document.getElementById('statusChart');
                if (ctxStatus) {
                    const statusLabels = Object.keys(metricsData);
                    const statusValues = Object.values(metricsData);
                    const statusColors = {
                        'present': '#10B981',
                        'late': '#EF4444', 
                        'sick': '#F59E0B',
                        'excused': '#3B82F6',
                        'alpha': '#6B7280'
                    };
                    
                    myStatusChart = new Chart(ctxStatus, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels.map(l => translateStatus(l)),
                            datasets: [{
                                data: statusValues,
                                backgroundColor: statusLabels.map(s => statusColors[s] || '#ccc'),
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { usePointStyle: true, boxWidth: 8, padding: 20 }
                                }
                            }
                        }
                    });
                }

                // Late Severity Chart
                const ctxLate = document.getElementById('lateChart');
                if (ctxLate) {
                    myLateChart = new Chart(ctxLate, {
                        type: 'pie',
                        data: {
                            labels: Object.keys(lateData).map(l => translateStatus(l)),
                            datasets: [{
                                data: Object.values(lateData),
                                backgroundColor: ['#FECACA', '#FCA5A5', '#EF4444', '#B91C1C'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { 
                                    position: 'right', 
                                    labels: { usePointStyle: true, boxWidth: 8 } 
                                },
                                title: { display: false, text: '{{ __("Late Severity") }}' }
                            }
                        }
                    });
                }

                // Absent Breakdown Chart
                const ctxAbsent = document.getElementById('absentChart');
                if (ctxAbsent) {
                    myAbsentChart = new Chart(ctxAbsent, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(absentData).map(l => translateStatus(l)),
                            datasets: [{
                                data: Object.values(absentData),
                                backgroundColor: ['#F59E0B', '#3B82F6', '#6B7280'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: { 
                                legend: { 
                                    position: 'right', 
                                    labels: { usePointStyle: true, boxWidth: 8 } 
                                } 
                            }
                        }
                    });
                }
            };

            // Initial render
            setTimeout(() => {
                initCharts(
                    @json($trend), 
                    @json($metrics), 
                    @json($divisionStats), 
                    @json($lateBuckets), 
                    @json($absentStats)
                );
            }, 100);

            // React to Livewire updates
            Livewire.on('chart-update', ({ trend, metrics, divisionStats, lateBuckets, absentStats }) => {
                initCharts(trend, metrics, divisionStats, lateBuckets, absentStats);
            });
        });
    </script>
    </div>
    @endpush
</div>
