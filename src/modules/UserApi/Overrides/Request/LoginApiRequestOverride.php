<?php

declare(strict_types=1);

namespace Syntexa\Modules\UserApi\Overrides\Request;

use Syntexa\Core\Attributes\AsRequestOverride;
use Syntexa\User\Application\Request\LoginApiRequest;
use Syntexa\Modules\UserApi\Request\ProjectLoginApiRequest;

#[AsRequestOverride(
    of: LoginApiRequest::class,
    use: ProjectLoginApiRequest::class,
    priority: 100
)]
class LoginApiRequestOverride
{
}
