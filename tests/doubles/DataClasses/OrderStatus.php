<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

#[Enum]
enum OrderStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
