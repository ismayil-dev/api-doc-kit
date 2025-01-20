<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyType;
use IsmayilDev\LaravelDocKit\Attributes\Properties\NumberProperty;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    title: 'Error',
    description: 'Error schema',
    required: ['statusCode', 'messages'],
    properties: [
        new NumberProperty(description: 'Status Code', property: 'statusCode'),
        new Property(
            property: 'messages',
            description: 'List of error messages',
            type: OpenApiPropertyType::ARRAY->value,
            items: new Items(type: 'string'),
        ),
        new Property(property: 'exception', description: 'Exception', type: OpenApiPropertyType::OBJECT->value),
    ],
)]
class ErrorResponse extends JsonResponse
{
    public function __construct(
        Throwable $exception,
        string|array|null $message = null,
        array $validationErrors = [],
        bool $showMessage = true,
        int $status = self::HTTP_BAD_REQUEST,
    ) {
        // Error message should always be an array, defaulting to the message on the exception
        $message = $showMessage ? Arr::wrap($message ?? $exception->getMessage()) : [];

        $data = [
            'statusCode' => $status,
            'messages' => $message,
        ];

        if ($status === self::HTTP_UNPROCESSABLE_ENTITY) {
            $data['validationErrors'] = $validationErrors;
        }

        if (config('app.debug')) {
            // In debug mode, we'll list out all the previous exceptions

            $data['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'previous' => [],
            ];

            $previous = $exception;

            while (($previous = $previous->getPrevious()) !== null) {
                $data['exception']['previous'][] = [
                    'class' => get_class($exception),
                    'message' => $previous->getMessage(),
                    'file' => $previous->getFile(),
                    'line' => $previous->getLine(),
                ];
            }
        }

        parent::__construct($data, $status);
    }
}
