<?php

declare(strict_types=1);

namespace Syntexa\Core\Http;

use Syntexa\Core\Request;
use ReflectionClass;
use ReflectionProperty;

/**
 * Hydrates Request DTO objects from HTTP Request data
 * 
 * Automatically populates public properties of Request DTOs from:
 * - JSON body (if Content-Type is application/json)
 * - POST data (form-data, x-www-form-urlencoded)
 * - Query parameters
 */
class RequestDtoHydrator
{
    /**
     * Hydrate a Request DTO from HTTP Request
     * 
     * @param object $dto The Request DTO instance to hydrate
     * @param Request $httpRequest The HTTP Request containing data
     * @return object The hydrated DTO
     */
    public static function hydrate(object $dto, Request $httpRequest): object
    {
        $reflection = new ReflectionClass($dto);
        
        // Collect data from all sources
        $data = self::collectData($httpRequest);
        
        // Hydrate each public property
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->getName();
            
            // Skip if property is not initialized or has a value
            if ($property->isInitialized($dto)) {
                continue;
            }
            
            // Get value from collected data
            $value = $data[$propertyName] ?? null;
            
            if ($value !== null) {
                // Get property type for type casting
                $type = $property->getType();
                $typedValue = self::castValue($value, $type);
                
                // Set property value
                $property->setValue($dto, $typedValue);
            }
        }
        
        return $dto;
    }
    
    /**
     * Collect data from HTTP Request (JSON body, POST, query)
     * 
     * Priority order:
     * 1. JSON body (if Content-Type: application/json) - highest priority
     * 2. POST data (form-urlencoded, multipart/form-data) - parsed by Swoole
     * 3. Query parameters - lowest priority
     */
    private static function collectData(Request $httpRequest): array
    {
        $data = [];
        
        // 1. Parse JSON body if Content-Type is application/json (highest priority)
        // Swoole doesn't populate ->post for JSON requests, so we parse from raw content
        if ($httpRequest->isJson()) {
            $jsonData = $httpRequest->getJsonBody();
            if ($jsonData !== null) {
                $data = array_merge($data, $jsonData);
            }
        } else {
            // 2. Add POST data (form-urlencoded, multipart/form-data)
            // Swoole automatically parses these into ->post array
            $data = array_merge($data, $httpRequest->post);
        }
        
        // 3. Add query parameters (lowest priority - only if not already set)
        foreach ($httpRequest->query as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
    
    /**
     * Cast value to appropriate type based on property type
     */
    private static function castValue(mixed $value, ?\ReflectionType $type): mixed
    {
        if ($type === null) {
            return $value;
        }
        
        // Handle union types (e.g., int|string|null)
        if ($type instanceof \ReflectionUnionType) {
            $types = $type->getTypes();
            // Try to cast to first non-null type
            foreach ($types as $t) {
                if ($t->getName() !== 'null') {
                    return self::castToType($value, $t->getName());
                }
            }
            return $value;
        }
        
        // Handle nullable types (e.g., ?int)
        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            
            // Handle nullable
            if ($type->allowsNull() && ($value === null || $value === '')) {
                return null;
            }
            
            return self::castToType($value, $typeName);
        }
        
        return $value;
    }
    
    /**
     * Cast value to specific type
     */
    private static function castToType(mixed $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return match ($type) {
                'int', 'float' => 0,
                'bool' => false,
                'string' => '',
                'array' => [],
                default => null,
            };
        }
        
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => self::castToBool($value),
            'string' => (string) $value,
            'array' => is_array($value) ? $value : [$value],
            default => $value,
        };
    }
    
    /**
     * Cast value to boolean
     */
    private static function castToBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $lower = strtolower($value);
            return in_array($lower, ['1', 'true', 'yes', 'on'], true);
        }
        
        return (bool) $value;
    }
}

