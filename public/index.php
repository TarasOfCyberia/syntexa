<?php

declare(strict_types=1);

use Syntexa\Core\Application;
use Syntexa\Core\ErrorHandler;
use Syntexa\Core\Request;

/**
 * Traditional PHP entry point for Apache/Nginx
 * This file serves as the web root entry point
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Create application
$app = new Application();

// Configure error handling
ErrorHandler::configure($app->getEnvironment());

// Handle request
$request = Request::create();
$response = $app->handleRequest($request);

// Send response
$response->send();
