<?php

declare(strict_types=1);

namespace Syntexa\Frontend\Block;

use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;

class BlockContext
{
    public function __construct(
        public string $handle,
        public array $args = [],
        public ?RequestInterface $request = null
    ) {}
}

class RenderState
{
    public function __construct(
        public array $data = []
    ) {}
}

interface BlockHandlerInterface
{
    public function handle(BlockContext $context, RenderState $state): RenderState;
}


