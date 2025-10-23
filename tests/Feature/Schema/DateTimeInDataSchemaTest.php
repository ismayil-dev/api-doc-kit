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

test('DataSchema with DateTime properties uses correct formats from attributes', function () {
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
    $dtoSchema = $data['components']['schemas']['DtoWithDates'];

    // Assert the DTO schema exists
    expect($dtoSchema)->toBeArray();

    // Priority 1: Property-level attribute with format
    // birthDate: #[DateTime(format: 'Y-m-d')] -> birth_date in toArray
    expect($dtoSchema['properties']['birth_date']['type'])->toBe('string');
    expect($dtoSchema['properties']['birth_date']['format'])->toBe('date');
    expect($dtoSchema['properties']['birth_date']['x-format'])->toBe('Y-m-d');
    expect($dtoSchema['properties']['birth_date']['example'])->toBe('2024-01-15');

    // Priority 1: Property-level attribute with type
    // createdAt: #[DateTime(type: 'datetime')] -> created_at in toArray
    expect($dtoSchema['properties']['created_at']['type'])->toBe('string');
    expect($dtoSchema['properties']['created_at']['format'])->toBe('date-time');
    expect($dtoSchema['properties']['created_at']['x-format'])->toBe('Y-m-d H:i:s'); // From config
    expect($dtoSchema['properties']['created_at']['example'])->toBe('2024-01-15 14:30:00');

    // Priority 2: DataSchema properties with type
    // updatedAt: new DateTimeProperty(property: 'updatedAt', type: 'datetime') -> updated_at in toArray
    expect($dtoSchema['properties']['updated_at']['type'])->toBe('string');
    expect($dtoSchema['properties']['updated_at']['format'])->toBe('date-time');
    expect($dtoSchema['properties']['updated_at']['x-format'])->toBe('Y-m-d H:i:s');
    expect($dtoSchema['properties']['updated_at']['example'])->toBe('2024-01-15 14:30:00');

    // Priority 2: DataSchema properties with custom format
    // publishedAt: new DateTimeProperty(property: 'publishedAt', format: 'd/m/Y H:i') -> published_at in toArray
    expect($dtoSchema['properties']['published_at']['type'])->toBe('string');
    expect($dtoSchema['properties']['published_at']['format'])->toBe('date-time');
    expect($dtoSchema['properties']['published_at']['x-format'])->toBe('d/m/Y H:i');
    expect($dtoSchema['properties']['published_at']['example'])->toBe('15/01/2024 14:30');

    // Priority 3: Global config (default datetime format)
    // defaultFormatDate: No attribute, no DataSchema property -> default_format_date in toArray
    expect($dtoSchema['properties']['default_format_date']['type'])->toBe('string');
    expect($dtoSchema['properties']['default_format_date']['format'])->toBe('date-time');
    expect($dtoSchema['properties']['default_format_date']['x-format'])->toBe('Y-m-d H:i:s');
    expect($dtoSchema['properties']['default_format_date']['example'])->toBe('2024-01-15 14:30:00');

    // Also check that regular int property still works
    expect($dtoSchema['properties']['id']['type'])->toBe('integer');
});
