<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\View\Handler;

use Syntexa\Frontend\Attributes\AsBlockHandler;
use Syntexa\Frontend\Block\BlockHandlerInterface;
use Syntexa\Frontend\Block\BlockContext;
use Syntexa\Frontend\Block\RenderState;
use Syntexa\UserFrontend\Application\View\Block\LoginFormBlock;

#[AsBlockHandler(for: LoginFormBlock::class, priority: 100)]
class LoginFormBlockHandler implements BlockHandlerInterface
{
    public function handle(BlockContext $context, RenderState $state): RenderState
    {
        $state->data['heading'] = $state->data['heading'] ?? 'Login';
        return $state;
    }
}


