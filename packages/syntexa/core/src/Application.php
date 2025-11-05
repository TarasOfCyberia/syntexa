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
        
        if (!$route) {
            echo "⚠️  No route found for: {$request->getPath()} ({$request->getMethod()})\n";
        } else {
            echo "✅ Found route: {$route['path']} -> {$route['class']}\n";
        }
        
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
            // HTTP Request/Handler flow
            if (($route['type'] ?? null) === 'http-request') {
                echo "🔄 Processing http-request route\n";
                $requestClass = $route['class'];
                $responseClass = $route['responseClass'] ?? null;
                $handlerClasses = $route['handlers'] ?? [];

                echo "📦 Request class: {$requestClass}\n";
                echo "📦 Response class: " . ($responseClass ?? 'null') . "\n";
                echo "📦 Handlers: " . count($handlerClasses) . "\n";

                // Instantiate DTOs
                $reqDto = class_exists($requestClass) ? new $requestClass() : null;
                if (!$reqDto) {
                    throw new \RuntimeException("Cannot instantiate request class: {$requestClass}");
                }
                $resDto = ($responseClass && class_exists($responseClass)) ? new $responseClass() : null;

                // Fallback generic response if none supplied
                if ($resDto === null) {
                    echo "⚠️  Using fallback GenericResponse\n";
                    $resDto = new \Syntexa\Core\Http\Response\GenericResponse();
                }

                // Apply AsHttpResponse defaults if present
                if ($resDto) {
                    try {
                        $r = new \ReflectionClass($resDto);
                        $attrs = $r->getAttributes('Syntexa\\Core\\Attributes\\AsHttpResponse');
                        if (!empty($attrs)) {
                            $a = $attrs[0]->newInstance();
                            if (method_exists($resDto, 'setRenderHandle')) {
                                $resDto->setRenderHandle($a->handle ?? '');
                            }
                            if (method_exists($resDto, 'setRenderContext') && isset($a->context)) {
                                $resDto->setRenderContext($a->context);
                            }
                            if (method_exists($resDto, 'setRenderFormat')) {
                                $resDto->setRenderFormat($a->format ?? null);
                            }
                            if (method_exists($resDto, 'setRendererClass')) {
                                $resDto->setRendererClass($a->renderer ?? null);
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                // Execute handlers in order
                foreach ($handlerClasses as $handlerClass) {
                    echo "🔄 Executing handler: {$handlerClass}\n";
                    if (!class_exists($handlerClass)) {
                        echo "⚠️  Handler class not found: {$handlerClass}\n";
                        continue;
                    }
                    $handler = new $handlerClass();
                    if (method_exists($handler, 'handle')) {
                        $resDto = $handler->handle($reqDto, $resDto);
                        echo "✅ Handler executed: {$handlerClass}\n";
                    }
                }

                // Centralized rendering step (if requested by handlers)
                if (method_exists($resDto, 'getRenderHandle')) {
                    $handle = $resDto->getRenderHandle();
                    if ($handle) {
                        $context = method_exists($resDto, 'getRenderContext') ? $resDto->getRenderContext() : [];
                        $format = method_exists($resDto, 'getRenderFormat') ? $resDto->getRenderFormat() : null;
                        if ($format === null) {
                            // default to layout when handle provided
                            $format = \Syntexa\Core\Http\Response\ResponseFormat::Layout;
                        }
                        $rendererClass = method_exists($resDto, 'getRendererClass') ? $resDto->getRendererClass() : null;

                        if ($format === \Syntexa\Core\Http\Response\ResponseFormat::Json) {
                            $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            if (method_exists($resDto, 'setContent')) {
                                $resDto->setContent($json ?: '');
                            }
                            if (method_exists($resDto, 'setHeader')) {
                                $resDto->setHeader('Content-Type', 'application/json');
                            }
                        } elseif ($format === \Syntexa\Core\Http\Response\ResponseFormat::Layout) {
                            // Use provided renderer or default LayoutRenderer
                            $renderer = $rendererClass ?: 'Syntexa\\Frontend\\Layout\\LayoutRenderer';
                            if (class_exists($renderer) && method_exists($renderer, 'renderHandle')) {
                                $html = $renderer::renderHandle($handle, $context);
                                if (method_exists($resDto, 'setContent')) {
                                    $resDto->setContent($html);
                                }
                                if (method_exists($resDto, 'setHeader')) {
                                    $resDto->setHeader('Content-Type', 'text/html; charset=UTF-8');
                                }
                            }
                        } else {
                            // raw/no-op
                        }
                    }
                }

                // Adapt to core Response
                if (method_exists($resDto, 'toCoreResponse')) {
                    echo "✅ Converting to Core Response\n";
                    return $resDto->toCoreResponse();
                }
                // Generic fallback
                echo "⚠️  Using generic JSON fallback\n";
                return Response::json(['ok' => true]);
            }

            // Legacy controller flow
            $controller = new $route['class']();
            $method = $route['method'];
            $response = $method === '__invoke' ? $controller() : $controller->$method();
            return $response;
        } catch (\Throwable $e) {
            echo "❌ Error in handleRoute: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
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
