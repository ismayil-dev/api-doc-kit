<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use Illuminate\Http\JsonResponse;

abstract class BaseResponse extends JsonResponse
{
    public function __construct(
        mixed $item,
    ) {

        parent::__construct(
            data: $item,
            status: $this->getStatus(),
        );
    }

    abstract protected function getStatus(): int;
}
