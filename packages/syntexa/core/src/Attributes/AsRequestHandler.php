<?php

namespace Syntexa\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsRequestHandler
{
    public function __construct(
        private string $for = ''
    ) {
    }

    public function getFor(): string
    {
        return $this->for;
    }
}