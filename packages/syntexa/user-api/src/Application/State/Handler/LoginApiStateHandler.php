<?php

declare(strict_types=1);

namespace Syntexa\User\Application\State\Handler;

use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;
use Syntexa\User\Application\State\Processor\LoginApiStateProcessor;
use Syntexa\User\Application\State\Response\LoginApiStateResponse;

#[AsHttpHandler(for: LoginApiStateProcessor::class)]
class LoginApiStateHandler implements HttpHandlerInterface
{
    /**
     * @param LoginApiStateProcessor $request
     * @param LoginApiStateResponse $response
     * @return LoginApiStateResponse
     */
    public function handle(RequestInterface $request, ResponseInterface $response): LoginApiStateResponse
    {
        // demo payload
        if (method_exists($response, 'setRenderContext')) {
            $response->setRenderContext([
                'ok' => true,
                'message' => 'Login API reached'
            ]);
        }
        return $response;
    }
}


