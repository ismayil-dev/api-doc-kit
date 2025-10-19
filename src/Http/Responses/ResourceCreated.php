<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CreatedResponse;

class ResourceCreated extends BaseResponse implements CreatedResponse
{
    protected function getStatus(): int
    {
        return self::HTTP_CREATED;
    }
}
