<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation — User Data Console</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; background: #fafafa; }
        .topbar-replacement {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .topbar-replacement h1 { margin: 0; font-size: 1.35rem; }
        .topbar-replacement p { margin: 0.35rem 0 0; opacity: 0.9; font-size: 0.9rem; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
    <div class="topbar-replacement">
        <h1>🗂️ User Data Console — API Documentation</h1>
        <p>OpenAPI 3.0 reference for every JSON endpoint backing the search, quality, duplicates, profile, and health tools.</p>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: '/openapi.json',
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: 'StandaloneLayout',
                deepLinking: true,
                docExpansion: 'list',
                defaultModelsExpandDepth: 1,
            });
        };
    </script>
</body>
</html>
