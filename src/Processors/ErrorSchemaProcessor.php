<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Processors;

use OpenApi\Analysis;
use OpenApi\Attributes\AdditionalProperties;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use OpenApi\Context;
use OpenApi\Generator;

/**
 * Registers shared error schemas in components/schemas/ so error responses
 * use $ref instead of inlining the same schema on every endpoint.
 *
 * Registers two schemas by default (matching Laravel's error format):
 * - ErrorSchema: {message: string} — for 400, 401, 403, 500
 * - ValidationErrorSchema: {message: string, errors: {field: string[]}} — for 422
 *
 * Both schema names are configurable via api-doc-kit.responses.error.schema_names
 */
readonly class ErrorSchemaProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        $defaultName = config('api-doc-kit.responses.error.schema_names.default', 'ErrorSchema');
        $validationName = config('api-doc-kit.responses.error.schema_names.422', 'ValidationErrorSchema');

        $this->registerErrorSchema($analysis, $defaultName);
        $this->registerValidationErrorSchema($analysis, $validationName);
    }

    private function registerErrorSchema(Analysis $analysis, string $name): void
    {
        // Check if already registered (e.g., by user's own #[Schema] attribute)
        foreach ($analysis->openapi->components->schemas ?? [] as $schema) {
            if ($schema instanceof Schema && $schema->schema === $name) {
                return;
            }
        }

        $schema = new Schema(
            schema: $name,
            title: $name,
            description: 'Error response',
            required: ['message'],
            properties: [
                new Property(
                    property: 'message',
                    type: 'string',
                    example: 'Error message',
                ),
            ],
            type: 'object',
        );
        $schema->_context = new Context(['generated' => true]);

        if ($analysis->openapi->components === Generator::UNDEFINED) {
            $analysis->openapi->components = new \OpenApi\Attributes\Components();
        }
        if ($analysis->openapi->components->schemas === Generator::UNDEFINED) {
            $analysis->openapi->components->schemas = [];
        }

        $analysis->openapi->components->schemas[] = $schema;
    }

    private function registerValidationErrorSchema(Analysis $analysis, string $name): void
    {
        foreach ($analysis->openapi->components->schemas ?? [] as $schema) {
            if ($schema instanceof Schema && $schema->schema === $name) {
                return;
            }
        }

        $schema = new Schema(
            schema: $name,
            title: $name,
            description: 'Validation error response',
            required: ['message', 'errors'],
            properties: [
                new Property(
                    property: 'message',
                    type: 'string',
                    example: 'The given data was invalid.',
                ),
                new Property(
                    property: 'errors',
                    description: 'Validation errors keyed by field name',
                    type: 'object',
                    additionalProperties: new AdditionalProperties(
                        type: 'array',
                        items: new Items(type: 'string'),
                    ),
                    example: ['email' => ['The email field is required.']],
                ),
            ],
            type: 'object',
        );
        $schema->_context = new Context(['generated' => true]);

        $analysis->openapi->components->schemas[] = $schema;
    }
}
