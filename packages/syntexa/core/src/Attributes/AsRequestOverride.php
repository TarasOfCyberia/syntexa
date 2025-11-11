<?php

declare(strict_types=1);

namespace Syntexa\Core\Attributes;

use Attribute;

/**
 * Declares an override for an existing AsRequest-decorated class.
 * Intended to live in the project's src/ and adjust routing/config
 * without YAML or duplicating classes.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsRequestOverride
{
    public function __construct(
        public string $of,
        public ?string $use = null,
        public ?string $path = null,
        public ?array $methods = null,
        public ?string $name = null,
        public ?string $responseWith = null,
        public ?array $requirements = null,
        public ?array $defaults = null,
        public ?array $options = null,
        public ?array $tags = null,
        public ?bool $public = null,
        public int $priority = 0
    ) {}
}


