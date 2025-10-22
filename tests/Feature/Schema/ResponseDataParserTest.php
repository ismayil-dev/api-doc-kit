<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Schema;

use IsmayilDev\ApiDocKit\Attributes\Properties\StringProperty;
use IsmayilDev\ApiDocKit\Schema\ResponseDataParser;
use IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses\DtoWithComputedField;
use IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses\MinimalDto;
use IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses\OrderDto;
use IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses\SimpleDto;
use OpenApi\Attributes\Property;

describe('ResponseDataParser', function () {
    beforeEach(function () {
        $this->parser = new ResponseDataParser;
    });

    it('should be instantiable', function () {
        expect($this->parser)->toBeInstanceOf(ResponseDataParser::class);
    });

    describe('parsing constructor properties', function () {
        it('should parse simple DTO with basic types', function () {
            $properties = $this->parser->parse(SimpleDto::class);

            expect($properties)->toHaveCount(4)
                ->and($properties[0])->toBeInstanceOf(Property::class)
                ->and($properties[0]->property)->toBe('id')
                ->and($properties[0]->type)->toBe('string')
                ->and($properties[1]->property)->toBe('name')
                ->and($properties[1]->type)->toBe('string')
                ->and($properties[2]->property)->toBe('age')
                ->and($properties[2]->type)->toBe('integer')
                ->and($properties[3]->property)->toBe('is_active')
                ->and($properties[3]->type)->toBe('boolean');
        });

        it('should parse DTO without toArray method', function () {
            $properties = $this->parser->parse(MinimalDto::class);

            expect($properties)->toHaveCount(2)
                ->and($properties[0]->property)->toBe('id')
                ->and($properties[0]->type)->toBe('integer')
                ->and($properties[1]->property)->toBe('title')
                ->and($properties[1]->type)->toBe('string');
        });
    });

    describe('parsing toArray method', function () {
        it('should parse DTO with toArray method', function () {
            $properties = $this->parser->parse(OrderDto::class);

            // Should extract fields from toArray
            expect($properties)->toHaveCount(6);

            $propertyNames = array_map(fn ($p) => $p->property, $properties);

            expect($propertyNames)->toContain('id')
                ->and($propertyNames)->toContain('description')
                ->and($propertyNames)->toContain('total')
                ->and($propertyNames)->toContain('status')
                ->and($propertyNames)->toContain('created_at')
                ->and($propertyNames)->toContain('formatted_total');
        });

        it('should infer types from constructor for toArray fields', function () {
            $properties = $this->parser->parse(OrderDto::class);

            $propertiesByName = [];
            foreach ($properties as $property) {
                $propertiesByName[$property->property] = $property;
            }

            // Fields from constructor should have correct types
            expect($propertiesByName['id']->type)->toBe('string')
                ->and($propertiesByName['description']->type)->toBe('string')
                ->and($propertiesByName['total']->type)->toBe('integer');
        });

        it('should handle computed fields in toArray', function () {
            $properties = $this->parser->parse(OrderDto::class);

            $propertyNames = array_map(fn ($p) => $p->property, $properties);

            // formatted_total is computed in toArray, not in constructor
            expect($propertyNames)->toContain('formatted_total');
        });
    });

    describe('type mapping', function () {
        it('should map PHP types to OpenAPI types correctly', function () {
            $properties = $this->parser->parse(SimpleDto::class);

            $typeMap = [];
            foreach ($properties as $property) {
                $typeMap[$property->property] = $property->type;
            }

            expect($typeMap['id'])->toBe('string')
                ->and($typeMap['name'])->toBe('string')
                ->and($typeMap['age'])->toBe('integer')
                ->and($typeMap['is_active'])->toBe('boolean');
        });

        it('should handle DateTimeInterface types', function () {
            $properties = $this->parser->parse(OrderDto::class);

            $propertyNames = array_map(fn ($p) => $p->property, $properties);

            // created_at is DateTimeImmutable in constructor
            expect($propertyNames)->toContain('created_at');
        });

        it('should handle enum types', function () {
            $properties = $this->parser->parse(OrderDto::class);

            $propertiesByName = [];
            foreach ($properties as $property) {
                $propertiesByName[$property->property] = $property;
            }

            // status is OrderStatus enum, should now have $ref to enum schema
            expect($propertiesByName['status']->ref)->toBe('#/components/schemas/OrderStatus');
        });
    });

    describe('example values', function () {
        it('should generate example values for properties', function () {
            $properties = $this->parser->parse(SimpleDto::class);

            foreach ($properties as $property) {
                expect($property->example)->not->toBeNull();
            }
        });

        it('should use appropriate examples for each type', function () {
            $properties = $this->parser->parse(SimpleDto::class);

            $exampleMap = [];
            foreach ($properties as $property) {
                $exampleMap[$property->property] = $property->example;
            }

            expect($exampleMap['id'])->toBeString()
                ->and($exampleMap['name'])->toBeString()
                ->and($exampleMap['age'])->toBeInt();

            // Boolean example might be true or UNDEFINED (OpenAPI default)
            expect($exampleMap['is_active'])->not->toBeNull();
        });
    });

    describe('explicit properties', function () {
        it('should use explicit properties for computed fields', function () {
            $explicitProps = [
                new StringProperty(
                    property: 'formatted_total',
                    description: 'Formatted amount',
                    example: '$100.00'
                ),
            ];

            $properties = $this->parser->parse(DtoWithComputedField::class, $explicitProps);

            $propertiesByName = [];
            foreach ($properties as $property) {
                $propertiesByName[$property->property] = $property;
            }

            // Computed field should use explicit definition
            expect($propertiesByName['formatted_total'])->toBeInstanceOf(Property::class)
                ->and($propertiesByName['formatted_total']->description)->toBe('Formatted amount')
                ->and($propertiesByName['formatted_total']->example)->toBe('$100.00');
        });

        it('should override auto-detected properties with explicit ones', function () {
            $explicitProps = [
                new StringProperty(
                    property: 'id',
                    description: 'Explicit ID description',
                    example: 'custom-id'
                ),
            ];

            $properties = $this->parser->parse(SimpleDto::class, $explicitProps);

            $propertiesByName = [];
            foreach ($properties as $property) {
                $propertiesByName[$property->property] = $property;
            }

            // Explicit should override auto-detected
            expect($propertiesByName['id']->description)->toBe('Explicit ID description')
                ->and($propertiesByName['id']->example)->toBe('custom-id');
        });

        it('should handle multiple explicit properties', function () {
            $explicitProps = [
                new StringProperty(property: 'formatted_total', description: 'Formatted total', example: '$99.99'),
            ];

            $properties = $this->parser->parse(DtoWithComputedField::class, $explicitProps);

            // Should have id, total from constructor, and formatted_total (explicit overrides auto-detected)
            expect($properties)->toHaveCount(3);

            $propertyNames = array_map(fn ($p) => $p->property, $properties);
            expect($propertyNames)->toContain('id')
                ->and($propertyNames)->toContain('total')
                ->and($propertyNames)->toContain('formatted_total');
        });
    });
});
