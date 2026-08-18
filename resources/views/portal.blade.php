<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data Console</title>
    <meta name="description" content="Search, monitor data quality, detect duplicates, and check the health of the ws_user dataset — in one page.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ===== Scoped styles for the Quality tab (from quality-dashboard.blade.php) ===== */
        .panel-quality {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 1rem;
        }
        .panel-quality #qualityApp { max-width: 1400px; margin: 0 auto; }
        .panel-quality .header { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .panel-quality .header h1 { color: #333; margin-bottom: 10px; font-size: 28px; }
        .panel-quality .header .subtitle { color: #666; font-size: 14px; }
        .panel-quality .loading-container, .panel-quality .error-container, .panel-quality .empty-container {
            background: white; padding: 60px 30px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .panel-quality .loading-spinner {
            display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea; border-radius: 50%; animation: spin-quality 1s linear infinite;
        }
        @keyframes spin-quality { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .panel-quality .error-message { color: #d32f2f; font-weight: 600; margin-top: 15px; }
        .panel-quality .empty-message { color: #999; font-weight: 500; }
        .panel-quality .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .panel-quality .metric-card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 4px solid #667eea; }
        .panel-quality .metric-card.email { border-left-color: #ff9800; }
        .panel-quality .metric-card.phone { border-left-color: #f44336; }
        .panel-quality .metric-card.birth_date { border-left-color: #2196f3; }
        .panel-quality .metric-card.hobbies { border-left-color: #4caf50; }
        .panel-quality .metric-card.status { border-left-color: #9c27b0; }
        .panel-quality .metric-card h3 { font-size: 18px; color: #333; margin-bottom: 16px; text-transform: capitalize; }
        .panel-quality .metric-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eee; }
        .panel-quality .metric-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .panel-quality .metric-label { color: #666; font-size: 13px; font-weight: 500; }
        .panel-quality .metric-value { color: #333; font-size: 14px; font-weight: 600; }
        .panel-quality .metric-value.number { color: #667eea; }
        .panel-quality .metric-value.percent { color: #ff9800; }
        .panel-quality .status-distribution { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin-top: 12px; }
        .panel-quality .status-item { background: #f5f5f5; padding: 10px; border-radius: 6px; text-align: center; }
        .panel-quality .status-item .label { font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 6px; }
        .panel-quality .status-item .count { font-size: 16px; font-weight: bold; color: #333; }
        .panel-quality .issues-section { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .panel-quality .issues-section h2 { font-size: 20px; color: #333; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #eee; }
        .panel-quality .issues-table { width: 100%; border-collapse: collapse; }
        .panel-quality .issues-table thead { background: #f5f5f5; }
        .panel-quality .issues-table th { padding: 12px; text-align: left; font-weight: 600; color: #666; font-size: 13px; text-transform: uppercase; }
        .panel-quality .issues-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .panel-quality .issues-table tbody tr:hover { background: #f9f9f9; }
        .panel-quality .severity-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .panel-quality .severity-badge.low { background: #e8f5e9; color: #2e7d32; }
        .panel-quality .severity-badge.medium { background: #fff3e0; color: #e65100; }
        .panel-quality .severity-badge.high { background: #ffebee; color: #c62828; }
        .panel-quality .examples-list { max-width: 100%; overflow-x: auto; }
        .panel-quality .examples-list span { display: inline-block; background: #f0f0f0; padding: 4px 8px; border-radius: 3px; margin-right: 8px; margin-bottom: 4px; font-family: 'Monaco', 'Courier New', monospace; font-size: 11px; color: #555; }
        .panel-quality .no-issues { text-align: center; padding: 40px 20px; color: #999; }
        .panel-quality .footer { background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .panel-quality .footer-info { font-size: 13px; color: #666; }
        .panel-quality .refresh-button { padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: background 0.3s ease; }
        .panel-quality .refresh-button:hover { background: #5568d3; }
        .panel-quality .refresh-button:disabled { background: #ccc; cursor: not-allowed; }
        @media (max-width: 768px) {
            .panel-quality .metrics-grid { grid-template-columns: 1fr; }
            .panel-quality .issues-table { font-size: 12px; }
            .panel-quality .issues-table th, .panel-quality .issues-table td { padding: 8px; }
            .panel-quality .footer { flex-direction: column; align-items: flex-start; }
        }

        /* ===== Scoped styles for the Duplicates tab (from duplicates.blade.php) ===== */
        .panel-duplicates {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 1rem;
        }
        .panel-duplicates .container { max-width: 1200px; margin: 0 auto; }
        .panel-duplicates .header { text-align: center; color: white; margin-bottom: 40px; animation: slideDown-dup 0.6s ease; }
        .panel-duplicates .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .panel-duplicates .header p { font-size: 1.1em; opacity: 0.9; }
        @keyframes slideDown-dup { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .panel-duplicates .main-card { background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; animation: fadeIn-dup 0.6s ease; }
        @keyframes fadeIn-dup { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .panel-duplicates .content { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; padding: 40px; }
        @media (max-width: 768px) { .panel-duplicates .content { grid-template-columns: 1fr; } }
        .panel-duplicates .form-section { display: flex; flex-direction: column; }
        .panel-duplicates .form-section h2 { font-size: 1.5em; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .panel-duplicates .form-group { margin-bottom: 20px; }
        .panel-duplicates .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
        .panel-duplicates .form-group select, .panel-duplicates .form-group input {
            width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em; transition: border-color 0.3s; font-family: inherit;
        }
        .panel-duplicates .form-group select:focus, .panel-duplicates .form-group input:focus {
            outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .panel-duplicates .button-group { display: flex; gap: 10px; margin-top: 30px; }
        .panel-duplicates button { flex: 1; padding: 12px 20px; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .panel-duplicates .btn-search { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .panel-duplicates .btn-search:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .panel-duplicates .btn-search:active { transform: translateY(0); }
        .panel-duplicates .btn-clear { background: #f5f5f5; color: #666; }
        .panel-duplicates .btn-clear:hover { background: #efefef; }
        .panel-duplicates .results-section { display: flex; flex-direction: column; }
        .panel-duplicates .results-section h2 { font-size: 1.5em; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .panel-duplicates .loading { text-align: center; padding: 40px; color: #667eea; }
        .panel-duplicates .spinner {
            border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; width: 40px; height: 40px;
            animation: spin-dup 1s linear infinite; margin: 0 auto 20px;
        }
        @keyframes spin-dup { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .panel-duplicates .error-message { background: #fee; border: 2px solid #fcc; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .panel-duplicates .stats { background: #f9f9f9; border-left: 4px solid #667eea; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .panel-duplicates .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 10px; }
        .panel-duplicates .stat-item { text-align: center; }
        .panel-duplicates .stat-value { font-size: 1.5em; font-weight: bold; color: #667eea; }
        .panel-duplicates .stat-label { font-size: 0.9em; color: #999; margin-top: 5px; }
        .panel-duplicates .groups-container { max-height: 500px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 8px; }
        .panel-duplicates .group-item { padding: 15px; border-bottom: 1px solid #e0e0e0; cursor: pointer; transition: background 0.2s; }
        .panel-duplicates .group-item:hover { background: #f9f9f9; }
        .panel-duplicates .group-item:last-child { border-bottom: none; }
        .panel-duplicates .group-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .panel-duplicates .group-attribute { font-weight: 600; color: #333; word-break: break-all; }
        .panel-duplicates .group-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 600; }
        .panel-duplicates .badge-high { background: #d4edda; color: #155724; }
        .panel-duplicates .badge-medium { background: #fff3cd; color: #856404; }
        .panel-duplicates .badge-low { background: #f8d7da; color: #721c24; }
        .panel-duplicates .group-details { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; font-size: 0.9em; }
        .panel-duplicates .detail-item { display: flex; justify-content: space-between; }
        .panel-duplicates .detail-label { color: #999; font-weight: 500; }
        .panel-duplicates .detail-value { color: #333; font-weight: 600; }
        .panel-duplicates .user-ids { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }
        .panel-duplicates .user-id-badge { background: #667eea; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; }
        .panel-duplicates .empty-state { text-align: center; padding: 40px; color: #999; }
        .panel-duplicates .empty-state-icon { font-size: 3em; margin-bottom: 15px; }
        .panel-duplicates .footer { text-align: center; color: white; margin-top: 40px; font-size: 0.9em; opacity: 0.8; }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Tab bar (no navigation — just shows/hides panels below) -->
    <div class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-2 flex overflow-x-auto">
            <button type="button" data-tab="search" class="tab-btn shrink-0 px-5 py-4 text-sm font-semibold border-b-2 whitespace-nowrap transition text-[#667eea] border-[#667eea]">🔎 Search</button>
            <button type="button" data-tab="quality" class="tab-btn shrink-0 px-5 py-4 text-sm font-semibold border-b-2 whitespace-nowrap transition text-gray-500 border-transparent hover:text-gray-700">📊 Quality</button>
            <button type="button" data-tab="duplicates" class="tab-btn shrink-0 px-5 py-4 text-sm font-semibold border-b-2 whitespace-nowrap transition text-gray-500 border-transparent hover:text-gray-700">🔗 Duplicates</button>
            <button type="button" data-tab="health" class="tab-btn shrink-0 px-5 py-4 text-sm font-semibold border-b-2 whitespace-nowrap transition text-gray-500 border-transparent hover:text-gray-700">💓 Health</button>
        </div>
    </div>

    <!-- ============================= SEARCH TAB ============================= -->
    <div id="tab-search">
        <div id="searchApp" class="min-h-screen bg-gray-50">
            <header class="bg-white shadow">
                <div class="max-w-6xl mx-auto px-4 py-6">
                    <h1 class="text-3xl font-bold text-gray-900">User Search</h1>
                    <p class="text-gray-600 mt-1">Search across users</p>
                </div>
            </header>

            <main class="max-w-6xl mx-auto px-4 py-8">
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Query</label>
                            <input
                                v-model="search.query"
                                @keyup.enter="performSearch"
                                type="text"
                                placeholder="Enter email, phone, user ID, or name..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="loading"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Type</label>
                            <select
                                v-model="search.type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="loading"
                            >
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="user_id">User ID</option>
                                <option value="name">Name</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Limit</label>
                            <input
                                v-model.number="search.limit"
                                type="number"
                                min="1"
                                max="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="loading"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Offset</label>
                            <input
                                v-model.number="search.offset"
                                type="number"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="loading"
                            >
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="performSearch"
                                :disabled="loading || !search.query"
                                class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition"
                            >
                                @{{ loading ? 'Searching...' : 'Search' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        @{{ error }}
                    </div>
                </div>

                <div v-if="results.length > 0 || searched" class="bg-white rounded-lg shadow p-6">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                Results
                                <span v-if="results.length > 0" class="text-gray-600 font-normal text-lg">
                                    (@{{ results.length }} of @{{ pagination.total }} total)
                                </span>
                            </h2>
                            <p v-if="results.length > 0" class="text-sm text-gray-600 mt-1">
                                Found in @{{ lastResponse.took_ms }}ms
                            </p>
                        </div>
                    </div>

                    <div v-if="results.length === 0" class="text-center py-12">
                        <h3 class="text-sm font-medium text-gray-900">No results</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            @{{ searched ? 'No users found matching your search.' : 'Enter a search query to get started.' }}
                        </p>
                    </div>

                    <div v-if="results.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in results" :key="user.user_id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">@{{ user.user_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">@{{ user.full_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ user.user_email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ user.msisdn || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="getStatusClass(user.status)" class="px-2 py-1 rounded text-xs font-medium">
                                            @{{ getStatusLabel(user.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @{{ formatDate(user.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="results.length > 0" class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Showing @{{ pagination.offset + 1 }} to @{{ Math.min(pagination.offset + pagination.limit, pagination.total) }} of @{{ pagination.total }}
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="previousPage"
                                :disabled="pagination.offset === 0 || loading"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <button
                                @click="nextPage"
                                :disabled="pagination.offset + pagination.limit >= pagination.total || loading"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ============================= QUALITY TAB ============================= -->
    <div id="tab-quality" hidden>
        <div class="panel-quality">
            <div id="qualityApp">
                <div class="header">
                    <h1>📊 Data Quality Dashboard</h1>
                    <p class="subtitle">Real-time analysis of ws_user table data quality metrics</p>
                </div>

                <div v-if="loading" class="loading-container">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 20px; color: #999;">Analyzing data quality...</p>
                </div>

                <div v-else-if="error" class="error-container">
                    <p style="font-size: 48px; margin-bottom: 15px;">⚠️</p>
                    <p class="error-message" v-text="error"></p>
                    <button @click="fetchQualityData" class="refresh-button" style="margin-top: 20px;">
                        Retry
                    </button>
                </div>

                <div v-else-if="!data" class="empty-container">
                    <p class="empty-message">No data available</p>
                </div>

                <div v-else>
                    <div class="metrics-grid">
                        <div class="metric-card email">
                            <h3>📧 Email</h3>
                            <div class="metric-item">
                                <span class="metric-label">Total Records</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.email.total)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Present</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.email.present)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Missing</span>
                                <span class="metric-value">
                                    <span v-text="formatNumber(data.quality_metrics.email.missing_count)"></span>
                                    <span class="metric-value percent" v-text="'(' + data.quality_metrics.email.missing_percent + '%)'"></span>
                                </span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Unique</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.email.unique)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Duplicates</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.email.duplicate_count)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Invalid Format</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.email.invalid_format)"></span>
                            </div>
                        </div>

                        <div class="metric-card phone">
                            <h3>📱 Phone</h3>
                            <div class="metric-item">
                                <span class="metric-label">Total Records</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.phone.total)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Present</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.phone.present)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Missing</span>
                                <span class="metric-value">
                                    <span v-text="formatNumber(data.quality_metrics.phone.missing_count)"></span>
                                    <span class="metric-value percent" v-text="'(' + data.quality_metrics.phone.missing_percent + '%)'"></span>
                                </span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Unique</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.phone.unique)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Duplicates</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.phone.duplicate_count)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Malformed</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.phone.malformed)"></span>
                            </div>
                        </div>

                        <div class="metric-card birth_date">
                            <h3>🎂 Birth Date</h3>
                            <div class="metric-item">
                                <span class="metric-label">Total Records</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.birth_date.total)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Present</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.birth_date.present)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Missing</span>
                                <span class="metric-value">
                                    <span v-text="formatNumber(data.quality_metrics.birth_date.missing_count)"></span>
                                    <span class="metric-value percent" v-text="'(' + data.quality_metrics.birth_date.missing_percent + '%)'"></span>
                                </span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Impossible Dates</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.birth_date.impossible_dates)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Future Dates</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.birth_date.future_dates)"></span>
                            </div>
                        </div>

                        <div class="metric-card hobbies">
                            <h3>🎯 Hobbies</h3>
                            <div class="metric-item">
                                <span class="metric-label">Total Records</span>
                                <span class="metric-value number" v-text="formatNumber(data.quality_metrics.hobbies.total)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">NULL Count</span>
                                <span class="metric-value">
                                    <span v-text="formatNumber(data.quality_metrics.hobbies.null_count)"></span>
                                    <span class="metric-value percent" v-text="'(' + data.quality_metrics.hobbies.null_percent + '%)'"></span>
                                </span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Special Characters</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.hobbies.with_special_chars)"></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Emoji</span>
                                <span class="metric-value" v-text="formatNumber(data.quality_metrics.hobbies.with_emoji)"></span>
                            </div>
                        </div>

                        <div class="metric-card status">
                            <h3>📈 Status Distribution</h3>
                            <div class="status-distribution">
                                <div v-for="(count, status) in data.quality_metrics.status.distribution" :key="status" class="status-item">
                                    <div class="label" v-text="status"></div>
                                    <div class="count" v-text="formatNumber(count)"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="issues-section">
                        <h2>🔍 Data Issues Detected</h2>
                        <div v-if="data.data_issues.length === 0" class="no-issues">
                            <p>✨ No data quality issues detected</p>
                        </div>
                        <div v-else class="issues-table-container">
                            <table class="issues-table">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Issue Type</th>
                                        <th>Count</th>
                                        <th>Severity</th>
                                        <th>Examples</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(issue, idx) in data.data_issues" :key="idx">
                                        <td><strong v-text="issue.field"></strong></td>
                                        <td v-text="formatIssueType(issue.issue_type)"></td>
                                        <td><strong v-text="formatNumber(issue.count)"></strong></td>
                                        <td><span :class="['severity-badge', issue.severity]" v-text="issue.severity"></span></td>
                                        <td>
                                            <div v-if="issue.examples.length > 0" class="examples-list">
                                                <span v-for="(example, i) in issue.examples" :key="i" v-text="example"></span>
                                            </div>
                                            <span v-else style="color: #999;">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="footer">
                        <div class="footer-info">
                            <strong>Analysis Time:</strong> <span v-text="data.took_ms + 'ms'"></span> |
                            <strong>Analyzed At:</strong> <span v-text="formatDateTime(data.analyzed_at)"></span>
                        </div>
                        <button @click="fetchQualityData" :disabled="loading" class="refresh-button">
                            ↻ Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================= DUPLICATES TAB ============================= -->
    <div id="tab-duplicates" hidden>
        <div class="panel-duplicates">
            <div class="container">
                <div class="header">
                    <h1>🔍 Duplicate Detection API</h1>
                    <p>Find potential duplicate accounts using various detection methods</p>
                </div>

                <div class="main-card">
                    <div class="content">
                        <div class="form-section">
                            <h2>🎯 Detection Settings</h2>

                            <div class="form-group">
                                <label for="dupMethod">Detection Method</label>
                                <select id="dupMethod">
                                    <option value="email">📧 Email Duplicates</option>
                                    <option value="phone">📱 Phone Duplicates</option>
                                    <option value="name">👤 Name Similarity</option>
                                    <option value="combined">🔗 Combined Detection</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="dupLimit">Result Limit (max: 500)</label>
                                <input type="number" id="dupLimit" value="10" min="1" max="500">
                            </div>

                            <div class="button-group">
                                <button class="btn-search" onclick="detectDuplicates()">🔍 Detect Duplicates</button>
                                <button class="btn-clear" onclick="clearDuplicateResults()">✕ Clear</button>
                            </div>
                        </div>

                        <div class="results-section">
                            <h2>📊 Results</h2>
                            <div id="dupResults"></div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>💡 Tip: Try different detection methods to see various results</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================= HEALTH TAB ============================= -->
    <div id="tab-health" hidden>
        <div class="bg-gray-900 text-gray-100">
            <div class="min-h-screen p-8">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-12">
                        <h1 class="text-4xl font-bold text-white mb-2">Health Check Dashboard</h1>
                        <p class="text-gray-400">Real-time system status monitoring</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-green-900 to-green-800 rounded-lg p-6 border border-green-700 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-200 text-sm font-semibold mb-2">Status</p>
                                    <p class="text-3xl font-bold text-green-100" id="healthStatus">Loading...</p>
                                </div>
                                <div class="text-4xl">
                                    <span id="healthStatusIcon" class="animate-pulse">⏳</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-lg p-6 border border-blue-700 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-200 text-sm font-semibold mb-2">Database</p>
                                    <p class="text-3xl font-bold text-blue-100" id="healthDatabase">Loading...</p>
                                </div>
                                <div class="text-4xl">
                                    <span id="healthDbIcon" class="animate-pulse">⏳</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-purple-900 to-purple-800 rounded-lg p-6 border border-purple-700 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-200 text-sm font-semibold mb-2">Total Records</p>
                                    <p class="text-3xl font-bold text-purple-100" id="healthTotalRecords">Loading...</p>
                                </div>
                                <div class="text-4xl">📊</div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-orange-900 to-orange-800 rounded-lg p-6 border border-orange-700 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-orange-200 text-sm font-semibold mb-2">Last Update</p>
                                    <p class="text-sm font-bold text-orange-100" id="healthTimestamp">Loading...</p>
                                </div>
                                <div class="text-4xl">🕐</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 shadow-lg mb-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Detailed Information</h2>

                        <div class="space-y-4">
                            <div>
                                <p class="text-gray-300 font-semibold mb-2">Raw Response:</p>
                                <pre class="bg-gray-900 p-4 rounded border border-gray-700 overflow-auto text-sm text-gray-200" id="healthJsonResponse">Loading...</pre>
                            </div>

                            <div class="bg-gray-700 p-4 rounded">
                                <p class="text-gray-300">
                                    <span class="font-semibold">Auto-refresh:</span> Every <span id="healthRefreshInterval" class="text-blue-400 font-bold">5</span> seconds
                                    <button onclick="toggleHealthAutoRefresh()" class="ml-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm font-semibold transition">
                                        <span id="healthToggleText">Pause</span>
                                    </button>
                                    <button onclick="refreshHealth()" class="ml-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded text-white text-sm font-semibold transition">
                                        Refresh Now
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 shadow-lg">
                        <h2 class="text-2xl font-bold text-white mb-6">Status History</h2>
                        <div class="h-64">
                            <canvas id="healthStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================= SCRIPTS ============================= -->

    <!-- Search tab logic (Vue 3) -->
    <script>
        (function () {
            const { createApp } = Vue;

            createApp({
                data() {
                    return {
                        search: { query: '', type: 'name', limit: 10, offset: 0 },
                        results: [],
                        pagination: { total: 0, limit: 10, offset: 0 },
                        loading: false,
                        error: null,
                        searched: false,
                        lastResponse: {},
                    };
                },
                methods: {
                    async performSearch() {
                        this.error = null;
                        this.loading = true;

                        try {
                            const params = new URLSearchParams({
                                q: this.search.query,
                                type: this.search.type,
                                limit: this.search.limit,
                                offset: this.search.offset,
                            });

                            const response = await fetch(`/api/search?${params}`);

                            if (!response.ok) {
                                const errorData = await response.json();
                                this.error = errorData.error || 'Search failed';
                                this.results = [];
                                this.searched = true;
                                return;
                            }

                            const data = await response.json();
                            this.lastResponse = data;
                            this.results = data.results;
                            this.pagination = { total: data.total, limit: data.limit, offset: data.offset };
                            this.searched = true;
                        } catch (err) {
                            this.error = 'Network error. Please try again.';
                            this.results = [];
                            this.searched = true;
                        } finally {
                            this.loading = false;
                        }
                    },
                    nextPage() {
                        this.search.offset += this.search.limit;
                        this.performSearch();
                    },
                    previousPage() {
                        this.search.offset = Math.max(0, this.search.offset - this.search.limit);
                        this.performSearch();
                    },
                    getStatusLabel(status) {
                        return ({ 0: 'Inactive', 1: 'Active', 2: 'Suspended' })[status] || 'Unknown';
                    },
                    getStatusClass(status) {
                        return ({
                            0: 'bg-gray-100 text-gray-800',
                            1: 'bg-green-100 text-green-800',
                            2: 'bg-red-100 text-red-800',
                        })[status] || 'bg-gray-100 text-gray-800';
                    },
                    formatDate(dateString) {
                        if (!dateString) return '-';
                        return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                    },
                },
            }).mount('#searchApp');
        })();
    </script>

    <!-- Quality tab logic (Vue 3) -->
    <script>
        (function () {
            const { createApp } = Vue;

            createApp({
                data() {
                    return { data: null, loading: true, error: null };
                },
                mounted() {
                    this.fetchQualityData();
                },
                methods: {
                    async fetchQualityData() {
                        this.loading = true;
                        this.error = null;

                        try {
                            const response = await fetch('/api/quality');
                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                            }
                            this.data = await response.json();
                        } catch (err) {
                            this.error = 'Failed to load quality data: ' + err.message;
                        } finally {
                            this.loading = false;
                        }
                    },
                    formatNumber(num) {
                        if (num === null || num === undefined) return '—';
                        return num.toLocaleString();
                    },
                    formatDateTime(iso) {
                        try {
                            return new Date(iso).toLocaleString();
                        } catch {
                            return iso;
                        }
                    },
                    formatIssueType(type) {
                        return type.replace(/_/g, ' ').split(' ')
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(' ');
                    },
                },
            }).mount('#qualityApp');
        })();
    </script>

    <!-- Duplicates tab logic (vanilla JS) -->
    <script>
        async function detectDuplicates() {
            const method = document.getElementById('dupMethod').value;
            const limit = document.getElementById('dupLimit').value;
            const resultsDiv = document.getElementById('dupResults');

            resultsDiv.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Detecting duplicates...</p>
                </div>
            `;

            try {
                const response = await fetch(`/api/duplicates/find?method=${method}&limit=${limit}`);
                const data = await response.json();

                if (!response.ok) {
                    resultsDiv.innerHTML = `<div class="error-message">❌ ${data.error || 'Error detecting duplicates'}</div>`;
                    return;
                }

                displayDuplicateResults(data);
            } catch (error) {
                resultsDiv.innerHTML = `<div class="error-message">❌ Error: ${error.message}</div>`;
            }
        }

        function displayDuplicateResults(data) {
            const resultsDiv = document.getElementById('dupResults');
            let html = '';

            html += `
                <div class="stats">
                    <strong>📈 Statistics</strong>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">${data.total_groups_found}</div>
                            <div class="stat-label">Groups Found</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${data.total_duplicate_users}</div>
                            <div class="stat-label">Duplicate Users</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${data.took_ms}ms</div>
                            <div class="stat-label">Query Time</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${data.method}</div>
                            <div class="stat-label">Method</div>
                        </div>
                    </div>
                </div>
            `;

            if (data.duplicate_groups && data.duplicate_groups.length > 0) {
                html += '<div class="groups-container">';

                data.duplicate_groups.forEach((group) => {
                    const confidenceBadgeClass = `badge-${group.confidence}`;

                    html += `
                        <div class="group-item">
                            <div class="group-header">
                                <span class="group-attribute">${group.shared_attribute || 'N/A'}</span>
                                <span class="group-badge ${confidenceBadgeClass}">${group.confidence.toUpperCase()}</span>
                            </div>
                            <div class="group-details">
                                <div class="detail-item">
                                    <span class="detail-label">Type:</span>
                                    <span class="detail-value">${group.attribute_type}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Users:</span>
                                    <span class="detail-value">${group.user_count}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Confidence:</span>
                                    <span class="detail-value">${(group.similarity_score * 100).toFixed(1)}%</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Type:</span>
                                    <span class="detail-value">${group.attribute_type}</span>
                                </div>
                            </div>
                            <div class="user-ids">
                                ${group.user_ids.map(id => `<span class="user-id-badge">#${id}</span>`).join('')}
                            </div>
                            ${group.match_reasons && group.match_reasons.length > 0 ? `
                                <div style="margin-top: 10px; font-size: 0.85em; color: #666;">
                                    <strong>Reasons:</strong> ${group.match_reasons.join(', ')}
                                </div>
                            ` : ''}
                        </div>
                    `;
                });

                html += '</div>';
            } else {
                html += `
                    <div class="empty-state">
                        <div class="empty-state-icon">✓</div>
                        <p>No duplicate groups found</p>
                    </div>
                `;
            }

            resultsDiv.innerHTML = html;
        }

        function clearDuplicateResults() {
            document.getElementById('dupResults').innerHTML = '';
            document.getElementById('dupMethod').value = 'email';
            document.getElementById('dupLimit').value = '10';
        }

        document.getElementById('dupResults').innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">👋</div>
                <p>Select settings and click "Detect Duplicates" to get started</p>
            </div>
        `;
    </script>

    <!-- Health tab logic (vanilla JS + Chart.js) -->
    <script>
        let healthAutoRefreshEnabled = true;
        let healthRefreshIntervalId = null;
        let healthStatusHistory = [];
        window.healthChart = null;

        function initHealthChart() {
            const ctx = document.getElementById('healthStatusChart').getContext('2d');
            window.healthChart = new Chart(ctx, {
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
                    plugins: { legend: { labels: { color: '#d1d5db' } } },
                    scales: {
                        y: { ticks: { color: '#d1d5db' }, grid: { color: 'rgba(107, 114, 128, 0.1)' } },
                        x: { ticks: { color: '#d1d5db' }, grid: { color: 'rgba(107, 114, 128, 0.1)' } }
                    }
                }
            });
        }

        async function refreshHealth() {
            try {
                const response = await fetch('/health');
                const data = await response.json();

                document.getElementById('healthStatus').textContent = data.status.toUpperCase();
                document.getElementById('healthStatusIcon').textContent = data.status === 'ready' ? '✅' : '⚠️';

                document.getElementById('healthDatabase').textContent = data.database.toUpperCase();
                document.getElementById('healthDbIcon').textContent = data.database === 'connected' ? '✅' : '❌';

                const formattedRecords = new Intl.NumberFormat().format(data.total_records);
                document.getElementById('healthTotalRecords').textContent = formattedRecords;

                const date = new Date(data.timestamp);
                document.getElementById('healthTimestamp').textContent = date.toLocaleString();

                document.getElementById('healthJsonResponse').textContent = JSON.stringify(data, null, 2);

                healthStatusHistory.push({
                    time: new Date(data.timestamp).toLocaleTimeString(),
                    records: data.total_records
                });

                if (healthStatusHistory.length > 20) {
                    healthStatusHistory.shift();
                }

                updateHealthChart();
            } catch (error) {
                console.error('Error fetching health status:', error);
                document.getElementById('healthStatus').textContent = 'ERROR';
                document.getElementById('healthStatusIcon').textContent = '❌';
                document.getElementById('healthDatabase').textContent = 'UNKNOWN';
                document.getElementById('healthDbIcon').textContent = '❓';
                document.getElementById('healthJsonResponse').textContent = `Error: ${error.message}`;
            }
        }

        function updateHealthChart() {
            if (window.healthChart) {
                window.healthChart.data.labels = healthStatusHistory.map(item => item.time);
                window.healthChart.data.datasets[0].data = healthStatusHistory.map(item => item.records);
                window.healthChart.update();
            }
        }

        function toggleHealthAutoRefresh() {
            healthAutoRefreshEnabled = !healthAutoRefreshEnabled;
            document.getElementById('healthToggleText').textContent = healthAutoRefreshEnabled ? 'Pause' : 'Resume';

            if (healthAutoRefreshEnabled) {
                startHealthAutoRefresh();
            } else {
                clearInterval(healthRefreshIntervalId);
            }
        }

        function startHealthAutoRefresh() {
            clearInterval(healthRefreshIntervalId);
            healthRefreshIntervalId = setInterval(() => {
                if (healthAutoRefreshEnabled) {
                    refreshHealth();
                }
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initHealthChart();
            refreshHealth();
            startHealthAutoRefresh();
        });

        window.addEventListener('beforeunload', () => {
            clearInterval(healthRefreshIntervalId);
        });
    </script>

    <!-- Tab switcher — just toggles visibility, no navigation, no other page is requested -->
    <script>
        (function () {
            const panels = {
                search: document.getElementById('tab-search'),
                quality: document.getElementById('tab-quality'),
                duplicates: document.getElementById('tab-duplicates'),
                health: document.getElementById('tab-health'),
            };
            const buttons = document.querySelectorAll('.tab-btn');
            const activeClasses = ['text-[#667eea]', 'border-[#667eea]'];
            const inactiveClasses = ['text-gray-500', 'border-transparent', 'hover:text-gray-700'];

            function activateTab(name) {
                Object.keys(panels).forEach(function (key) {
                    panels[key].hidden = key !== name;
                });

                buttons.forEach(function (btn) {
                    const isActive = btn.dataset.tab === name;
                    activeClasses.forEach(c => btn.classList.toggle(c, isActive));
                    inactiveClasses.forEach(c => btn.classList.toggle(c, !isActive));
                });

                if (name === 'health' && window.healthChart) {
                    window.healthChart.resize();
                }
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    activateTab(btn.dataset.tab);
                });
            });
        })();
    </script>
</body>
</html>
