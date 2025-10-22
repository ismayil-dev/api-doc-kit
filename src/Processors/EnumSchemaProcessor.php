<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Processors;

use BackedEnum;
use IsmayilDev\ApiDocKit\Attributes\Schema\Enum as EnumAttribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Analysis;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;
use ReflectionClass;
use ReflectionException;

/**
 * Processes Enum attributes to generate proper OpenAPI enum schemas.
 *
 * For integer-backed enums, adds x-enum-varnames to enable proper SDK generation.
 */
readonly class EnumSchemaProcessor
{
    /**
     * Process all Enum annotations
     *
     * @throws ReflectionException
     */
    public function __invoke(Analysis $analysis): void
    {
        $annotations = $analysis->annotations;

        foreach ($annotations as $annotation) {
            if (! $annotation instanceof EnumAttribute) {
                continue;
            }

            $classWithNamespace = $this->getClassWithNameSpace($annotation);

            try {
                $reflection = new ReflectionClass($classWithNamespace);

                // Verify it's actually an enum
                if (! $reflection->isEnum()) {
                    continue;
                }

                // Get enum cases
                $cases = $reflection->getMethod('cases')->invoke(null);

                if (empty($cases)) {
                    continue;
                }

                // Extract values and names
                $values = [];
                $names = [];

                foreach ($cases as $case) {
                    $names[] = $case->name;

                    // For backed enums, get the value; for unit enums, use name
                    if ($case instanceof BackedEnum) {
                        $values[] = $case->value;
                    } else {
                        $values[] = $case->name;
                    }
                }

                // Determine enum type
                $firstCase = $cases[0];
                $isIntEnum = $firstCase instanceof BackedEnum && is_int($firstCase->value);

                // Set schema properties
                $className = class_basename($classWithNamespace);

                if ($annotation->title === Generator::UNDEFINED || $annotation->title === null) {
                    $annotation->title = $className;
                }

                if ($annotation->description === Generator::UNDEFINED || $annotation->description === null) {
                    $annotation->description = "Enum for {$className}";
                }

                // Set type based on backing type
                $annotation->type = $isIntEnum ? OpenApiPropertyType::INTEGER->value : OpenApiPropertyType::STRING->value;

                // Set enum values
                $annotation->enum = $values;

                // For integer enums, add x-enum-varnames to help SDK generators
                if ($isIntEnum) {
                    $existingX = is_array($annotation->x) ? $annotation->x : [];
                    $annotation->x = array_merge(
                        $existingX,
                        ['enum-varnames' => $names]
                    );
                }
            } catch (ReflectionException $e) {
                // Skip enums that can't be reflected
                continue;
            }
        }
    }

    /**
     * Get fully qualified class name from annotation context
     */
    protected function getClassWithNameSpace(Schema $annotation): string
    {
        // For enums, the name is stored in the 'enum' property, not 'class'
        $className = $annotation->_context->enum ?? $annotation->_context->class;

        return "{$annotation->_context->namespace}\\{$className}";
    }
}
