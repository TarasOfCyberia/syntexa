<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\HttpResponse;

use Syntexa\Core\Http\Response\GenericResponse;
use Syntexa\Core\Attributes\AsHttpResponse;

#[AsHttpResponse(handle: 'login')]
class LoginFormResponse extends GenericResponse
{
}