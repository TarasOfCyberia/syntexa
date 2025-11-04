<?php

namespace Syntexa\Core\Handler;

use Syntexa\User\Interface\HttpRequest\LoginFormRequest;

interface HttpHandlerInterface
{
    public function handle(mixed $request): mixed;
}