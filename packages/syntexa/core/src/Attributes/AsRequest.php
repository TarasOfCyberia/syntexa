<?php

declare(strict_types=1);

namespace Syntexa\Core\Attributes;

use Attribute;

/**
 * Marks a class as a request DTO with route information
 * 
 * This attribute tells Syntexa that this class should be treated as a request
 * and defines the route path, methods, and other options.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsRequest
{
    public function __construct(
        public string $path,
        public array $methods = ['GET'],
        public ?string $name = null,
        public array $requirements = [],
        public array $defaults = [],
        public array $options = [],
        public array $tags = [],
        public bool $public = true,
        public string $responseWith = '',
    ) {}
}
