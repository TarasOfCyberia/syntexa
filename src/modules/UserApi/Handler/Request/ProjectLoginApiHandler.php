<?php

declare(strict_types=1);

namespace Syntexa\Modules\UserApi\Handler\Request;

use Syntexa\Core\Attributes\AsRequestHandler;
use Syntexa\Core\Handler\HttpHandlerInterface;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;
use Syntexa\Modules\UserApi\Overrides\Request\LoginApiRequestOverride;
use Syntexa\User\Application\Response\LoginApiResponse;

/**
 * Project-specific handler that extends module logic
 * This handler runs AFTER the module's LoginApiHandler
 */
#[AsRequestHandler(for: LoginApiRequestOverride::class)]
class ProjectLoginApiHandler implements HttpHandlerInterface
{
    /**
     * @param LoginApiRequestOverride $request
     * @param LoginApiResponse $response
     * @return LoginApiResponse
     */
    public function handle(RequestInterface $request, ResponseInterface $response): LoginApiResponse
    {
        /** @var LoginApiResponse $response */
        
        // Access extended fields from LoginApiRequestOverride
        if ($request->email) {
            // Custom project logic here
            if (method_exists($response, 'setRenderContext')) {
                $context = method_exists($response, 'getRenderContext') ? $response->getRenderContext() : [];
                $context['email'] = $request->email;
                $context['utmSource'] = $request->utmSource ?? 'unknown';
                $response->setRenderContext($context);
            }
        }
        
        return $response;
    }
}

