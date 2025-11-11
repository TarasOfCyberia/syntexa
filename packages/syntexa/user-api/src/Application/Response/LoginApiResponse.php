<?php

declare(strict_types=1);

namespace Syntexa\User\Application\Response;

use Syntexa\Core\Http\Response\GenericResponse;
use Syntexa\Core\Attributes\AsResponse;
use Syntexa\Core\Http\Response\ResponseFormat;

#[AsResponse(handle: 'api.login', format: ResponseFormat::Json)]
class LoginApiResponse extends GenericResponse
{
}

