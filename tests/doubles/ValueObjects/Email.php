<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\ValueObjects;

use InvalidArgumentException;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;

#[DataSchema]
final readonly class Email
{
    public function __construct(
        private string $email
    ) {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: {$email}");
        }
    }

    public function getValue(): string
    {
        return $this->email;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
