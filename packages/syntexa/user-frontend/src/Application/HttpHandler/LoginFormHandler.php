<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\HttpHandler;

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
        // Rendering HTML here; could be switched to Twig HtmlResponse later
        $response->setHeader('Content-Type', 'text/html')
                 ->setContent('<!doctype html><html><head><title>Login</title></head><body><h1>Login</h1><form method="POST" action="/login"><input type="email" name="email" placeholder="Email"/><br/><input type="password" name="password" placeholder="Password"/><br/><button type="submit">Sign in</button></form></body></html>');
        return $response;
    }
}


