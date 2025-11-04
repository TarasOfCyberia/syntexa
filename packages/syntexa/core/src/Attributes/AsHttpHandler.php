<?php

namespace Syntexa\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsHttpHandler
{
    public function __construct(
        private string $for = ''
    ) {
    }
}