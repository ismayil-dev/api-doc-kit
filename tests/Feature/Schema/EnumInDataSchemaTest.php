<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Schema;

use IsmayilDev\ApiDocKit\Processors\DataSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\EnumSchemaProcessor;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\ExpandEnums;
use Symfony\Component\Yaml\Yaml;

test('DataSchema with enum property generates $ref to enum schema', function () {
    $generator = new Generator;
    $generator->withProcessor(function ($pipeline) {
        // Add EnumSchemaProcessor first
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
        $pipeline->insert(app(DataSchemaProcessor::class), $insertBeforeBuildPaths);
    });

    $openapi = $generator->generate([__DIR__.'/../../doubles'], null, false);
    $yaml = $openapi->toYaml();

    // Parse YAML to inspect the schema
    $data = Yaml::parse($yaml);
    $dtoSchema = $data['components']['schemas']['DtoWithEnum'];

    // Assert the DTO schema exists
    expect($dtoSchema)->toBeArray();

    // Assert status property uses $ref to OrderStatus enum
    expect($dtoSchema['properties']['status'])->toHaveKey('$ref');
    expect($dtoSchema['properties']['status']['$ref'])->toBe('#/components/schemas/OrderStatus');

    // Assert id property is still a normal integer
    expect($dtoSchema['properties']['id']['type'])->toBe('integer');

    // Assert OrderStatus enum schema exists
    expect($data['components']['schemas'])->toHaveKey('OrderStatus');
    expect($data['components']['schemas']['OrderStatus']['type'])->toBe('string');
    // Note: There are two OrderStatus enums in test doubles, so just check for common values
    expect($data['components']['schemas']['OrderStatus']['enum'])->toContain('pending');
    expect($data['components']['schemas']['OrderStatus']['enum'])->toContain('completed');
});
