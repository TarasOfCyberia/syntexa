<?php

declare(strict_types=1);

namespace Syntexa\Core;

/**
 * Request Factory for creating Request objects from different sources
 */
class RequestFactory
{
    /**
     * Create Request from any source (auto-detection)
     */
    public static function create(mixed $source = null): Request
    {
        if ($source instanceof \Swoole\Http\Request) {
            return self::fromSwoole($source);
        }
        
        return self::fromGlobals();
    }
    
    /**
     * Create Request from PHP globals
     */
    public static function fromGlobals(): Request
    {
        return new Request(
            method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
            uri: $_SERVER['REQUEST_URI'] ?? '/',
            headers: self::getHeaders(),
            query: $_GET,
            post: $_POST,
            server: $_SERVER,
            cookies: $_COOKIE,
            content: file_get_contents('php://input') ?: null
        );
    }
    
    /**
     * Create Request from Swoole request object
     */
    public static function fromSwoole(\Swoole\Http\Request $swooleRequest): Request
    {
        return new Request(
            method: $swooleRequest->server['request_method'] ?? 'GET',
            uri: $swooleRequest->server['request_uri'] ?? '/',
            headers: $swooleRequest->header ?? [],
            query: $swooleRequest->get ?? [],
            post: $swooleRequest->post ?? [],
            server: array_merge($swooleRequest->server ?? [], ['SWOOLE_SERVER' => '1']),
            cookies: $swooleRequest->cookie ?? [],
            content: $swooleRequest->getContent() ?: null
        );
    }
    
    /**
     * Create Request from array data
     */
    public static function fromArray(array $data): Request
    {
        return new Request(
            method: $data['method'] ?? 'GET',
            uri: $data['uri'] ?? '/',
            headers: $data['headers'] ?? [],
            query: $data['query'] ?? [],
            post: $data['post'] ?? [],
            server: $data['server'] ?? [],
            cookies: $data['cookies'] ?? [],
            content: $data['content'] ?? null
        );
    }
    
    private static function getHeaders(): array
    {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for servers without getallheaders()
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $headerName = str_replace('_', '-', substr($key, 5));
                    $headers[$headerName] = $value;
                }
            }
        }
        
        return $headers;
    }
}
