<?php

namespace Syntexa\User\Application\HttpHandler;

use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;
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
    public function handle(RequestInterface $request, ResponseInterface $response): LoginFormResponse
    {
        return $response;
    }
}