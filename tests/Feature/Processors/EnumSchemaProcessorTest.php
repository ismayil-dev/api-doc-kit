<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Processors;

use IsmayilDev\ApiDocKit\Processors\EnumSchemaProcessor;
use OpenApi\Generator;
use OpenApi\Processors\ExpandEnums;
use Symfony\Component\Yaml\Yaml;

test('EnumSchemaProcessor generates correct schema for integer-backed enums', function () {
    $generator = new Generator;
    $generator->withProcessor(function ($pipeline) {
        // First ExpandEnums runs (as part of default pipeline)
        // Then our EnumSchemaProcessor should fix it
        $insertAfterExpandEnums = function (array $pipes) {
            foreach ($pipes as $ii => $pipe) {
                if ($pipe instanceof ExpandEnums) {
                    return $ii + 1;
                }
            }

            return null;
        };

        $pipeline->insert(new EnumSchemaProcessor, $insertAfterExpandEnums);
    });

    // Disable validation to suppress PathItem warnings
    $openapi = $generator->generate([__DIR__.'/../../doubles/Enums'], null, false);

    $yaml = $openapi->toYaml();

    // Parse YAML to inspect the actual OrderPaymentStatus schema
    $data = Yaml::parse($yaml);
    $orderPaymentStatusSchema = $data['components']['schemas']['OrderPaymentStatus'];

    // Assert OrderPaymentStatus (int-backed) has correct schema
    expect($orderPaymentStatusSchema['type'])->toBe('integer')
        ->and($orderPaymentStatusSchema['enum'])->toBe([0, 1, 2, 3])
        ->and($orderPaymentStatusSchema)->toHaveKey('x-enum-varnames')
        ->and($orderPaymentStatusSchema['x-enum-varnames'])->toBe(['Pending', 'Paid', 'Refunded', 'Cancelled']);
});

test('EnumSchemaProcessor generates correct schema for string-backed enums', function () {
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

        $pipeline->insert(new EnumSchemaProcessor, $insertAfterExpandEnums);
    });

    // Disable validation to suppress PathItem warnings
    $openapi = $generator->generate([__DIR__.'/../../doubles/Enums'], null, false);

    $yaml = $openapi->toYaml();

    // Parse YAML to inspect the actual OrderStatus schema
    $data = Yaml::parse($yaml);
    $orderStatusSchema = $data['components']['schemas']['OrderStatus'];

    // Assert OrderStatus (string-backed) has correct schema
    expect($orderStatusSchema['type'])->toBe('string')
        ->and($orderStatusSchema['enum'])->toBe(['draft', 'pending', 'completed', 'cancelled'])
        ->and($orderStatusSchema)->toHaveKey('x-enum-varnames')
        ->and($orderStatusSchema['x-enum-varnames'])->toBe(['Draft', 'Pending', 'Completed', 'Cancelled']);

    // String enums should ALSO have x-enum-varnames
    // This is useful when case names differ from values (e.g., InProgress vs 'in_progress')
});
