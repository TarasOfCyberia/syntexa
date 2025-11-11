<?php

declare(strict_types=1);

namespace Syntexa\Core\Http;

/**
 * Security helper for cleaning up superglobal arrays
 * 
 * In Swoole, superglobal arrays ($_POST, $_GET, $_REQUEST) are not automatically
 * populated, but we clean them anyway for security best practices and to prevent
 * accidental use of unvalidated data.
 */
class SecurityHelper
{
    /**
     * Clear all superglobal arrays for security
     * 
     * This prevents accidental use of unvalidated data from superglobals.
     * In Swoole, these arrays are usually empty, but we clear them anyway
     * to follow security best practices.
     */
    public static function clearSuperglobals(): void
    {
        // Clear $_POST
        $_POST = [];
        
        // Clear $_GET
        $_GET = [];
        
        // Clear $_REQUEST (combination of $_GET, $_POST, $_COOKIE)
        $_REQUEST = [];
        
        // Note: We don't clear $_SERVER, $_COOKIE, $_FILES as they may be needed
        // by other parts of the application or Swoole itself.
        // $_SERVER contains important server information
        // $_COOKIE may be needed for session management
        // $_FILES is not used in Swoole (files are handled differently)
    }
    
    /**
     * Clear superglobals and optionally restore them after callback
     * 
     * @param callable $callback Function to execute with cleared superglobals
     * @return mixed Return value from callback
     */
    public static function withClearedSuperglobals(callable $callback): mixed
    {
        // Save current state (though they should be empty in Swoole)
        $originalPost = $_POST;
        $originalGet = $_GET;
        $originalRequest = $_REQUEST;
        
        try {
            // Clear superglobals
            self::clearSuperglobals();
            
            // Execute callback
            return $callback();
        } finally {
            // Restore original state (though they should be empty)
            $_POST = $originalPost;
            $_GET = $originalGet;
            $_REQUEST = $originalRequest;
        }
    }
}

