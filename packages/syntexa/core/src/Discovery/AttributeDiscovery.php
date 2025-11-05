<?php

declare(strict_types=1);

namespace Syntexa\Core\Discovery;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Attributes\AsHttpRequest;
use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\ModuleRegistry;
use Syntexa\Core\IntelligentAutoloader;
use ReflectionClass;
use ReflectionMethod;

/**
 * Discovers and caches attributes from PHP classes
 * 
 * This class scans the src/ directory for classes with specific attributes
 * and builds a registry of controllers and routes.
 */
class AttributeDiscovery
{
    private static array $controllers = [];
    private static array $routes = [];
    private static array $httpRequests = [];
    private static array $httpHandlers = [];
    private static bool $initialized = false;
    
    /**
     * Initialize the discovery system
     * This should be called once at server startup
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }
        
        echo "🔍 Discovering attributes...\n";
        
        $startTime = microtime(true);
        
        // Initialize intelligent autoloader first
        IntelligentAutoloader::initialize();
        
        // Initialize module registry
        ModuleRegistry::initialize();
        
        // Scan attributes using intelligent autoloader
        self::scanAttributesIntelligently();
        
        $endTime = microtime(true);
        
        echo "✅ Found " . count(self::$controllers) . " controllers\n";
        echo "✅ Found " . count(self::$routes) . " routes\n";
        echo "✅ Found " . count(self::$httpRequests) . " http requests\n";
        echo "⏱️  Discovery took " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
        
        self::$initialized = true;
    }
    
    /**
     * Get all discovered controllers
     */
    public static function getControllers(): array
    {
        return self::$controllers;
    }
    
    /**
     * Get all discovered routes
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }
    
    /**
     * Find a route by path and method
     */
    public static function findRoute(string $path, string $method = 'GET'): ?array
    {
        echo "🔍 Looking for route: {$path} ({$method})\n";
        foreach (self::$routes as $route) {
            if ($route['path'] === $path && in_array($method, $route['methods'])) {
                echo "✅ Found matching route: {$route['path']}\n";
                // enrich with http request handlers if applicable
                if (($route['type'] ?? null) === 'http-request') {
                    $reqClass = $route['class'];
                    echo "📦 Enriching http-request route with class: {$reqClass}\n";
                    $extra = self::$httpRequests[$reqClass] ?? null;
                    if ($extra) {
                        $route['handlers'] = $extra['handlers'];
                        $route['responseClass'] = $extra['responseClass'];
                        echo "✅ Enriched with " . count($extra['handlers']) . " handlers\n";
                    } else {
                        echo "⚠️  No extra data found for request class: {$reqClass}\n";
                    }
                }
                return $route;
            }
        }
        
        echo "⚠️  No route found for {$path} ({$method})\n";
        return null;
    }
    
    /**
     * Scan attributes using intelligent autoloader
     */
    private static function scanAttributesIntelligently(): void
    {
        // Find all classes with AsController attribute (legacy/simple)
        $controllerClasses = array_filter(
            IntelligentAutoloader::findClassesWithAttribute(AsController::class),
            fn ($class) => str_starts_with($class, 'Syntexa\\')
        );
        
        echo "🧠 Found " . count($controllerClasses) . " controller classes\n";
        
        foreach ($controllerClasses as $className) {
            try {
                $class = new ReflectionClass($className);
                self::analyzeClass($class);
            } catch (\Throwable $e) {
                echo "⚠️  Error analyzing class {$className}: " . $e->getMessage() . "\n";
            }
        }

        // Find all classes with AsHttpRequest attribute
        $httpRequestClasses = array_filter(
            IntelligentAutoloader::findClassesWithAttribute(AsHttpRequest::class),
            fn ($class) => str_starts_with($class, 'Syntexa\\')
        );
        echo "🔍 Found " . count($httpRequestClasses) . " http request classes\n";
        foreach ($httpRequestClasses as $className) {
            try {
                $class = new ReflectionClass($className);
                $attrs = $class->getAttributes(AsHttpRequest::class);
                if (!empty($attrs)) {
                    /** @var AsHttpRequest $attr */
                    $attr = $attrs[0]->newInstance();
                    self::$httpRequests[$class->getName()] = [
                        'requestClass' => $class->getName(),
                        'path' => $attr->path,
                        'methods' => $attr->methods,
                        'name' => $attr->name ?? $class->getShortName(),
                        'responseClass' => $attr->responseWith ?: null,
                        'file' => $class->getFileName(),
                        'handlers' => [],
                    ];

                    // also index into routes for lookup by path/method
                    self::$routes[] = [
                        'path' => $attr->path,
                        'methods' => $attr->methods,
                        'name' => $attr->name ?? $class->getShortName(),
                        'class' => $class->getName(),
                        'method' => '__invoke',
                        'requirements' => $attr->requirements,
                        'defaults' => $attr->defaults,
                        'options' => $attr->options,
                        'type' => 'http-request'
                    ];
                    echo "✅ Registered http-request: {$attr->path} -> {$class->getName()}\n";
                }
            } catch (\Throwable $e) {
                echo "⚠️  Error analyzing http request {$className}: " . $e->getMessage() . "\n";
            }
        }

        // Find handlers and map to requests
        $httpHandlerClasses = array_filter(
            IntelligentAutoloader::findClassesWithAttribute(AsHttpHandler::class),
            fn ($class) => str_starts_with($class, 'Syntexa\\')
        );
        echo "🔍 Found " . count($httpHandlerClasses) . " http handler classes\n";
        foreach ($httpHandlerClasses as $className) {
            try {
                $class = new ReflectionClass($className);
                $attrs = $class->getAttributes(AsHttpHandler::class);
                if (!empty($attrs)) {
                    /** @var AsHttpHandler $attr */
                    $attr = $attrs[0]->newInstance();
                    $for = $attr->getFor();
                    echo "🔗 Handler: {$class->getName()} -> for: {$for}\n";
                    self::$httpHandlers[$class->getName()] = [
                        'handlerClass' => $class->getName(),
                        'for' => $for,
                    ];
                    if (isset(self::$httpRequests[$for])) {
                        self::$httpRequests[$for]['handlers'][] = $class->getName();
                        echo "✅ Mapped handler {$class->getName()} to request {$for}\n";
                    } else {
                        echo "⚠️  Request class not found for handler: {$for}\n";
                    }
                }
            } catch (\Throwable $e) {
                echo "⚠️  Error analyzing http handler {$className}: " . $e->getMessage() . "\n";
            }
        }

        // Discover frontend block handlers (optional)
        if (class_exists('Syntexa\\Frontend\\Attributes\\AsBlockHandler') && class_exists('Syntexa\\Frontend\\Layout\\BlockHandlerRegistry')) {
            $asBlockHandler = 'Syntexa\\Frontend\\Attributes\\AsBlockHandler';
            $blockHandlerClasses = IntelligentAutoloader::findClassesWithAttribute($asBlockHandler);
            echo "🔍 Found " . count($blockHandlerClasses) . " block handler classes\n";
            foreach ($blockHandlerClasses as $className) {
                try {
                    $class = new \ReflectionClass($className);
                    $attrs = $class->getAttributes($asBlockHandler);
                    if (!empty($attrs)) {
                        $attr = $attrs[0]->newInstance();
                        $for = $attr->getFor();
                        $prio = $attr->getPriority();
                        \Syntexa\Frontend\Layout\BlockHandlerRegistry::register($for, $class->getName(), $prio);
                        echo "✅ Registered block handler {$class->getName()} for {$for} (priority {$prio})\n";
                    }
                } catch (\Throwable $e) {
                    echo "⚠️  Error analyzing block handler {$className}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    /**
     * Scan all PHP files in discovered modules (legacy method)
     */
    private static function scanAllAttributes(): void
    {
        $modules = ModuleRegistry::getModules();
        
        foreach ($modules as $module) {
            echo "🔍 Scanning module: {$module['name']} ({$module['type']})\n";
            
            $files = self::getAllPhpFiles($module['path']);
            
            foreach ($files as $file) {
                try {
                    $class = self::loadClassFromFile($file);
                    if ($class) {
                        self::analyzeClass($class);
                    }
                } catch (\Throwable $e) {
                    echo "⚠️  Error analyzing file {$file}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    /**
     * Get all PHP files recursively
     */
    private static function getAllPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Load class from file
     */
    private static function loadClassFromFile(string $file): ?ReflectionClass
    {
        // Skip vendor files
        if (strpos($file, '/vendor/') !== false) {
            return null;
        }
        
        // Extract namespace and class name from file
        $content = file_get_contents($file);
        
        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            return null;
        }
        
        if (!preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return null;
        }
        
        $fullClassName = $namespaceMatches[1] . '\\' . $classMatches[1];
        
        // Load the file if class doesn't exist
        if (!class_exists($fullClassName)) {
            require_once $file;
        }
        
        // Check if class exists after loading
        if (!class_exists($fullClassName)) {
            return null;
        }
        
        return new ReflectionClass($fullClassName);
    }
    
    /**
     * Analyze a class for attributes
     */
    private static function analyzeClass(ReflectionClass $class): void
    {
        // Check for AsController attribute
        $controllerAttributes = $class->getAttributes(AsController::class);
        if (!empty($controllerAttributes)) {
            $controllerAttr = $controllerAttributes[0]->newInstance();
            
            self::$controllers[] = [
                'class' => $class->getName(),
                'name' => $controllerAttr->name ?? $class->getShortName(),
                'tags' => $controllerAttr->tags,
                'public' => $controllerAttr->public,
                'file' => $class->getFileName()
            ];
            
            // New approach: AsController defines the route directly
            if (!empty($controllerAttr->path)) {
                echo "🎯 Found single-action controller: {$class->getName()} -> {$controllerAttr->path}\n";
                self::$routes[] = [
                    'path' => $controllerAttr->path,
                    'methods' => $controllerAttr->methods,
                    'name' => $controllerAttr->name ?? $class->getShortName(),
                    'class' => $class->getName(),
                    'method' => '__invoke', // Single action controller
                    'requirements' => $controllerAttr->requirements,
                    'defaults' => $controllerAttr->defaults,
                    'options' => $controllerAttr->options
                ];
            } else {
                // Legacy approach: Analyze methods for routes
                self::analyzeMethods($class);
            }
        }
    }
    
    /**
     * Analyze class methods for Route attributes (legacy)
     */
    private static function analyzeMethods(ReflectionClass $class): void
    {
        // Legacy per-method routes via Route attribute are no longer supported.
        // Keep the method for backward compatibility without doing anything.
    }
}
