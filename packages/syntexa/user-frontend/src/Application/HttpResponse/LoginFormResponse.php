<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\HttpResponse;

use Syntexa\Core\Http\Response\GenericResponse;
use Syntexa\Core\Attributes\AsHttpResponse;
use Syntexa\Core\Http\Response\ResponseFormat;

#[AsHttpResponse(handle: 'login', format: ResponseFormat::Layout)]
class LoginFormResponse extends GenericResponse
{
}