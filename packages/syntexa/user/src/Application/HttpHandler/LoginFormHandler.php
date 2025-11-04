<?php

namespace Syntexa\User\Application\HttpHandler;

use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\User\Application\HttpRequest\LoginFormRequest;

#[AsHttpHandler(for: LoginFormRequest::class)]
class LoginFormHandler implements HttpHandlerInterface
{
    public function handle(mixed $request): mixed
    {
        return '';
    }
}