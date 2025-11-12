<?php

declare(strict_types=1);

namespace Syntexa\Modules\UserApi\Overrides\Request;

use Syntexa\Core\Attributes\AsRequestOverride;
use Syntexa\User\Application\Request\LoginApiRequest;

#[AsRequestOverride(
    of: LoginApiRequest::class,
    priority: 100
)]
class LoginApiRequestOverride extends LoginApiRequest
{
    public ?string $email = null;
    public ?string $token = null;
    public ?string $utmSource = null;
}
