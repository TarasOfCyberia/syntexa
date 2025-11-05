<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\HttpResponse;

use Syntexa\Frontend\Http\Response\HtmlResponse;
use Syntexa\Frontend\Attributes\AsHttpResponse;

#[AsHttpResponse(handle: 'login', context: ['title' => 'Login'])]
class LoginFormResponse extends HtmlResponse
{
}