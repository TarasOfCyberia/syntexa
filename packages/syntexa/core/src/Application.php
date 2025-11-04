<?php

declare(strict_types=1);

namespace Syntexa\Core;

use Syntexa\Core\Discovery\AttributeDiscovery;

/**
 * Minimal Syntexa Application
 */
class Application
{
    private Environment $environment;
    
    public function __construct()
    {
        $this->environment = Environment::create();
    }
    
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
    
    public function handleRequest(Request $request): Response
    {
        // Initialize attribute discovery
        AttributeDiscovery::initialize();
        
        // Try to find route using AttributeDiscovery
        $route = AttributeDiscovery::findRoute($request->getPath(), $request->getMethod());
        
        if ($route) {
            return $this->handleRoute($route, $request);
        }
        
        // Fallback to simple routing
        $path = $request->getPath();
        if ($path === '/' || $path === '') {
            return $this->helloWorld($request);
        }

        return $this->notFound($request);
    }
    
    private function handleRoute(array $route, Request $request): Response
    {
        try {
            // Create controller instance
            $controller = new $route['class']();
            
            // Call the method
            $method = $route['method'];
            
            if ($method === '__invoke') {
                // Single action controller
                $response = $controller();
            } else {
                // Multi-action controller
                $response = $controller->$method();
            }
            
            return $response;
        } catch (\Throwable $e) {
            return Response::json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    private function helloWorld(Request $request): Response
    {
        return Response::json([
            'message' => 'Hello World from Syntexa!',
            'framework' => $this->environment->get('APP_NAME', 'Syntexa'),
            'mode' => $this->detectRuntimeMode($request),
            'environment' => $this->environment->get('APP_ENV', 'prod'),
            'debug' => $this->environment->isDebug(),
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'swoole_server' => $request->getServer('SWOOLE_SERVER', 'not-set'),
            'server_software' => $request->getServer('SERVER_SOFTWARE', 'not-set'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function detectRuntimeMode(Request $request): string
    {
        return 'swoole';
    }
    
    private function notFound(Request $request): Response
    {
        return Response::notFound('The requested resource was not found');
    }
}
