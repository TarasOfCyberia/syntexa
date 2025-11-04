<?php

namespace Syntexa\Core\Handler;

interface HttpHandlerInterface
{
    public function handle($request, $response): mixed;
}