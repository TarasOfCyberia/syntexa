<?php

declare(strict_types=1);

use Swoole\Http\Server;
use Syntexa\Core\Application;
use Syntexa\Core\ErrorHandler;
use Syntexa\Core\Request;

/**
 * Swoole server entry point
 * This file starts the Swoole HTTP server
 */

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Check if Swoole is available
if (!extension_loaded('swoole')) {
    die("Swoole extension is required but not installed.\n");
}

// Create application to get environment
$app = new Application();
$env = $app->getEnvironment();

// Configure error handling
ErrorHandler::configure($env);

// Create Swoole HTTP server with environment configuration
$server = new Server($env->swooleHost, $env->swoolePort);

// Server configuration from environment
$server->set([
    'worker_num' => $env->swooleWorkerNum,
    'max_request' => $env->swooleMaxRequest,
    'enable_coroutine' => true,
    'max_coroutine' => $env->swooleMaxCoroutine,
    'log_file' => $env->swooleLogFile,
    'log_level' => $env->swooleLogLevel,
]);

// Server events
$server->on("start", function ($server) use ($env) {
    echo "Syntexa Framework - Swoole Mode\n";
    echo "Server started at http://{$env->swooleHost}:{$env->swoolePort}\n";
    echo "Mode: " . ($env->isDev() ? 'development' : 'production') . "\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Swoole Version: " . swoole_version() . "\n";
    echo "Workers: {$env->swooleWorkerNum}\n";
    echo "Max Requests: {$env->swooleMaxRequest}\n";
});

$server->on("request", function ($request, $response) use ($env) {
    // Set CORS headers from environment
    $response->header("Access-Control-Allow-Origin", $env->corsAllowOrigin);
    $response->header("Access-Control-Allow-Methods", $env->corsAllowMethods);
    $response->header("Access-Control-Allow-Headers", $env->corsAllowHeaders);
    
    if ($env->corsAllowCredentials) {
        $response->header("Access-Control-Allow-Credentials", "true");
    }
    
    // Handle preflight requests
    if ($request->server['request_method'] === 'OPTIONS') {
        $response->status(200);
        $response->end();
        return;
    }
    
    // Set content type
    $response->header("Content-Type", "application/json");
    
    // Create application
    $app = new Application();
    
    // Create Request object from Swoole request
    $syntexaRequest = Request::create($request);
    
    // Handle request
    $syntexaResponse = $app->handleRequest($syntexaRequest);
    
    // Set status code
    $response->status($syntexaResponse->getStatusCode());
    
    // Set headers
    foreach ($syntexaResponse->getHeaders() as $name => $value) {
        $response->header($name, $value);
    }
    
    // Output response
    $response->end($syntexaResponse->getContent());
});

// Start the server
$server->start();
