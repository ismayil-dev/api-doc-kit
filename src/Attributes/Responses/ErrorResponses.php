<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ErrorResponses
{
    /**
     * @param  array<int>|null  $only  Include only these error status codes
     * @param  array<int>|null  $except  Exclude these error status codes from defaults
     */
    public function __construct(
        private readonly ?array $only = null,
        private readonly ?array $except = null,
    ) {}

    public function getOnly(): ?array
    {
        return $this->only;
    }

    public function getExcept(): ?array
    {
        return $this->except;
    }

    public function hasOnly(): bool
    {
        return $this->only !== null;
    }

    public function hasExcept(): bool
    {
        return $this->except !== null;
    }

    /**
     * Filter error status codes based on only/except rules
     *
     * @param  array<int>  $defaultCodes
     * @return array<int>
     */
    public function filter(array $defaultCodes): array
    {
        if ($this->hasOnly()) {
            return $this->only;
        }

        if ($this->hasExcept()) {
            return array_diff($defaultCodes, $this->except);
        }

        return $defaultCodes;
    }
}
