<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;

class ResourceCollection extends BaseResponse implements CollectionResponse
{
    protected function getStatus(): int
    {
        return self::HTTP_OK;
    }
}
