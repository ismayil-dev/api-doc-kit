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
 * Registers two schemas by default:
 * - ErrorSchema: {statusCode: int, messages: string[], exception?: object}
 * - ValidationErrorSchema: {statusCode: int, message: string, errors: {field: string[]}, exception?: object}
 *
 * The `exception` field is optional — only present in debug/dev environments.
 *
 * Both schema names are configurable via api-doc-kit.responses.error.schema_names
 */
readonly class ErrorSchemaProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        $defaultName = config('api-doc-kit.responses.error.schema_names.default', 'ErrorSchema');
        $validationName = config('api-doc-kit.responses.error.schema_names.422', 'ValidationErrorSchema');

        $this->ensureComponents($analysis);
        $this->registerErrorSchema($analysis, $defaultName);
        $this->registerValidationErrorSchema($analysis, $validationName);
    }

    private function ensureComponents(Analysis $analysis): void
    {
        if ($analysis->openapi->components === Generator::UNDEFINED) {
            $analysis->openapi->components = new \OpenApi\Attributes\Components();
        }
        if ($analysis->openapi->components->schemas === Generator::UNDEFINED) {
            $analysis->openapi->components->schemas = [];
        }
    }

    private function registerErrorSchema(Analysis $analysis, string $name): void
    {
        if ($this->schemaExists($analysis, $name)) {
            return;
        }

        $schema = new Schema(
            schema: $name,
            title: $name,
            description: 'Error response',
            required: ['statusCode', 'messages'],
            properties: [
                new Property(
                    property: 'statusCode',
                    description: 'HTTP status code',
                    type: 'integer',
                    example: 400,
                ),
                new Property(
                    property: 'messages',
                    description: 'Error messages',
                    type: 'array',
                    items: new Items(type: 'string'),
                    example: ['Something went wrong.'],
                ),
                new Property(
                    property: 'exception',
                    description: 'Exception details (only present in debug/dev environment)',
                    type: 'object',
                    nullable: true,
                ),
            ],
            type: 'object',
        );
        $schema->_context = new Context(['generated' => true]);

        $analysis->openapi->components->schemas[] = $schema;
    }

    private function registerValidationErrorSchema(Analysis $analysis, string $name): void
    {
        if ($this->schemaExists($analysis, $name)) {
            return;
        }

        $schema = new Schema(
            schema: $name,
            title: $name,
            description: 'Validation error response',
            required: ['statusCode', 'message', 'errors'],
            properties: [
                new Property(
                    property: 'statusCode',
                    description: 'HTTP status code',
                    type: 'integer',
                    example: 422,
                ),
                new Property(
                    property: 'message',
                    description: 'Summary error message',
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
                new Property(
                    property: 'exception',
                    description: 'Exception details (only present in debug/dev environment)',
                    type: 'object',
                    nullable: true,
                ),
            ],
            type: 'object',
        );
        $schema->_context = new Context(['generated' => true]);

        $analysis->openapi->components->schemas[] = $schema;
    }

    private function schemaExists(Analysis $analysis, string $name): bool
    {
        foreach ($analysis->openapi->components->schemas ?? [] as $schema) {
            if ($schema instanceof Schema && $schema->schema === $name) {
                return true;
            }
        }

        return false;
    }
}
