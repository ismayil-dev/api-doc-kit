<?php

namespace IsmayilDev\ApiDocKit\Enums;

use OpenApi\Attributes\Schema;

#[Schema(type: 'string')]
enum OpenApiPropertyFormat: string
{
    case DATE_TIME = 'date-time';
    case DATE = 'date';
    case TIME = 'time';
}
