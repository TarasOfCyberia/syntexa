<?php

declare(strict_types=1);

namespace Syntexa\Frontend\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsBlockHandler
{
    public function __construct(
        private string $for,
        private int $priority = 100
    ) {}

    public function getFor(): string { return $this->for; }
    public function getPriority(): int { return $this->priority; }
}


