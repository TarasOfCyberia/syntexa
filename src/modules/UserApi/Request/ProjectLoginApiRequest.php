<?php

declare(strict_types=1);

namespace Syntexa\Modules\UserApi\Request;

use Syntexa\User\Application\Request\LoginApiRequest as BaseLoginApiRequest;

class ProjectLoginApiRequest extends BaseLoginApiRequest
{
    public ?string $email = null;
    public ?string $token = null;
    public ?string $utmSource = null;
}
