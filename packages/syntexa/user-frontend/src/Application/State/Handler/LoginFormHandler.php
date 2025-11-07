<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\State\Handler;

use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;
use Syntexa\UserFrontend\Application\State\Provider\LoginFormProvider;
use Syntexa\UserFrontend\Application\State\Response\LoginFormStateResponse;

#[AsHttpHandler(for: LoginFormProvider::class)]
class LoginFormHandler implements HttpHandlerInterface
{
    /**
     * @param LoginFormProvider $request
     * @param LoginFormStateResponse $response
     * @return LoginFormStateResponse
     */
    public function handle(RequestInterface $request, ResponseInterface $response): LoginFormStateResponse
    {
        /** @var LoginFormStateResponse $response */
        // Defaults are defined in LoginFormResponse; handlers may override if needed
        return $response;
    }
}


