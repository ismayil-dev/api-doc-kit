<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Enums;

use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CreatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\EmptyResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;
use IsmayilDev\ApiDocKit\Http\Responses\PaginatedResource;

enum HttpResponseSchema: string
{
    case SingleResource = SingleResourceResponse::class;
    case CollectionResponse = CollectionResponse::class;
    case PaginatedResource = PaginatedResource::class;
    case CreatedResponse = CreatedResponse::class;
    case EmptyResponse = EmptyResponse::class;
}
