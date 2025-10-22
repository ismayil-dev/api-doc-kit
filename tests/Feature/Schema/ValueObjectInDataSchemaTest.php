<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Schema;

use IsmayilDev\ApiDocKit\Processors\DataSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\EnumSchemaProcessor;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\ExpandEnums;
use Symfony\Component\Yaml\Yaml;

test('DataSchema with value object property generates $ref to value object schema', function () {
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
    $dtoSchema = $data['components']['schemas']['DtoWithValueObject'];

    // Assert the DTO schema exists
    expect($dtoSchema)->toBeArray();

    // Assert recipient property uses $ref to Email value object
    expect($dtoSchema['properties']['recipient'])->toHaveKey('$ref');
    expect($dtoSchema['properties']['recipient']['$ref'])->toBe('#/components/schemas/Email');

    // Assert id property is still a normal integer
    expect($dtoSchema['properties']['id']['type'])->toBe('integer');

    // Assert subject property is still a normal string
    expect($dtoSchema['properties']['subject']['type'])->toBe('string');

    // Assert Email value object schema exists
    expect($data['components']['schemas'])->toHaveKey('Email');
    expect($data['components']['schemas']['Email']['type'])->toBe('object');
    expect($data['components']['schemas']['Email']['properties'])->toHaveKey('email');
    expect($data['components']['schemas']['Email']['properties']['email']['type'])->toBe('string');
});
