<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Check Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="min-h-screen p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-4xl font-bold text-white mb-2">Health Check Dashboard</h1>
                <p class="text-gray-400">Real-time system status monitoring</p>
            </div>

            <!-- Main Status Card -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Status -->
                <div class="bg-gradient-to-br from-green-900 to-green-800 rounded-lg p-6 border border-green-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-200 text-sm font-semibold mb-2">Status</p>
                            <p class="text-3xl font-bold text-green-100" id="status">Loading...</p>
                        </div>
                        <div class="text-4xl">
                            <span id="statusIcon" class="animate-pulse">⏳</span>
                        </div>
                    </div>
                </div>

                <!-- Database Connection -->
                <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-lg p-6 border border-blue-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-200 text-sm font-semibold mb-2">Database</p>
                            <p class="text-3xl font-bold text-blue-100" id="database">Loading...</p>
                        </div>
                        <div class="text-4xl">
                            <span id="dbIcon" class="animate-pulse">⏳</span>
                        </div>
                    </div>
                </div>

                <!-- Total Records -->
                <div class="bg-gradient-to-br from-purple-900 to-purple-800 rounded-lg p-6 border border-purple-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-200 text-sm font-semibold mb-2">Total Records</p>
                            <p class="text-3xl font-bold text-purple-100" id="totalRecords">Loading...</p>
                        </div>
                        <div class="text-4xl">📊</div>
                    </div>
                </div>

                <!-- Timestamp -->
                <div class="bg-gradient-to-br from-orange-900 to-orange-800 rounded-lg p-6 border border-orange-700 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-200 text-sm font-semibold mb-2">Last Update</p>
                            <p class="text-sm font-bold text-orange-100" id="timestamp">Loading...</p>
                        </div>
                        <div class="text-4xl">🕐</div>
                    </div>
                </div>
            </div>

            <!-- Detailed Status Section -->
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-white mb-6">Detailed Information</h2>
                
                <div class="space-y-4">
                    <!-- Response JSON -->
                    <div>
                        <p class="text-gray-300 font-semibold mb-2">Raw Response:</p>
                        <pre class="bg-gray-900 p-4 rounded border border-gray-700 overflow-auto text-sm text-gray-200" id="jsonResponse">Loading...</pre>
                    </div>

                    <!-- Auto-refresh Info -->
                    <div class="bg-gray-700 p-4 rounded">
                        <p class="text-gray-300">
                            <span class="font-semibold">Auto-refresh:</span> Every <span id="refreshInterval" class="text-blue-400 font-bold">5</span> seconds
                            <button onclick="toggleAutoRefresh()" class="ml-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm font-semibold transition">
                                <span id="toggleText">Pause</span>
                            </button>
                            <button onclick="refreshHealth()" class="ml-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded text-white text-sm font-semibold transition">
                                Refresh Now
                            </button>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistics Chart -->
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 shadow-lg">
                <h2 class="text-2xl font-bold text-white mb-6">Status History</h2>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        let autoRefreshEnabled = true;
        let refreshIntervalId = null;
        let statusHistory = [];
        let chart = null;

        // Initialize chart
        function initChart() {
            const ctx = document.getElementById('statusChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total Records Over Time',
                        data: [],
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#d1d5db'
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                color: '#d1d5db'
                            },
                            grid: {
                                color: 'rgba(107, 114, 128, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#d1d5db'
                            },
                            grid: {
                                color: 'rgba(107, 114, 128, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Fetch health status
        async function refreshHealth() {
            try {
                const response = await fetch('/health');
                const data = await response.json();

                // Update status
                document.getElementById('status').textContent = data.status.toUpperCase();
                document.getElementById('statusIcon').textContent = data.status === 'ready' ? '✅' : '⚠️';

                // Update database
                document.getElementById('database').textContent = data.database.toUpperCase();
                document.getElementById('dbIcon').textContent = data.database === 'connected' ? '✅' : '❌';

                // Update total records with formatting
                const formattedRecords = new Intl.NumberFormat().format(data.total_records);
                document.getElementById('totalRecords').textContent = formattedRecords;

                // Update timestamp
                const date = new Date(data.timestamp);
                document.getElementById('timestamp').textContent = date.toLocaleString();

                // Update JSON response
                document.getElementById('jsonResponse').textContent = JSON.stringify(data, null, 2);

                // Add to history for chart
                statusHistory.push({
                    time: new Date(data.timestamp).toLocaleTimeString(),
                    records: data.total_records
                });

                // Keep only last 20 entries
                if (statusHistory.length > 20) {
                    statusHistory.shift();
                }

                // Update chart
                updateChart();

            } catch (error) {
                console.error('Error fetching health status:', error);
                document.getElementById('status').textContent = 'ERROR';
                document.getElementById('statusIcon').textContent = '❌';
                document.getElementById('database').textContent = 'UNKNOWN';
                document.getElementById('dbIcon').textContent = '❓';
                document.getElementById('jsonResponse').textContent = `Error: ${error.message}`;
            }
        }

        // Update chart with history
        function updateChart() {
            if (chart) {
                chart.data.labels = statusHistory.map(item => item.time);
                chart.data.datasets[0].data = statusHistory.map(item => item.records);
                chart.update();
            }
        }

        // Toggle auto-refresh
        function toggleAutoRefresh() {
            autoRefreshEnabled = !autoRefreshEnabled;
            document.getElementById('toggleText').textContent = autoRefreshEnabled ? 'Pause' : 'Resume';
            
            if (autoRefreshEnabled) {
                startAutoRefresh();
            } else {
                clearInterval(refreshIntervalId);
            }
        }

        // Start auto-refresh
        function startAutoRefresh() {
            clearInterval(refreshIntervalId);
            refreshIntervalId = setInterval(() => {
                if (autoRefreshEnabled) {
                    refreshHealth();
                }
            }, 5000); // 5 seconds
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initChart();
            refreshHealth();
            startAutoRefresh();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            clearInterval(refreshIntervalId);
        });
    </script>
</body>
</html>
