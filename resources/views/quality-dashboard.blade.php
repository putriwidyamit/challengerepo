<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Quality Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        #app {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .loading-container,
        .error-container,
        .empty-container {
            background: white;
            padding: 60px 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            color: #d32f2f;
            font-weight: 600;
            margin-top: 15px;
        }
        
        .empty-message {
            color: #999;
            font-weight: 500;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }
        
        .metric-card.email { border-left-color: #ff9800; }
        .metric-card.phone { border-left-color: #f44336; }
        .metric-card.birth_date { border-left-color: #2196f3; }
        .metric-card.hobbies { border-left-color: #4caf50; }
        .metric-card.status { border-left-color: #9c27b0; }
        
        .metric-card h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 16px;
            text-transform: capitalize;
        }
        
        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .metric-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .metric-label {
            color: #666;
            font-size: 13px;
            font-weight: 500;
        }
        
        .metric-value {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        
        .metric-value.number {
            color: #667eea;
        }
        
        .metric-value.percent {
            color: #ff9800;
        }
        
        .status-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        
        .status-item {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }
        
        .status-item .label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        
        .status-item .count {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .issues-section {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .issues-section h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .issues-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .issues-table thead {
            background: #f5f5f5;
        }
        
        .issues-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .issues-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .issues-table tbody tr:hover {
            background: #f9f9f9;
        }
        
        .severity-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .severity-badge.low {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .severity-badge.medium {
            background: #fff3e0;
            color: #e65100;
        }
        
        .severity-badge.high {
            background: #ffebee;
            color: #c62828;
        }
        
        .examples-list {
            max-width: 100%;
            overflow-x: auto;
        }
        
        .examples-list span {
            display: inline-block;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 3px;
            margin-right: 8px;
            margin-bottom: 4px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 11px;
            color: #555;
        }
        
        .no-issues {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .footer {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .footer-info {
            font-size: 13px;
            color: #666;
        }
        
        .refresh-button {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .refresh-button:hover {
            background: #5568d3;
        }
        
        .refresh-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .issues-table {
                font-size: 12px;
            }
            
            .issues-table th,
            .issues-table td {
                padding: 8px;
            }
            
            .footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="header">
            <h1>📊 Data Quality Dashboard</h1>
            <p class="subtitle">Real-time analysis of ws_user table data quality metrics</p>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
            <div class="loading-spinner"></div>
            <p style="margin-top: 20px; color: #999;">Analyzing data quality...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="error-container">
            <p style="font-size: 48px; margin-bottom: 15px;">⚠️</p>
            <p class="error-message" v-text="error"></p>
            <button @click="fetchQualityData" class="refresh-button" style="margin-top: 20px;">
                Retry
            </button>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="!data" class="empty-container">
            <p class="empty-message">No data available</p>
        </div>
        
        <!-- Content -->
        <div v-else>
            <!-- Metrics Grid -->
            <div class="metrics-grid">
                <!-- Email Metrics -->
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
                
                <!-- Phone Metrics -->
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
                
                <!-- Birth Date Metrics -->
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
                
                <!-- Hobbies Metrics -->
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
                
                <!-- Status Distribution -->
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
            
            <!-- Data Issues Section -->
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
            
            <!-- Footer -->
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
    
    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    data: null,
                    loading: true,
                    error: null,
                };
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
        }).mount('#app');
    </script>
</body>
</html>
