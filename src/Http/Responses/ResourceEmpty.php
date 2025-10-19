<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use Illuminate\Http\JsonResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\EmptyResponse;

class ResourceEmpty extends JsonResponse implements EmptyResponse
{
    public function __construct()
    {
        parent::__construct(
            status: self::HTTP_NO_CONTENT,
        );
    }
}
