<?php

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

class ApiResponse extends Response
{
    public function __construct(
        int $statusCode,
        string $description,
        JsonErrorContent|JsonPaginatedContent|JsonCollectionContent|JsonContent|JsonRefContent|string|null $content,
    ) {
        $content = match (true) {
            is_string($content) || is_null($content) => new JsonRefContent($content),
            default => $content,
        };

        parent::__construct(
            response: $statusCode,
            description: $description,
            content: $content,
        );
    }
}
