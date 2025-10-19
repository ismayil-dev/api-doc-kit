<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;

class ResourceResponse extends BaseResponse implements SingleResourceResponse
{
    protected function getStatus(): int
    {
        return self::HTTP_OK;
    }
}
