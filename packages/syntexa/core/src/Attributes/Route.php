<?php

declare(strict_types=1);

namespace Syntexa\Core\Attributes;

use Attribute;

/**
 * Marks a method as a route handler
 * 
 * This attribute defines the route path, HTTP methods, and other route options
 * for a controller method.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $path,
        public array $methods = ['GET'],
        public ?string $name = null,
        public array $requirements = [],
        public array $defaults = [],
        public array $options = []
    ) {}
}
