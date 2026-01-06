<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header & Filters -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 sm:mb-0">
                📊 Analytics Dashboard
            </h2>
            <div class="flex gap-2">
                <div class="w-40">
                    <x-tom-select wire:model.live="month" placeholder="Select Month" class="w-full">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endforeach
                    </x-tom-select>
                </div>
                <div class="w-28">
                     <x-tom-select wire:model.live="year" placeholder="Select Year" class="w-full">
                        @foreach(range(date('Y')-1, date('Y')) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </x-tom-select>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Line Chart (Trend) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Attendance Trend</h3>
                <div class="relative h-64 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart (Status Distribution) -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Status Distribution</h3>
                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Leaderboards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Top Diligent -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-green-600 dark:text-green-400">🏆 Fajar Siddiq Award (Top Diligent)</h3>
                    <span class="text-xs text-gray-500">Earliest avg check-in</span>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($topDiligent as $employee)
                            <li class="py-3 sm:py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img class="w-8 h-8 rounded-full" src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                            {{ $employee->name }}
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                        {{ gmdate('H:i:s', $employee->avg_check_in) }}
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">No data yet.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Top Late -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-red-600 dark:text-red-400">🐢 Tukang Telat (Most Late)</h3>
                    <span class="text-xs text-gray-500">Highest late count</span>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($topLate as $employee)
                            <li class="py-3 sm:py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img class="w-8 h-8 rounded-full" src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                            {{ $employee->name }}
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center text-base font-bold text-red-600 dark:text-red-400">
                                        {{ $employee->late_count }}x
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Everyone is diligent! 🎉</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Top Early Leavers -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-amber-500 dark:text-amber-400">🏃‍♂️ Tukang Bolos (Early Leaver)</h3>
                    <span class="text-xs text-gray-500">Checkout before time</span>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($topEarlyLeavers as $employee)
                            <li class="py-3 sm:py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img class="w-8 h-8 rounded-full" src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                            {{ $employee->name }}
                                        </p>
                                    </div>
                                    <div class="inline-flex items-center text-base font-bold text-amber-500 dark:text-amber-400">
                                        {{ $employee->early_leave_count }}x
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Everyone stays until the end! 👏</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
             let myTrendChart = null;
             let myStatusChart = null;

             const initCharts = (trendData, metricsData) => {
                // Destroy existing charts if needed
                if (myTrendChart) myTrendChart.destroy();
                if (myStatusChart) myStatusChart.destroy();

                // Trend Chart
                const ctxTrend = document.getElementById('trendChart');
                if (ctxTrend) {
                    myTrendChart = new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: trendData.labels,
                            datasets: [
                                {
                                    label: 'Present',
                                    data: trendData.present,
                                    borderColor: '#10B981', // green-500
                                    tension: 0.3
                                },
                                {
                                    label: 'Late',
                                    data: trendData.late,
                                    borderColor: '#EF4444', // red-500
                                    tension: 0.3
                                },
                                {
                                    label: 'Absent/Sick',
                                    data: trendData.absent,
                                    borderColor: '#F59E0B', // amber-500
                                    tension: 0.3
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
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                }

                // Status Chart
                const ctxStatus = document.getElementById('statusChart');
                if (ctxStatus) {
                    // Prepare pie data
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
                            labels: statusLabels,
                            datasets: [{
                                data: statusValues,
                                backgroundColor: statusLabels.map(s => statusColors[s] || '#ccc'),
                            }]
                        },
                        options: {
                            responsive: true,
                             maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                }
                            }
                        }
                    });
                }
            };

            // Initial render
            initCharts(@json($trend), @json($metrics));

            // React to Livewire updates
            Livewire.on('chart-update', ({ trend, metrics }) => {
                initCharts(trend, metrics);
            });
        });
    </script>
    @endpush
</div>
