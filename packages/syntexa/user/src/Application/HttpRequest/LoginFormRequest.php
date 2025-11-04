<?php

declare(strict_types=1);

namespace Syntexa\User\Application\HttpRequest;

use Syntexa\Core\Attributes\AsHttpRequest;
use Syntexa\User\Application\HttpResponse\LoginFormResponse;

#[AsHttpRequest(path: '/login', name: 'login.form', responseWith: LoginFormResponse::class)]
class LoginFormRequest
{

}