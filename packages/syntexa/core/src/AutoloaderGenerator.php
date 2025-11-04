<?php

declare(strict_types=1);

namespace Syntexa\Core;

/**
 * Automatic PSR-4 autoloader generator
 * 
 * This class automatically generates PSR-4 mappings based on:
 * - Existing vendor/syntexa/* packages
 * - src/modules/* directories
 * - src/packages/* directories
 * - vendor/* packages with type: "syntexa-module"
 */
class AutoloaderGenerator
{
    /**
     * Generate PSR-4 autoloader configuration
     */
    public static function generate(): array
    {
        $mappings = [];
        
        // 1. Scan vendor/syntexa/* packages
        $mappings = array_merge($mappings, self::scanVendorSyntexaPackages());
        
        // 2. Scan src/modules/* directories
        $mappings = array_merge($mappings, self::scanLocalModules());
        
        // 3. Scan src/packages/* directories
        $mappings = array_merge($mappings, self::scanLocalPackages());
        
        // 4. Scan vendor/* for syntexa-module type packages
        $mappings = array_merge($mappings, self::scanVendorSyntexaModules());
        
        return $mappings;
    }
    
    /**
     * Scan vendor/syntexa/* packages
     */
    private static function scanVendorSyntexaPackages(): array
    {
        $mappings = [];
        $vendorPath = self::getProjectRoot() . '/vendor/syntexa';
        
        if (!is_dir($vendorPath)) {
            return $mappings;
        }
        
        $packages = glob($vendorPath . '/*', GLOB_ONLYDIR);
        
        foreach ($packages as $package) {
            $packageName = basename($package);
            $namespace = "Syntexa\\" . ucfirst($packageName) . "\\";
            
            // Check if package has src/ directory
            if (is_dir($package . '/src')) {
                $mappings[$namespace] = "vendor/syntexa/{$packageName}/src/";
            } else {
                $mappings[$namespace] = "vendor/syntexa/{$packageName}/";
            }
        }
        
        return $mappings;
    }
    
    /**
     * Scan src/modules/* directories
     */
    private static function scanLocalModules(): array
    {
        $mappings = [];
        $modulesPath = self::getProjectRoot() . '/src/modules';
        
        if (!is_dir($modulesPath)) {
            return $mappings;
        }
        
        $modules = glob($modulesPath . '/*', GLOB_ONLYDIR);
        
        foreach ($modules as $module) {
            $moduleName = basename($module);
            $namespace = "Syntexa\\Modules\\" . ucfirst($moduleName) . "\\";
            $mappings[$namespace] = "src/modules/{$moduleName}/";
        }
        
        return $mappings;
    }
    
    /**
     * Scan src/packages/* directories
     */
    private static function scanLocalPackages(): array
    {
        $mappings = [];
        $packagesPath = self::getProjectRoot() . '/src/packages';
        
        if (!is_dir($packagesPath)) {
            return $mappings;
        }
        
        $packages = glob($packagesPath . '/*', GLOB_ONLYDIR);
        
        foreach ($packages as $package) {
            $packageName = basename($package);
            $namespace = "Syntexa\\Packages\\" . ucfirst($packageName) . "\\";
            $mappings[$namespace] = "src/packages/{$packageName}/";
        }
        
        return $mappings;
    }
    
    /**
     * Scan vendor/* for syntexa-module type packages
     */
    private static function scanVendorSyntexaModules(): array
    {
        $mappings = [];
        $vendorPath = self::getProjectRoot() . '/vendor';
        
        if (!is_dir($vendorPath)) {
            return $mappings;
        }
        
        // Scan all vendor packages
        $vendorDirs = glob($vendorPath . '/*', GLOB_ONLYDIR);
        
        foreach ($vendorDirs as $vendorDir) {
            $vendorName = basename($vendorDir);
            $packages = glob($vendorDir . '/*', GLOB_ONLYDIR);
            
            foreach ($packages as $package) {
                $packageName = basename($package);
                $composerJson = $package . '/composer.json';
                
                if (file_exists($composerJson)) {
                    $config = json_decode(file_get_contents($composerJson), true);
                    
                    // Check if it's a syntexa-module
                    if (isset($config['type']) && $config['type'] === 'syntexa-module') {
                        // Extract namespace from autoload
                        if (isset($config['autoload']['psr-4'])) {
                            foreach ($config['autoload']['psr-4'] as $namespace => $path) {
                                $mappings[$namespace] = "vendor/{$vendorName}/{$packageName}/{$path}";
                            }
                        }
                    }
                }
            }
        }
        
        return $mappings;
    }
    
    /**
     * Get project root directory
     */
    private static function getProjectRoot(): string
    {
        $currentDir = __DIR__;
        
        // Go up from vendor/syntexa/core/src/ to project root
        return dirname($currentDir, 4);
    }
    
    /**
     * Update composer.json with generated mappings
     */
    public static function updateComposerJson(): void
    {
        $mappings = self::generate();
        $composerJsonPath = self::getProjectRoot() . '/composer.json';
        
        if (!file_exists($composerJsonPath)) {
            throw new \RuntimeException('composer.json not found');
        }
        
        $composer = json_decode(file_get_contents($composerJsonPath), true);
        
        // Update autoload section
        $composer['autoload']['psr-4'] = $mappings;
        
        // Write back to file
        file_put_contents(
            $composerJsonPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        echo "✅ Updated composer.json with " . count($mappings) . " PSR-4 mappings\n";
    }
}
