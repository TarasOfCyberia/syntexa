<?php

declare(strict_types=1);

namespace Syntexa\Core\Http\Response;

enum ResponseFormat: string
{
    case Layout = 'layout';
    case Json = 'json';
    case Raw = 'raw';
}


