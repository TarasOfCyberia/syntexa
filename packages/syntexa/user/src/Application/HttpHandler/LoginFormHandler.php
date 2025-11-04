<?php

namespace Syntexa\User\Application\HttpHandler;

use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\User\Application\HttpRequest\LoginFormRequest;
use Syntexa\User\Application\HttpResponse\LoginFormResponse;

#[AsHttpHandler(for: LoginFormRequest::class)]
class LoginFormHandler implements HttpHandlerInterface
{
    /**
     * @param LoginFormRequest $request
     * @param LoginFormResponse $response
     * @return LoginFormResponse
     */
    public function handle($request, $response): mixed
    {
        return $response;
    }
}