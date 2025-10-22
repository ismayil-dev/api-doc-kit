<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\Enums;

use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

/**
 * String-backed enum for testing (should work without x-enum-varnames)
 */
#[Enum]
enum OrderStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
