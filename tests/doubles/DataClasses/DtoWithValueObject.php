<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Illuminate\Contracts\Support\Arrayable;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;
use IsmayilDev\ApiDocKit\Tests\Doubles\ValueObjects\Email;

#[DataSchema]
class DtoWithValueObject implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly Email $recipient,
        public readonly string $subject,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'recipient' => $this->recipient->getValue(),
            'subject' => $this->subject,
        ];
    }
}
