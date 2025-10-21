<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Http\Responses;

use Illuminate\Http\Response;
use IsmayilDev\ApiDocKit\Attributes\Responses\ApiResponse;
use IsmayilDev\ApiDocKit\Attributes\Responses\JsonCollectionContent;
use IsmayilDev\ApiDocKit\Attributes\Responses\JsonErrorContent;
use IsmayilDev\ApiDocKit\Attributes\Responses\JsonPaginatedContent;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CreatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\EmptyResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\PaginatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\UpdatedResponse;
use OpenApi\Attributes\MediaType;

class ResponseSchemaBuilder
{
    public function __construct() {}

    /**
     * Build a success response with optional custom schema override
     *
     * @param  string  $responseType  The response contract class
     * @param  string  $responseRef  The schema reference
     * @param  MediaType|string|null  $customSchema  Custom schema override from attribute
     */
    public function buildSuccessResponse(
        string $responseType,
        string $responseRef,
        MediaType|string|null $customSchema = null
    ): ?ApiResponse {
        // If custom schema provided via attribute, use it
        if ($customSchema !== null) {
            return $this->buildCustomSuccessResponse($responseType, $customSchema);
        }

        // Check config for global overrides
        $configOverride = $this->getConfigSuccessOverride($responseType);

        if ($configOverride !== null) {
            return $this->buildCustomSuccessResponse($responseType, $configOverride);
        }

        // Use default implementations
        return $this->buildDefaultSuccessResponse($responseType, $responseRef);
    }

    /**
     * Build an error response with optional custom schema override
     *
     * @param  string|null  $customDescription  Custom description from attribute
     * @param  MediaType|string|null  $customSchema  Custom schema from attribute
     */
    public function buildErrorResponse(
        int $statusCode,
        ?string $customDescription = null,
        MediaType|string|null $customSchema = null
    ): ApiResponse {
        $description = $customDescription
            ?? $this->getConfigErrorDescription($statusCode)
            ?? $this->getDefaultErrorDescription($statusCode);

        $schema = $customSchema
            ?? $this->getConfigErrorSchema($statusCode)
            ?? $this->getConfigGlobalErrorSchema()
            ?? new JsonErrorContent;

        return new ApiResponse(
            statusCode: $statusCode,
            description: $description,
            content: $schema
        );
    }

    /**
     * Get default success response implementation
     */
    protected function buildDefaultSuccessResponse(string $responseType, string $responseRef): ?ApiResponse
    {
        return match ($responseType) {
            CreatedResponse::class => new ApiResponse(
                Response::HTTP_CREATED,
                'Created response',
                $responseRef
            ),
            CollectionResponse::class => new ApiResponse(
                statusCode: Response::HTTP_OK,
                description: 'Collection response',
                content: new JsonCollectionContent($responseRef)
            ),
            PaginatedResponse::class => new ApiResponse(
                statusCode: Response::HTTP_OK,
                description: 'Paginated response',
                content: new JsonPaginatedContent($responseRef)
            ),
            EmptyResponse::class => new ApiResponse(
                Response::HTTP_NO_CONTENT,
                'Empty response',
                null
            ),
            SingleResourceResponse::class => new ApiResponse(
                Response::HTTP_OK,
                'Single resource response',
                $responseRef
            ),
            UpdatedResponse::class => new ApiResponse(
                Response::HTTP_OK,
                'Updated response',
                $responseRef
            ),
            default => null,
        };
    }

    /**
     * Build custom success response using provided schema
     */
    protected function buildCustomSuccessResponse(string $responseType, MediaType|string $customSchema): ApiResponse
    {
        $statusCode = $this->getStatusCodeForResponseType($responseType);
        $description = $this->getDescriptionForResponseType($responseType);

        // If schema is a class name string, instantiate it
        if (is_string($customSchema) && class_exists($customSchema)) {
            $customSchema = new $customSchema;
        }

        return new ApiResponse(
            statusCode: $statusCode,
            description: $description,
            content: $customSchema
        );
    }

    /**
     * Get config override for success response type
     */
    protected function getConfigSuccessOverride(string $responseType): ?string
    {
        $key = match ($responseType) {
            SingleResourceResponse::class => 'single',
            CollectionResponse::class => 'collection',
            PaginatedResponse::class => 'paginated',
            CreatedResponse::class => 'created',
            UpdatedResponse::class => 'updated',
            EmptyResponse::class => 'empty',
            default => null,
        };

        if ($key === null) {
            return null;
        }

        return config("api-doc-kit.responses.success.{$key}");
    }

    /**
     * Get config override for error schema (per-status)
     */
    protected function getConfigErrorSchema(int $statusCode): ?string
    {
        return config("api-doc-kit.responses.error.per_status.{$statusCode}");
    }

    /**
     * Get config global error schema override
     */
    protected function getConfigGlobalErrorSchema(): ?string
    {
        return config('api-doc-kit.responses.error.schema');
    }

    /**
     * Get config custom description for error status code
     */
    protected function getConfigErrorDescription(int $statusCode): ?string
    {
        return config("api-doc-kit.responses.error.descriptions.{$statusCode}");
    }

    /**
     * Get default error description
     */
    protected function getDefaultErrorDescription(int $statusCode): string
    {
        return match ($statusCode) {
            Response::HTTP_BAD_REQUEST => 'Bad request',
            Response::HTTP_UNAUTHORIZED => 'Unauthorized',
            Response::HTTP_FORBIDDEN => 'Forbidden',
            Response::HTTP_NOT_FOUND => 'Not found',
            Response::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'Validation failed',
            Response::HTTP_TOO_MANY_REQUESTS => 'Too many requests',
            Response::HTTP_INTERNAL_SERVER_ERROR => 'Internal server error',
            default => "Error {$statusCode}",
        };
    }

    /**
     * Get HTTP status code for response type
     */
    protected function getStatusCodeForResponseType(string $responseType): int
    {
        return match ($responseType) {
            CreatedResponse::class => Response::HTTP_CREATED,
            EmptyResponse::class => Response::HTTP_NO_CONTENT,
            default => Response::HTTP_OK,
        };
    }

    /**
     * Get description for response type
     */
    protected function getDescriptionForResponseType(string $responseType): string
    {
        return match ($responseType) {
            CreatedResponse::class => 'Created response',
            CollectionResponse::class => 'Collection response',
            PaginatedResponse::class => 'Paginated response',
            EmptyResponse::class => 'Empty response',
            SingleResourceResponse::class => 'Single resource response',
            UpdatedResponse::class => 'Updated response',
            default => 'Success',
        };
    }
}
