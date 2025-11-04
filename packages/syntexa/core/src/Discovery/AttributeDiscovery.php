<?php

declare(strict_types=1);

namespace Syntexa\Core\Discovery;

use Syntexa\Core\Attributes\AsController;
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
        foreach (self::$routes as $route) {
            if ($route['path'] === $path && in_array($method, $route['methods'])) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Scan attributes using intelligent autoloader
     */
    private static function scanAttributesIntelligently(): void
    {
        // Find all classes with AsController attribute
        $controllerClasses = IntelligentAutoloader::findClassesWithAttribute(AsController::class);
        
        echo "🧠 Found " . count($controllerClasses) . " controller classes\n";
        
        foreach ($controllerClasses as $className) {
            try {
                $class = new ReflectionClass($className);
                self::analyzeClass($class);
            } catch (\Throwable $e) {
                echo "⚠️  Error analyzing class {$className}: " . $e->getMessage() . "\n";
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
