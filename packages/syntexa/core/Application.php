<?php

declare(strict_types=1);

namespace Syntexa\Core;

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
        // Simple routing
        $path = $request->getPath();
        
        if ($path === '/' || $path === '') {
            return $this->helloWorld($request);
        }
        
        return $this->notFound($request);
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
        // Check if running in Swoole server
        if (extension_loaded('swoole') && $request->getServer('SWOOLE_SERVER') === '1') {
            return 'swoole';
        }
        
        // Check if running in traditional PHP-FPM
        $serverSoftware = $request->getServer('SERVER_SOFTWARE');
        if ($serverSoftware && (str_contains($serverSoftware, 'nginx') || str_contains($serverSoftware, 'apache'))) {
            return 'php-fpm';
        }
        
        // Check if running in PHP built-in server
        if ($serverSoftware && str_contains($serverSoftware, 'PHP')) {
            return 'php-builtin';
        }
        
        // Check if Swoole extension is available but not used
        if (extension_loaded('swoole')) {
            return 'php-builtin-with-swoole';
        }
        
        // Default fallback
        return 'unknown';
    }
    
    private function notFound(Request $request): Response
    {
        return Response::notFound('The requested resource was not found');
    }
}
