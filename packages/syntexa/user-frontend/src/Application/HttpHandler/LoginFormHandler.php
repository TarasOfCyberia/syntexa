<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\HttpHandler;

use Syntexa\Core\Attributes\AsHttpHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;
use Syntexa\UserFrontend\Application\HttpRequest\LoginFormRequest;
use Syntexa\UserFrontend\Application\HttpResponse\LoginFormResponse;
use Syntexa\Frontend\Layout\LayoutRenderer;

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
        /** @var LoginFormResponse $response */
        $html = LayoutRenderer::renderHandle('login', [
            'title' => 'Login'
        ]);
        if ($html === '' || $html === null) {
            $html = '<!doctype html><html><head><meta charset="utf-8"><title>Login</title></head><body><main><h1>Login</h1></main></body></html>';
        }
        $response->setContent($html)->setHeader('Content-Type', 'text/html; charset=UTF-8');
        return $response;
    }
}


