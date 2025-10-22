<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CreatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\EmptyResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\PaginatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;

class ApiResponse
{
    public static function paginated(mixed $resource): PaginatedResponse
    {
        return new PaginatedResource($resource);
    }

    public static function created(mixed $item): CreatedResponse
    {
        return new ResourceCreated($item);
    }

    public static function empty(): EmptyResponse
    {
        return new ResourceEmpty;
    }

    public static function resource(mixed $item): SingleResourceResponse
    {
        return new ResourceResponse($item);
    }

    public static function collection(mixed $items): CollectionResponse
    {
        return new ResourceCollection($items);
    }

    public static function deleted(): EmptyResponse
    {
        return new ResourceEmpty;
    }
}
