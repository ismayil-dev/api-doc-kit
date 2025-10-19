<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use IsmayilDev\ApiDocKit\Http\Responses\Contracts\UpdatedResponse;

class ResourceUpdated extends BaseResponse implements UpdatedResponse
{
    protected function getStatus(): int
    {
        return self::HTTP_OK;
    }
}
