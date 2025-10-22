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
    expect($orderPaymentStatusSchema['type'])->toBe('integer');
    expect($orderPaymentStatusSchema['enum'])->toBe([0, 1, 2, 3]);
    expect($orderPaymentStatusSchema)->toHaveKey('x-enum-varnames');
    expect($orderPaymentStatusSchema['x-enum-varnames'])->toBe(['Pending', 'Paid', 'Refunded', 'Cancelled']);
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
    expect($orderStatusSchema['type'])->toBe('string');
    expect($orderStatusSchema['enum'])->toBe(['draft', 'pending', 'completed', 'cancelled']);

    // String enums should NOT have x-enum-varnames
    // (they don't need it since names are already in values)
    expect($orderStatusSchema)->not->toHaveKey('x-enum-varnames');
});
