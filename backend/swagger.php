<?php
require_once 'middleware.php';

header('Content-Type: text/html; charset=UTF-8');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit();
}

// Get the current server URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Manager API - Swagger Documentation</title>

    <!-- Swagger UI CSS -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui.css" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.10.5/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.10.5/favicon-16x16.png" sizes="16x16" />

    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }

        *, *:before, *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
            font-family: "Open Sans", sans-serif;
        }

        .custom-header {
            background: #1f2937;
            color: white;
            padding: 1rem;
            text-align: center;
            margin-bottom: 0;
        }

        .custom-header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .custom-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.8;
        }

        .server-info {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin: 1rem;
            border-radius: 4px;
        }

        .server-info h3 {
            margin: 0 0 0.5rem 0;
            color: #007bff;
        }

        .server-info code {
            background: #e9ecef;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }

        #swagger-ui {
            max-width: none;
        }

        .swagger-ui .topbar {
            display: none;
        }
    </style>
</head>

<body>
    <div class="custom-header">
        <h1>📱 Contact Manager API</h1>
        <p>Interactive API Documentation & Testing Interface</p>
    </div>

    <div class="server-info">
        <h3>🌐 Server Information</h3>
        <p><strong>Current Server:</strong> <code><?php echo $baseUrl; ?></code></p>
        <p><strong>API Version:</strong> <code>1.0.0</code></p>
        <p><strong>Documentation Generated:</strong> <code><?php echo date('Y-m-d H:i:s T'); ?></code></p>

        <h4>📋 Quick Links:</h4>
        <ul>
            <li><a href="<?php echo $baseUrl; ?>/api" target="_blank">API Info</a> - Get API information and endpoints</li>
            <li><a href="<?php echo $baseUrl; ?>/health" target="_blank">Health Check</a> - API health status</li>
            <li><a href="<?php echo $baseUrl; ?>/db" target="_blank">Database Test</a> - Test database connectivity</li>
            <li><a href="<?php echo $baseUrl; ?>/logs" target="_blank">Request Logs</a> - View API request logs</li>
        </ul>

        <h4>🔧 Testing Tips:</h4>
        <ul>
            <li>Use the "Try it out" button to test endpoints directly</li>
            <li>All main API endpoints require <strong>POST</strong> requests with JSON data</li>
            <li>Authentication endpoints: <code>/Login</code> and <code>/Register</code></li>
            <li>Contact management: <code>/getContacts</code>, <code>/addContact</code>, <code>/updateContact</code>, <code>/deleteContact</code>, <code>/searchContacts</code></li>
        </ul>
    </div>

    <div id="swagger-ui"></div>

    <!-- Swagger UI Bundle JS -->
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-standalone-preset.js"></script>

    <script>
    window.onload = function() {
        // Begin Swagger UI call region
        const ui = SwaggerUIBundle({
            url: '<?php echo $baseUrl; ?>/swagger.json',
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout",
            validatorUrl: null,
            tryItOutEnabled: true,
            requestInterceptor: function(request) {
                // Add timestamp to help with debugging
                console.log('API Request:', request.method, request.url);

                // Ensure proper headers
                if (request.method === 'POST' || request.method === 'PUT' || request.method === 'PATCH') {
                    request.headers['Content-Type'] = 'application/json';
                }

                return request;
            },
            responseInterceptor: function(response) {
                // Log responses for debugging
                console.log('API Response:', response.status, response.url);
                return response;
            },
            onComplete: function() {
                console.log('Swagger UI loaded successfully');
            },
            onFailure: function(error) {
                console.error('Failed to load Swagger UI:', error);
            },
            docExpansion: 'list',
            operationsSorter: 'alpha',
            tagsSorter: 'alpha',
            filter: true,
            showRequestHeaders: true,
            showCommonExtensions: true,
            defaultModelExpandDepth: 2,
            defaultModelsExpandDepth: 1
        });

        // Add custom styling after load
        setTimeout(function() {
            // Hide the Swagger UI topbar if it exists
            const topbar = document.querySelector('.swagger-ui .topbar');
            if (topbar) {
                topbar.style.display = 'none';
            }

            // Add custom server selector if multiple servers
            const infoSection = document.querySelector('.swagger-ui .info');
            if (infoSection) {
                const serverList = document.createElement('div');
                serverList.className = 'servers';
                serverList.innerHTML = `
                    <div class="servers-title">
                        <h4>🖥️ Available Servers:</h4>
                    </div>
                    <div class="servers-list">
                        <div class="server">
                            <strong>Production:</strong> <?php echo $baseUrl; ?>
                        </div>
                        <div class="server">
                            <strong>Local:</strong> http://localhost:8000
                        </div>
                    </div>
                `;
                infoSection.appendChild(serverList);
            }
        }, 1000);

        // End Swagger UI call region
    };
    </script>

    <!-- Custom footer -->
    <div style="text-align: center; padding: 2rem; color: #666; border-top: 1px solid #eee; margin-top: 2rem;">
        <p>
            <strong>Contact Manager API Documentation</strong><br>
            Built with ❤️ using Swagger UI<br>
            Last updated: <?php echo date('Y-m-d H:i:s T'); ?>
        </p>
        <p>
            <a href="<?php echo $baseUrl; ?>/swagger.json" target="_blank">📄 View Raw OpenAPI Spec</a> |
            <a href="<?php echo $baseUrl; ?>/api" target="_blank">🔧 API Info</a> |
            <a href="<?php echo $baseUrl; ?>/logs" target="_blank">📊 Request Logs</a>
        </p>
    </div>

    <script>
    // Additional helper functions
    function testEndpoint(method, endpoint, data = null) {
        const url = '<?php echo $baseUrl; ?>' + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        return fetch(url, options)
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                return data;
            })
            .catch(error => {
                console.error('Error:', error);
                throw error;
            });
    }

    // Make test function available globally for console testing
    window.testApi = testEndpoint;

    // Log helpful message
    console.log('💡 Tip: Use window.testApi(method, endpoint, data) to test endpoints from the browser console!');
    console.log('Example: window.testApi("GET", "/health")');
    </script>
</body>
</html>
