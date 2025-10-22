<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Processors;

use Illuminate\Support\Facades\Log;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use IsmayilDev\ApiDocKit\Exceptions\StrictModeException;
use IsmayilDev\ApiDocKit\Schema\ResponseDataParser;
use OpenApi\Analysis;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use ReflectionException;

/**
 * Processes DataSchema attributes to generate OpenAPI schemas
 * from typed data classes (DTOs, value objects, etc.)
 */
readonly class DataSchemaProcessor
{
    public function __construct(
        private readonly ResponseDataParser $parser
    ) {}

    /**
     * Process all DataSchema annotations
     *
     * @throws ReflectionException
     */
    public function __invoke(Analysis $analysis): void
    {
        $annotations = $analysis->annotations;

        foreach ($annotations as $annotation) {
            if (! $annotation instanceof DataSchema) {
                continue;
            }

            $classWithNamespace = $this->getClassWithNameSpace($annotation);
            $className = class_basename($classWithNamespace);

            try {
                // Get explicit property definitions from attribute
                $explicitProperties = $annotation->getExplicitProperties();

                // Parse the data class to extract properties
                $properties = $this->parser->parse($classWithNamespace, $explicitProperties);

                // Extract required fields (non-nullable properties)
                $required = $this->extractRequiredFields($properties);

                // Get schema title from attribute or generate from class name
                if ($annotation->title === Generator::UNDEFINED || $annotation->title === null) {
                    $annotation->title = $className;
                }

                // Set description if not provided
                if ($annotation->description === Generator::UNDEFINED || $annotation->description === null) {
                    $annotation->description = "Schema for {$className}";
                }

                // Set schema properties
                $annotation->type = OpenApiPropertyType::OBJECT->value;
                $annotation->properties = $properties;
                $annotation->required = $required;
            } catch (StrictModeException $e) {
                // Re-throw with additional context
                Log::error("Failed to generate schema for {$className}: {$e->getMessage()}");

                throw $e;
            } catch (ReflectionException $e) {
                Log::error("Reflection error for {$className}: {$e->getMessage()}");

                throw $e;
            }
        }
    }

    /**
     * Get fully qualified class name from annotation context
     */
    protected function getClassWithNameSpace(Schema $annotation): string
    {
        return "{$annotation->_context->namespace}\\{$annotation->_context->class}";
    }

    /**
     * Extract required fields from properties
     *
     * @param  array<\OpenApi\Attributes\Property>  $properties
     * @return array<string>
     */
    protected function extractRequiredFields(array $properties): array
    {
        $required = [];

        foreach ($properties as $property) {
            // If property is not nullable, it's required
            /*if ($property->nullable === false || $property->nullable === Generator::UNDEFINED) {
                $required[] = $property->property;
            }*/
            // @TODO: Think about this later
            $required[] = $property->property;
        }

        return $required;
    }
}
