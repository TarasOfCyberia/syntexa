<?php

declare(strict_types=1);

namespace Syntexa\User\Application\State\Response;

use Syntexa\Core\Http\Response\GenericResponse;
use Syntexa\Core\Attributes\AsHttpResponse;
use Syntexa\Core\Http\Response\ResponseFormat;

#[AsHttpResponse(handle: 'api.login', format: ResponseFormat::Json)]
class LoginApiStateResponse extends GenericResponse
{
}


