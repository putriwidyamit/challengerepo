<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicate Detection API</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            animation: slideDown 0.6s ease;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 40px;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
        }

        .form-section {
            display: flex;
            flex-direction: column;
        }

        .form-section h2 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-search:active {
            transform: translateY(0);
        }

        .btn-clear {
            background: #f5f5f5;
            color: #666;
        }

        .btn-clear:hover {
            background: #efefef;
        }

        .results-section {
            display: flex;
            flex-direction: column;
        }

        .results-section h2 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #667eea;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background: #fee;
            border: 2px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .stats {
            background: #f9f9f9;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.9em;
            color: #999;
            margin-top: 5px;
        }

        .groups-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .group-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .group-item:hover {
            background: #f9f9f9;
        }

        .group-item:last-child {
            border-bottom: none;
        }

        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .group-attribute {
            font-weight: 600;
            color: #333;
            word-break: break-all;
        }

        .group-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-high {
            background: #d4edda;
            color: #155724;
        }

        .badge-medium {
            background: #fff3cd;
            color: #856404;
        }

        .badge-low {
            background: #f8d7da;
            color: #721c24;
        }

        .group-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
            font-size: 0.9em;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            color: #999;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
        }

        .user-ids {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 8px;
        }

        .user-id-badge {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            font-size: 0.9em;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Duplicate Detection API</h1>
            <p>Find potential duplicate accounts using various detection methods</p>
        </div>

        <div class="main-card">
            <div class="content">
                <!-- Form Section -->
                <div class="form-section">
                    <h2>🎯 Detection Settings</h2>

                    <div class="form-group">
                        <label for="method">Detection Method</label>
                        <select id="method">
                            <option value="email">📧 Email Duplicates</option>
                            <option value="phone">📱 Phone Duplicates</option>
                            <option value="name">👤 Name Similarity</option>
                            <option value="combined">🔗 Combined Detection</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="limit">Result Limit (max: 500)</label>
                        <input type="number" id="limit" value="10" min="1" max="500">
                    </div>

                    <div class="button-group">
                        <button class="btn-search" onclick="detectDuplicates()">🔍 Detect Duplicates</button>
                        <button class="btn-clear" onclick="clearResults()">✕ Clear</button>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="results-section">
                    <h2>📊 Results</h2>
                    <div id="results"></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>💡 Tip: Try different detection methods to see various results</p>
        </div>
    </div>

    <script>
        async function detectDuplicates() {
            const method = document.getElementById('method').value;
            const limit = document.getElementById('limit').value;
            const resultsDiv = document.getElementById('results');

            // Show loading
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

                displayResults(data);
            } catch (error) {
                resultsDiv.innerHTML = `<div class="error-message">❌ Error: ${error.message}</div>`;
            }
        }

        function displayResults(data) {
            const resultsDiv = document.getElementById('results');
            let html = '';

            // Stats
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

            // Groups
            if (data.duplicate_groups && data.duplicate_groups.length > 0) {
                html += '<div class="groups-container">';

                data.duplicate_groups.forEach((group, index) => {
                    const confidenceBadgeClass = `badge-${group.confidence}`;
                    const userIds = group.user_ids.join(', ');
                    const userNames = group.user_names.join(', ');

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

        function clearResults() {
            document.getElementById('results').innerHTML = '';
            document.getElementById('method').value = 'email';
            document.getElementById('limit').value = '10';
        }

        // Load results on page load
        window.addEventListener('load', () => {
            document.getElementById('results').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">👋</div>
                    <p>Select settings and click "Detect Duplicates" to get started</p>
                </div>
            `;
        });
    </script>
</body>
</html>
