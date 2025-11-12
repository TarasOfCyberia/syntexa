<?php

declare(strict_types=1);

namespace Syntexa\Core\Attributes;

use Attribute;

/**
 * Declares an override for an existing AsRequest-decorated class.
 * Intended to live in the project's src/ and adjust routing/config
 * without YAML or duplicating classes.
 * 
 * If the override class extends the target class (specified in 'of'),
 * and 'use' is not provided, the system will automatically detect
 * the class replacement. This allows you to simply extend the base
 * class and add fields without explicitly specifying 'use'.
 * 
 * You can use environment variable references in any attribute value:
 * - `env::VAR_NAME` - reads from .env file, returns empty string if not set
 * - `env::VAR_NAME::default_value` - reads from .env file, returns default if not set (recommended)
 * - `env::VAR_NAME:default_value` - legacy format, also supported for backward compatibility
 * 
 * Example:
 * ```php
 * #[AsRequestOverride(
 *     of: LoginApiRequest::class,
 *     path: 'env::API_LOGIN_PATH::/api/login',
 *     name: 'env::API_LOGIN_ROUTE_NAME::api.login'
 * )]
 * ```
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


