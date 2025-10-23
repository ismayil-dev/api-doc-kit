<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Schema;

use IsmayilDev\ApiDocKit\Processors\DataSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\EnumSchemaProcessor;
use IsmayilDev\ApiDocKit\Schema\ResponseDataParser;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\ExpandEnums;
use Symfony\Component\Yaml\Yaml;

test('DataSchema with array properties uses correct item types', function () {
    $generator = new Generator;
    $generator->withProcessor(function ($pipeline) {
        $insertAfterExpandEnums = function (array $pipes) {
            foreach ($pipes as $ii => $pipe) {
                if ($pipe instanceof ExpandEnums) {
                    return $ii + 1;
                }
            }

            return null;
        };

        $insertBeforeBuildPaths = function (array $pipes) {
            foreach ($pipes as $ii => $pipe) {
                if ($pipe instanceof BuildPaths) {
                    return $ii;
                }
            }

            return null;
        };

        $pipeline->insert(new EnumSchemaProcessor, $insertAfterExpandEnums);
        $pipeline->insert(new DataSchemaProcessor(new ResponseDataParser), $insertBeforeBuildPaths);
    });

    $openapi = $generator->generate([__DIR__.'/../../doubles'], null, false);
    $yaml = $openapi->toYaml();

    // Parse YAML to inspect the schema
    $data = Yaml::parse($yaml);
    $dtoSchema = $data['components']['schemas']['DtoWithArrays'];

    // Assert the DTO schema exists
    expect($dtoSchema)->toBeArray()
        // Priority 1: Property-level attribute with class reference
        // items: #[ArrayOf(OrderItem::class)]
        ->and($dtoSchema['properties']['items']['type'])->toBe('array')
        ->and($dtoSchema['properties']['items']['items'])->toHaveKey('$ref')
        ->and($dtoSchema['properties']['items']['items']['$ref'])->toBe('#/components/schemas/OrderItem')
        // Priority 2: Property-level attribute with primitive type
        // ids: #[ArrayOf('integer')]
        ->and($dtoSchema['properties']['ids']['type'])->toBe('array')
        ->and($dtoSchema['properties']['ids']['items']['type'])->toBe('integer')
        // Priority 3: DataSchema properties parameter
        // categories: new ArrayProperty(property: 'categories', itemType: 'string')
        ->and($dtoSchema['properties']['categories']['type'])->toBe('array')
        ->and($dtoSchema['properties']['categories']['items']['type'])->toBe('string')
        // Verify OrderItem schema exists
        ->and($data['components']['schemas'])->toHaveKey('OrderItem')
        ->and($data['components']['schemas']['OrderItem']['properties'])->toHaveKey('sku')
        ->and($data['components']['schemas']['OrderItem']['properties'])->toHaveKey('quantity')
        ->and($data['components']['schemas']['OrderItem']['properties'])->toHaveKey('price')
        // Also check that regular int property still works
        ->and($dtoSchema['properties']['id']['type'])->toBe('integer');
});
