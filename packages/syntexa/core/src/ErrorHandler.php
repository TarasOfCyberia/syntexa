<?php

declare(strict_types=1);

namespace Syntexa\Core;

/**
 * Centralized error handling configuration
 */
class ErrorHandler
{
    public static function configure(Environment $environment): void
    {
        error_reporting(E_ALL);
        
        if ($environment->isDev()) {
            // Development mode - show all errors
            ini_set('display_errors', '1');
            ini_set('log_errors', '1');
        } else {
            // Production mode - hide errors
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', self::getLogPath());
        }
    }
    
    private static function getLogPath(): string
    {
        // Try to find the project root
        $root = self::findProjectRoot();
        return $root . '/var/log/error.log';
    }
    
    private static function findProjectRoot(): string
    {
        // Start from current directory and go up until we find composer.json
        $dir = __DIR__;
        
        while ($dir !== '/' && $dir !== '') {
            if (file_exists($dir . '/composer.json')) {
                return $dir;
            }
            $dir = dirname($dir);
        }
        
        // Fallback to current directory
        return __DIR__;
    }
}
