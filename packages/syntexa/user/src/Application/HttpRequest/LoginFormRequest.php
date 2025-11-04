<?php

declare(strict_types=1);

namespace Syntexa\User\Application\HttpRequest;

use Syntexa\Core\Attributes\AsHttpRequest;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\User\Application\HttpResponse\LoginFormResponse;

#[AsHttpRequest(path: '/login', name: 'login.form', responseWith: LoginFormResponse::class)]
class LoginFormRequest implements RequestInterface
{

}