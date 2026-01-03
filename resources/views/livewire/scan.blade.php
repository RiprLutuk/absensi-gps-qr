<div class="w-full to-slate-100 dark:from-slate-900 dark:to-slate-800">
    @php
        use Illuminate\Support\Carbon;
        $hasCheckedIn = !is_null($attendance?->time_in);
        $hasCheckedOut = !is_null($attendance?->time_out);
        $isComplete = $hasCheckedIn && $hasCheckedOut;
    @endphp

    @pushOnce('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endPushOnce

    @pushOnce('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpushOnce

    @if (!$isAbsence)
        <script src="{{ url('/assets/js/html5-qrcode.min.js') }}"></script>
    @endif

    <div>
        {{-- Header Section --}}
        <div class="mb-4 sm:mb-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 sm:p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ __('Attendance') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                    @if ($isComplete)
                        <div
                            class="completion-badge flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 rounded-full">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                            </svg>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">Complete</span>
                        </div>
                    @else
                        <div class="hidden md:flex items-center gap-2">
                            <span class="pulse-dot w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Live</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @include('components.alert-messages')

        @if ($isComplete)
            {{-- Completion View --}}
            <div class="space-y-4 sm:space-y-6">
                {{-- Success Message --}}
                <div class="rounded-lg border border-gray-200 bg-white p-4 sm:p-6 shadow dark:border-gray-700 dark:bg-gray-800 text-center">
                    <div
                        class="success-checkmark mb-4 inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-500 dark:to-green-700 rounded-full shadow-lg">
                        <svg class="w-10 h-10 text-green-700 dark:text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Attendance Complete!</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">You've successfully completed today's
                        attendance</p>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    @include('components.time-card', [
                        'icon' =>
                            'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                        'bgColor' => 'blue',
                        'label' => 'Check In',
                        'time' => Carbon::parse($attendance->time_in)->format('H:i'),
                        'status' => $attendance->status,
                    ])

                    @include('components.time-card', [
                        'icon' =>
                            'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                        'bgColor' => 'orange',
                        'label' => 'Check Out',
                        'time' => Carbon::parse($attendance->time_out)->format('H:i'),
                    ])
                </div>

                {{-- Location History Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                    {{-- Check In Location --}}
                    @include('components.location-card', [
                        'title' => 'Check In Location',
                        'mapId' => 'checkInMap',
                        'latitude' => $attendance?->latitude_in,
                        'longitude' => $attendance?->longitude_in,
                        'icon' =>
                            'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                        'iconColor' => 'blue',
                    ])

                    {{-- Check Out Location --}}
                    @include('components.location-card', [
                        'title' => 'Check Out Location',
                        'mapId' => 'checkOutMap',
                        'latitude' => $attendance?->latitude_out,
                        'longitude' => $attendance?->longitude_out,
                        'icon' =>
                            'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                        'iconColor' => 'orange',
                    ])
                </div>

                {{-- Action Buttons --}}
                @include('components.action-buttons')
            </div>
        @elseif ($hasCheckedIn && !$hasCheckedOut)
            {{-- Checked In View --}}
            <div class="space-y-4 sm:space-y-6">
                {{-- Status Banner --}}
                <div
                    class="rounded-lg border border-blue-200 bg-white p-4 sm:p-6 shadow dark:border-blue-800 dark:bg-gray-800">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300 rounded-xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">You're Checked In!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Scan QR to check out</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 sm:gap-6 lg:flex-row">
                    @if (!$isAbsence)
                        <div class="flex flex-col gap-4 sm:gap-6 lg:w-2/5">
                            @include('components.shift-selector')
                            @include('components.scanner-card', ['title' => 'Scan to Check Out'])
                        </div>
                    @endif

                    <div class="flex-1 space-y-4 sm:space-y-6">
                        {{-- Today's Status Card --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-4 sm:p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-300">Today's Check In</h4>
                                @include('components.status-badge', ['status' => $attendance->status])
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ Carbon::parse($attendance->time_in)->format('H:i:s') }}
                            </p>
                        </div>

                        {{-- Check In Location History --}}
                        @include('components.location-card', [
                            'title' => 'Check In Location',
                            'mapId' => 'checkInMap',
                            'latitude' => $attendance?->latitude_in,
                            'longitude' => $attendance?->longitude_in,
                            'icon' =>
                                'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                            'iconColor' => 'blue',
                        ])

                        {{-- Current Location for Check Out --}}
                        @include('components.location-card', [
                            'title' => 'Current Location',
                            'mapId' => 'currentLocationMap',
                            'latitude' => $currentLiveCoords[0] ?? null,
                            'longitude' => $currentLiveCoords[1] ?? null,
                            'showRefresh' => true,
                            'icon' =>
                                'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                            'iconColor' => 'green',
                        ])

                        @include('components.action-buttons')
                    </div>
                </div>
            </div>
        @else
            {{-- Initial State - Not Checked In --}}
            <div class="flex flex-col gap-4 sm:gap-6 lg:flex-row">
                @if (!$isAbsence)
                    <div class="flex flex-col gap-4 sm:gap-6 lg:w-2/5">
                        @include('components.shift-selector')
                        @include('components.scanner-card', ['title' => 'Scan QR Code'])
                    </div>
                @endif

                <div class="flex-1 space-y-4 sm:space-y-6">
                    @include('components.location-card', [
                        'title' => 'Current Location',
                        'mapId' => 'currentLocationMap',
                        'latitude' => $currentLiveCoords[0] ?? null,
                        'longitude' => $currentLiveCoords[1] ?? null,
                        'showRefresh' => true,
                        'icon' =>
                            'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                        'iconColor' => 'green',
                    ])

                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        @include('components.time-card', [
                            'icon' =>
                                'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                            'bgColor' => 'blue',
                            'label' => 'Check In',
                            'time' => '--:--:--',
                            'compact' => true,
                        ])

                        @include('components.time-card', [
                            'icon' =>
                                'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                            'bgColor' => 'amber',
                            'label' => 'Check Out',
                            'time' => '--:--:--',
                            'compact' => true,
                        ])
                    </div>

                    @include('components.action-buttons')
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const state = {
            errorMsg: document.querySelector('#scanner-error'),
            hasCheckedIn: {{ $hasCheckedIn ? 'true' : 'false' }},
            hasCheckedOut: {{ $hasCheckedOut ? 'true' : 'false' }},
            isComplete: {{ $isComplete ? 'true' : 'false' }},
            isAbsence: {{ $isAbsence ? 'true' : 'false' }},
            maps: {},
            userLat: null,
            userLng: null,
            userLat: null,
            userLng: null,
            isRefreshing: false,
            facingMode: 'environment'
        };

        // Toggle Map Function
        // Toggle Map Function
        window.toggleMap = function(mapId) {
            const mapEl = document.getElementById(mapId);
            const btn = document.getElementById(`toggle-${mapId}-btn`);
            const svg = btn.querySelector('svg');
            const span = btn.querySelector('span');

            if (mapEl.classList.contains('hidden')) {
                mapEl.classList.remove('hidden');
                svg.style.transform = 'rotate(180deg)';
                span.textContent = 'Hide Map';

                if (!state.maps[mapId]) {
                    initMap(mapId);
                }
                
                // Fix Leaflet rendering issues when showing hidden map
                setTimeout(() => {
                    if (state.maps[mapId]) {
                        state.maps[mapId].invalidateSize();
                    }
                }, 200);
            } else {
                mapEl.classList.add('hidden');
                svg.style.transform = 'rotate(0deg)';
                span.textContent = 'Show Map';
            }
        };

        // Initialize Map
        function initMap(mapId) {
            let lat, lng, popupText, markerColor;

            if (mapId === 'checkInMap') {
                lat = {{ $attendance?->latitude_in ?? 0 }};
                lng = {{ $attendance?->longitude_in ?? 0 }};
                popupText = 'Check In Location';
                markerColor = 'blue';
            } else if (mapId === 'checkOutMap') {
                lat = {{ $attendance?->latitude_out ?? 0 }};
                lng = {{ $attendance?->longitude_out ?? 0 }};
                popupText = 'Check Out Location';
                markerColor = 'orange';
            } else {
                lat = state.userLat;
                lng = state.userLng;
                popupText = 'Your Current Location';
                markerColor = 'green';
            }

            if (lat && lng) {
                state.maps[mapId] = L.map(mapId).setView([lat, lng], 18);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 21,
                }).addTo(state.maps[mapId]);

                const marker = L.marker([lat, lng]).addTo(state.maps[mapId]);
                marker.bindPopup(popupText).openPopup();
            }
        }

        // Update Location Display
        function updateLocationDisplay(lat, lng, mapId = 'currentLocationMap') {
            const locationText = document.getElementById(`location-text-${mapId}`);
            const updatedText = document.getElementById(`location-updated-${mapId}`);
            const timeStr = new Date().toLocaleTimeString();

            if (locationText) {
                locationText.innerHTML = `
                    <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank"
                       class="inline-flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        ${lat}, ${lng}
                    </a>
                `;
            }
            
            if (updatedText) {
                updatedText.textContent = `Last updated: ${timeStr}`;
            }
        }

        // Refresh Location Function
        window.refreshLocation = function() {
            if (state.isRefreshing) return;

            state.isRefreshing = true;
            const btn = document.getElementById('refresh-location-btn');
            const svg = btn.querySelector('svg');

            svg.style.animation = 'spin 1s linear infinite';

            getLocation(true).finally(() => {
                state.isRefreshing = false;
                svg.style.animation = '';
            });
        };

        async function getLocation(isRefresh = false) {
            try {
                let lat, lng;

                if (window.Capacitor?.isNativePlatform?.()) {
                    const perm = await Capacitor.Plugins.Geolocation.requestPermissions();

                    if (perm.location !== 'granted') {
                        throw new Error('Location permission denied');
                    }

                    const position = await Capacitor.Plugins.Geolocation.getCurrentPosition({
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });

                    lat = position.coords.latitude.toFixed(6);
                    lng = position.coords.longitude.toFixed(6);
                } else {
                    if (!navigator.geolocation) {
                        throw new Error('Geolocation not supported');
                    }

                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(
                            resolve,
                            reject, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    });

                    lat = position.coords.latitude.toFixed(6);
                    lng = position.coords.longitude.toFixed(6);
                }

                state.userLat = parseFloat(lat);
                state.userLng = parseFloat(lng);

                if (window.Livewire) {
                    window.Livewire.find('{{ $_instance->getId() }}')
                        .set('currentLiveCoords', [state.userLat, state.userLng]);
                }

                updateLocationDisplay(lat, lng);

                if (state.maps['currentLocationMap'] && isRefresh) {
                    state.maps['currentLocationMap'].setView(
                        [state.userLat, state.userLng],
                        18
                    );

                    state.maps['currentLocationMap'].eachLayer(layer => {
                        if (layer instanceof L.Marker) {
                            state.maps['currentLocationMap'].removeLayer(layer);
                        }
                    });

                    const timeStr = new Date().toLocaleTimeString();
                    L.marker([state.userLat, state.userLng])
                        .addTo(state.maps['currentLocationMap'])
                        .bindPopup(`Your Current Location<br><span class="text-xs text-gray-500">Updated: ${timeStr}</span>`)
                        .openPopup();
                }

                return true;

            } catch (err) {
                console.error(err);

                const locationText = document.getElementById('location-text-currentLocationMap');
                if (locationText) {
                    locationText.innerHTML =
                        '<span class="text-red-600 dark:text-red-400">Location access denied</span>';
                }

                if (state.errorMsg) {
                    state.errorMsg.classList.remove('hidden');
                    state.errorMsg.innerHTML = 'Please enable location access';
                }

                throw err;
            }
        }

        // Initialize Scanner
        function initScanner() {
            if (state.isAbsence || state.isComplete) return;

            const scannerEl = document.getElementById('scanner');
            if (!scannerEl || typeof Html5Qrcode === 'undefined') return;

            const scanner = new Html5Qrcode('scanner');
            const config = {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                fps: 30,
                aspectRatio: 1.0,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    let minEdgePercentage = 0.7; // 70%
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                    return {
                        width: qrboxSize,
                        height: qrboxSize
                    };
                },
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            };
            
            // Expose switchCamera globally
            window.switchCamera = async function() {
                if (scanner.getState() === Html5QrcodeScannerState.SCANNING) {
                    await scanner.stop();
                    state.facingMode = state.facingMode === 'environment' ? 'user' : 'environment';
                    await startScanning();
                }
            };

            async function startScanning() {
                try {
                    if (window.isNativeApp()) {
                        await window.startNativeBarcodeScanner(onScanSuccess);
                        return;
                    }

                    // LOGIC LAMA (WEB)
                    if (scanner.getState() === Html5QrcodeScannerState.PAUSED) {
                        return scanner.resume();
                    }

                    await scanner.start({
                            facingMode: state.facingMode
                        },
                        config,
                        onScanSuccess
                    );
                } catch (err) {
                    console.error('Scanner start error:', err);
                }
            }



            async function onScanSuccess(decodedText) {
                if (scanner.getState() === Html5QrcodeScannerState.SCANNING) {
                    scanner.pause(true);
                }

                if (state.hasCheckedIn && !state.hasCheckedOut) {
                    const result = await window.Livewire.find('{{ $_instance->getId() }}').call('scan',
                        decodedText);
                    handleScanResult(result, scanner, startScanning);
                    return;
                }

                if (!(await checkTime())) {
                    await startScanning();
                    return;
                }

                const result = await window.Livewire.find('{{ $_instance->getId() }}').call('scan',
                    decodedText);
                handleScanResult(result, scanner, startScanning);
            }

            function handleScanResult(result, scanner, startScanning) {
                if (result === true) {
                    scanner.stop();
                    if (state.errorMsg) {
                        state.errorMsg.classList.add('hidden');
                        state.errorMsg.innerHTML = '';
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance recorded successfully',
                        timer: 2000,
                        showConfirmButton: false,
                        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#1f2937'
                    }).then(() => {
                        window.location.reload();
                    });
                } else if (typeof result === 'string') {
                    if (state.errorMsg) {
                        state.errorMsg.classList.remove('hidden');
                        state.errorMsg.innerHTML = result;
                    }
                    setTimeout(startScanning, 500);
                }
            }

            async function checkTime() {
                const attendance = await window.Livewire.find('{{ $_instance->getId() }}').call(
                    'getAttendance');
                
                if (attendance?.time_in) {
                    // Check 1: Minimum attendance duration (1 minute safety to prevent accidental double taps)
                    const timeIn = new Date(attendance.time_in).valueOf();
                    const diff = (Date.now() - timeIn) / (1000 * 60); // minutes
                    
                    if (diff < 1) {
                         Swal.fire({
                             icon: 'warning',
                             title: 'Too Fast!',
                             text: 'You just checked in. Please wait a moment before checking out.',
                             confirmButtonColor: '#3085d6',
                         });
                         return false;
                    }

                    // Check 2: Early Checkout Warning
                    if (attendance.shift_end_time) {
                        const now = new Date();
                        const shiftEnd = new Date();
                        const [hours, minutes, seconds] = attendance.shift_end_time.split(':');
                        shiftEnd.setHours(hours, minutes, seconds);

                        // If checkout is more than 5 minutes early
                        if (now < shiftEnd && (shiftEnd - now) > (5 * 60 * 1000)) {
                            const result = await Swal.fire({
                                icon: 'warning',
                                title: 'Early Checkout',
                                html: `Your shift ends at <b>${attendance.shift_end_time}</b>.<br>Are you sure you want to checkout now?`,
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, checkout',
                                cancelButtonText: 'Cancel'
                            });
                            
                            return result.isConfirmed;
                        }
                    }
                }
                return true;
            }

            // Style scanner buttons
            const observer = new MutationObserver(() => {
                const baseClasses = ['px-4', 'py-2', 'rounded-xl', 'font-medium', 'transition',
                    'duration-200'
                ];
                const buttons = {
                    '#html5-qrcode-button-camera-start': [...baseClasses, 'bg-blue-600',
                        'hover:bg-blue-700', 'text-white'
                    ],
                    '#html5-qrcode-button-camera-stop': [...baseClasses, 'bg-red-600',
                        'hover:bg-red-700', 'text-white'
                    ],
                    '#html5-qrcode-button-file-selection': [...baseClasses, 'bg-blue-600',
                        'hover:bg-blue-700', 'text-white'
                    ],
                    '#html5-qrcode-button-camera-permission': [...baseClasses, 'bg-blue-600',
                        'hover:bg-blue-700', 'text-white'
                    ]
                };

                Object.entries(buttons).forEach(([selector, classes]) => {
                    const btn = document.querySelector(selector);
                    if (btn) btn.classList.add(...classes);
                });
            });

            observer.observe(scannerEl, {
                childList: true,
                subtree: true
            });

            // Handle shift selector
            if (!state.hasCheckedIn) {
                const shift = document.querySelector('#shift');
                if (shift) {
                    const msg = 'Please select a shift first';
                    let isRendered = false;

                    setTimeout(() => {
                        if (!shift.value) {
                            if (state.errorMsg) {
                                state.errorMsg.classList.remove('hidden');
                                state.errorMsg.innerHTML = msg;
                            }
                        } else {
                            startScanning();
                            isRendered = true;
                        }
                    }, 1000);

                    shift.addEventListener('change', () => {
                        if (!isRendered && shift.value) {
                            startScanning();
                            isRendered = true;
                        }

                        if (!shift.value) {
                            scanner.pause(true);
                            if (state.errorMsg) {
                                state.errorMsg.classList.remove('hidden');
                                state.errorMsg.innerHTML = msg;
                            }
                        } else if (scanner.getState() === Html5QrcodeScannerState.PAUSED) {
                            scanner.resume();
                            if (state.errorMsg) {
                                state.errorMsg.classList.add('hidden');
                                state.errorMsg.innerHTML = '';
                            }
                        }
                    });
                }
            } else {
                setTimeout(startScanning, 1000);
            }
        }

        async function ensureLocationPermission() {

            if (window.Capacitor?.isNativePlatform?.()) {
                const {
                    Geolocation
                } = window.Capacitor.Plugins;

                const status = await Geolocation.checkPermissions();

                if (status.location === 'granted') return true;

                const req = await Geolocation.requestPermissions();
                return req.location === 'granted';
            }

            if (!navigator.geolocation) return false;

            if (navigator.permissions) {
                const perm = await navigator.permissions.query({
                    name: 'geolocation'
                });
                return perm.state === 'granted' || perm.state === 'prompt';
            }

            return true;
        }

        (async () => {
            const allowed = await ensureLocationPermission();

            if (allowed) {
                await getLocation();
            } else if (state.errorMsg) {
                state.errorMsg.classList.remove('hidden');
                state.errorMsg.innerHTML = 'Please enable location permission';
            }

            initScanner();
        })();

    });
</script>
<script>
    window.startNativeBarcodeScanner = async function(onSuccess) {
        try {
            if (!window.Capacitor?.Plugins?.BarcodeScanner) {
                throw new Error('BarcodeScanner plugin not available');
            }

            const {
                BarcodeScanner
            } = window.Capacitor.Plugins;

            // Permission
            const perm = await BarcodeScanner.checkPermission({
                force: true
            });
            if (!perm.granted) {
                throw new Error('Camera permission denied');
            }

            // Hide WebView background
            await BarcodeScanner.hideBackground();
            document.body.classList.add('scanner-active');

            // Start scan (FULLSCREEN)
            const result = await BarcodeScanner.startScan({
                targetedFormats: ['QR_CODE'],
                cameraDirection: 'rear'
            });

            // Restore UI
            await BarcodeScanner.showBackground();
            await BarcodeScanner.stopScan();
            document.body.classList.remove('scanner-active');

            if (result?.hasContent) {
                onSuccess(result.content);
            }

        } catch (err) {
            console.error('[Native Scanner Error]', err);

            await window.Capacitor.Plugins.BarcodeScanner?.showBackground();
            await window.Capacitor.Plugins.BarcodeScanner?.stopScan();
            document.body.classList.remove('scanner-active');

            const errorEl = document.getElementById('scanner-error');
            if (errorEl) {
                errorEl.classList.remove('hidden');
                errorEl.innerHTML = err.message || 'Failed to open camera';
            }
        }
    };
</script>
