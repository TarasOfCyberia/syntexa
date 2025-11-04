<?php

namespace Syntexa\Core\Handler;

use Syntexa\Core\Contract\RequestInterface;
use Syntexa\Core\Contract\ResponseInterface;

interface HttpHandlerInterface
{
    public function handle(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}