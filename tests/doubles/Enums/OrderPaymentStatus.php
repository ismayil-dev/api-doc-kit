<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\Enums;

use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

/**
 * Integer-backed enum for testing x-enum-varnames generation
 */
#[Enum]
enum OrderPaymentStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Refunded = 2;
    case Cancelled = 3;
}
