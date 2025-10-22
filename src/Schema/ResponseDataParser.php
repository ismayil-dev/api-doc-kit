<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Schema;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use IsmayilDev\ApiDocKit\Exceptions\StrictModeException;
use OpenApi\Attributes\Property;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Parses data classes to extract schema information.
 *
 * Extracts properties from:
 * 1. Constructor promoted properties (public readonly properties)
 * 2. toArray() method structure (if class implements Arrayable)
 */
class ResponseDataParser
{
    private ParserFactory $parserFactory;

    public function __construct()
    {
        $this->parserFactory = new ParserFactory;
    }

    /**
     * Parse a data class and extract OpenAPI properties
     *
     * @param  class-string  $className
     * @param  array<Property>|null  $explicitProperties  Explicit property definitions from DataSchema attribute
     * @return array<Property>
     *
     * @throws ReflectionException
     * @throws StrictModeException
     */
    public function parse(string $className, ?array $explicitProperties = null): array
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ReflectionException("Failed to reflect class {$className}: {$e->getMessage()}", 0, $e);
        }

        // Get properties from constructor
        $constructorProperties = $this->parseConstructorProperties($reflection);

        // Build explicit properties map for quick lookup
        $explicitPropsMap = $this->buildExplicitPropertiesMap($explicitProperties);

        // If class implements Arrayable, also parse toArray() method
        if ($reflection->implementsInterface(Arrayable::class)) {
            $toArrayProperties = $this->parseToArrayMethod($reflection, $className);

            // Merge all properties: constructor + toArray + explicit
            return $this->mergeProperties($constructorProperties, $toArrayProperties, $explicitPropsMap, $className);
        }

        // Return constructor properties merged with explicit properties
        return $this->mergeConstructorWithExplicit($constructorProperties, $explicitPropsMap);
    }

    /**
     * Parse constructor promoted properties
     *
     * @return array<string, Property>
     */
    protected function parseConstructorProperties(ReflectionClass $reflection): array
    {
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $properties = [];

        foreach ($constructor->getParameters() as $parameter) {
            // Only process promoted properties (public readonly properties)
            if (! $parameter->isPromoted()) {
                continue;
            }

            $name = $parameter->getName();
            $type = $parameter->getType();

            if ($type === null) {
                continue;
            }

            $properties[$name] = $this->buildProperty(
                name: $name,
                type: $type,
                nullable: $parameter->allowsNull(),
            );
        }

        return $properties;
    }

    /**
     * Parse toArray() method to extract field structure
     *
     * @param  string  $className  For error/warning messages
     * @return array<string, Property>
     */
    protected function parseToArrayMethod(ReflectionClass $reflection, string $className): array
    {
        if (! $reflection->hasMethod('toArray')) {
            return [];
        }

        $method = $reflection->getMethod('toArray');
        $fileName = $method->getFileName();

        if ($fileName === false) {
            return [];
        }

        try {
            $code = file_get_contents($fileName);
            if ($code === false) {
                return [];
            }

            $parser = $this->parserFactory->createForNewestSupportedVersion();
            $ast = $parser->parse($code);

            if ($ast === null) {
                return [];
            }

            // Find the toArray method in AST
            $nodeFinder = new NodeFinder;
            $toArrayMethod = $nodeFinder->findFirst($ast, function (Node $node) {
                return $node instanceof ClassMethod && $node->name->toString() === 'toArray';
            });

            if (! $toArrayMethod instanceof ClassMethod) {
                return [];
            }

            // Find return array structure
            $arrayNodes = $nodeFinder->find($toArrayMethod, function (Node $node) {
                return $node instanceof Array_;
            });

            $properties = [];

            foreach ($arrayNodes as $arrayNode) {
                if (! $arrayNode instanceof Array_) {
                    continue;
                }

                foreach ($arrayNode->items as $item) {
                    if (! $item instanceof ArrayItem) {
                        continue;
                    }

                    // Get array key (field name)
                    $key = $item->key;
                    if (! $key instanceof String_) {
                        continue;
                    }

                    $fieldName = $key->value;

                    // Try to infer type from the value expression
                    $inferredType = $this->inferTypeFromExpression($item->value, $reflection);

                    $properties[$fieldName] = new Property(
                        property: $fieldName,
                        type: $inferredType->value,
                        nullable: false,
                    );
                }
            }

            return $properties;
        } catch (\Throwable $e) {
            // If AST parsing fails, silently return empty array
            return [];
        }
    }

    /**
     * Infer OpenAPI type from AST expression
     */
    protected function inferTypeFromExpression(Node $expression, ReflectionClass $context): OpenApiPropertyType
    {
        // Check if it's accessing a promoted property ($this->id)
        if ($expression instanceof Node\Expr\PropertyFetch) {
            $propertyName = $expression->name;
            if ($propertyName instanceof Node\Identifier) {
                $name = $propertyName->name;

                // Try to get type from constructor parameter
                $constructor = $context->getConstructor();
                if ($constructor !== null) {
                    foreach ($constructor->getParameters() as $param) {
                        if ($param->getName() === $name && $param->isPromoted()) {
                            $type = $param->getType();
                            if ($type !== null) {
                                return $this->mapPhpTypeToOpenApi($type);
                            }
                        }
                    }
                }
            }
        }

        // Check for method calls (e.g., $this->status->value for enums)
        if ($expression instanceof Node\Expr\MethodCall) {
            $var = $expression->var;
            if ($var instanceof Node\Expr\PropertyFetch) {
                $propertyName = $var->name;
                if ($propertyName instanceof Node\Identifier) {
                    $name = $propertyName->name;
                    $constructor = $context->getConstructor();
                    if ($constructor !== null) {
                        foreach ($constructor->getParameters() as $param) {
                            if ($param->getName() === $name && $param->isPromoted()) {
                                $type = $param->getType();
                                if ($type instanceof ReflectionNamedType) {
                                    $typeName = $type->getName();
                                    if (enum_exists($typeName) && is_subclass_of($typeName, BackedEnum::class)) {
                                        // For backed enums, return the backing type
                                        $enumReflection = new ReflectionClass($typeName);
                                        $cases = $enumReflection->getMethod('cases')->invoke(null);
                                        if (count($cases) > 0) {
                                            $firstCase = $cases[0];

                                            return is_int($firstCase->value) ? OpenApiPropertyType::INTEGER : OpenApiPropertyType::STRING;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Default to string for unknown expressions (formatted values, computations, etc.)
        return OpenApiPropertyType::STRING;
    }

    /**
     * Merge properties from constructor, toArray, and explicit definitions
     *
     * @param  array<string, Property>  $constructorProps
     * @param  array<string, Property>  $toArrayProps
     * @param  array<string, Property>  $explicitProps
     * @param  string  $className  For error/warning messages
     * @return array<Property>
     *
     * @throws StrictModeException
     */
    protected function mergeProperties(
        array $constructorProps,
        array $toArrayProps,
        array $explicitProps,
        string $className
    ): array {
        $strictMode = $this->isStrictModeEnabled();
        $merged = [];

        foreach ($toArrayProps as $name => $property) {
            // Priority 1: Explicit property definition
            if (isset($explicitProps[$name])) {
                $merged[] = $explicitProps[$name];

                continue;
            }

            // Priority 2: Constructor property (auto-detected, reliable type)
            if (isset($constructorProps[$name])) {
                $merged[] = $constructorProps[$name];

                continue;
            }

            // Priority 3: Computed field from toArray()
            // Check if type was successfully inferred (not defaulting to string)
            if ($property->type === OpenApiPropertyType::STRING->value && $this->looksLikeComputedField($name, $constructorProps)) {
                // This is likely a computed field with unknown type
                if ($strictMode) {
                    throw StrictModeException::undefinedComputedField($name, class_basename($className));
                }

                // Non-strict mode: log warning and use string
                $this->logWarning(
                    "DataSchema: Unknown type for computed field '{$name}' in {$className}, defaulting to 'string'. ".
                    'Consider defining it explicitly in #[DataSchema(properties: [...])] or enable strict mode.'
                );
            }

            $merged[] = $property;
        }

        return array_values($merged);
    }

    /**
     * Merge constructor properties with explicit properties (for non-Arrayable classes)
     *
     * @param  array<string, Property>  $constructorProps
     * @param  array<string, Property>  $explicitProps
     * @return array<Property>
     */
    protected function mergeConstructorWithExplicit(array $constructorProps, array $explicitProps): array
    {
        // Explicit properties override constructor properties
        $merged = array_merge($constructorProps, $explicitProps);

        return array_values($merged);
    }

    /**
     * Build map of explicit properties keyed by property name
     *
     * @param  array<Property>|null  $explicitProperties
     * @return array<string, Property>
     */
    protected function buildExplicitPropertiesMap(?array $explicitProperties): array
    {
        if ($explicitProperties === null) {
            return [];
        }

        $map = [];
        foreach ($explicitProperties as $property) {
            if ($property instanceof Property && $property->property !== null) {
                $map[$property->property] = $property;
            }
        }

        return $map;
    }

    /**
     * Determine if a field is likely a computed field (not in constructor)
     */
    protected function looksLikeComputedField(string $fieldName, array $constructorProps): bool
    {
        // If it's not in constructor, it's computed
        return ! isset($constructorProps[$fieldName]);
    }

    /**
     * Build OpenAPI Property from PHP type
     */
    protected function buildProperty(
        string $name,
        ReflectionNamedType|ReflectionUnionType $type,
        bool $nullable = false,
    ): Property {
        $openApiType = $this->mapPhpTypeToOpenApi($type);
        $example = $this->getExampleValue($openApiType);

        return new Property(
            property: $name,
            type: $openApiType->value,
            example: $example,
            nullable: $nullable,
        );
    }

    /**
     * Map PHP type to OpenAPI type
     */
    protected function mapPhpTypeToOpenApi(ReflectionNamedType|ReflectionUnionType $type): OpenApiPropertyType
    {
        if ($type instanceof ReflectionUnionType) {
            // For union types, pick the first non-null type
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType && $subType->getName() !== 'null') {
                    return $this->mapNamedTypeToOpenApi($subType);
                }
            }

            return OpenApiPropertyType::STRING;
        }

        return $this->mapNamedTypeToOpenApi($type);
    }

    /**
     * Map ReflectionNamedType to OpenAPI type
     */
    protected function mapNamedTypeToOpenApi(ReflectionNamedType $type): OpenApiPropertyType
    {
        $typeName = $type->getName();

        // Built-in types
        return match ($typeName) {
            'string' => OpenApiPropertyType::STRING,
            'int', 'integer' => OpenApiPropertyType::INTEGER,
            'float', 'double' => OpenApiPropertyType::NUMBER,
            'bool', 'boolean' => OpenApiPropertyType::BOOLEAN,
            'array' => OpenApiPropertyType::ARRAY,
            'object' => OpenApiPropertyType::OBJECT,
            default => $this->mapClassTypeToOpenApi($typeName),
        };
    }

    /**
     * Map class types to OpenAPI types
     */
    protected function mapClassTypeToOpenApi(string $className): OpenApiPropertyType
    {
        if (! class_exists($className) && ! enum_exists($className) && ! interface_exists($className)) {
            return OpenApiPropertyType::STRING;
        }

        // Handle DateTimeInterface
        if (is_subclass_of($className, DateTimeInterface::class) || $className === DateTimeInterface::class) {
            return OpenApiPropertyType::DATETIME;
        }

        // Handle enums - return string (or integer for backed int enums)
        if (enum_exists($className)) {
            if (is_subclass_of($className, BackedEnum::class)) {
                // Check backing type
                $reflection = new ReflectionClass($className);
                $cases = $reflection->getMethod('cases')->invoke(null);
                if (count($cases) > 0) {
                    $firstCase = $cases[0];

                    return is_int($firstCase->value) ? OpenApiPropertyType::INTEGER : OpenApiPropertyType::STRING;
                }
            }

            return OpenApiPropertyType::STRING;
        }

        // Default to string for unknown classes
        return OpenApiPropertyType::STRING;
    }

    /**
     * Get example value for OpenAPI type
     */
    protected function getExampleValue(OpenApiPropertyType $type): mixed
    {
        return match ($type) {
            OpenApiPropertyType::STRING => 'string',
            OpenApiPropertyType::INTEGER => 123,
            OpenApiPropertyType::NUMBER => 123.45,
            OpenApiPropertyType::BOOLEAN => true,
            OpenApiPropertyType::ARRAY => [],
            OpenApiPropertyType::DATETIME => '2024-01-01 00:00:00',
            OpenApiPropertyType::DATE => '2024-01-01',
            default => null,
        };
    }

    /**
     * Check if strict mode is enabled
     *
     * Safely checks config, falling back to false if config is not available (e.g., in tests)
     */
    protected function isStrictModeEnabled(): bool
    {
        try {
            return config('api-doc-kit.schema.strict_mode', false);
        } catch (\Throwable) {
            // If config() helper is not available (e.g., in unit tests), default to false
            return false;
        }
    }

    /**
     * Log warning safely (handles when Log facade is not available)
     */
    protected function logWarning(string $message): void
    {
        try {
            Log::warning($message);
        } catch (\Throwable) {
            // Silently fail if Log facade is not available (e.g., in unit tests)
            // In production, this will work fine
        }
    }
}
