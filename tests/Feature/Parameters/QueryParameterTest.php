<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Parameters;

use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\ArrayQueryParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\BoolQueryParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\DateQueryParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\EnumQueryParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\FloatQueryParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\PaginationQueryParameters;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\SearchQueryParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\SortQueryParameter;
use IsmayilDev\ApiDocKit\Helper\EnumSchemaHelper;
use IsmayilDev\ApiDocKit\Tests\Doubles\Enums\OrderPaymentStatus;
use IsmayilDev\ApiDocKit\Tests\Doubles\Enums\OrderStatus;

test('FloatQueryParameter emits number/float schema with bounds', function () {
    $param = new FloatQueryParameter('price', minimum: 0.0, maximum: 100.0);

    expect($param->name)->toBe('price')
        ->and($param->schema->type)->toBe('number')
        ->and($param->schema->format)->toBe('float')
        ->and($param->schema->minimum)->toBe(0.0)
        ->and($param->schema->maximum)->toBe(100.0);
});

test('BoolQueryParameter emits boolean schema', function () {
    $param = new BoolQueryParameter('isActive');

    expect($param->name)->toBe('isActive')
        ->and($param->schema->type)->toBe('boolean');
});

test('DateQueryParameter emits string/date by default', function () {
    $param = new DateQueryParameter('from');

    expect($param->schema->type)->toBe('string')
        ->and($param->schema->format)->toBe('date');
});

test('DateQueryParameter emits string/date-time when withTime=true', function () {
    $param = new DateQueryParameter('from', withTime: true);

    expect($param->schema->format)->toBe('date-time');
});

test('EnumQueryParameter uses EnumSchemaHelper for string-backed enum', function () {
    $param = new EnumQueryParameter('status', enumClass: OrderStatus::class);

    expect($param->schema->type)->toBe('string')
        ->and($param->schema->enum)->toBe(['draft', 'pending', 'completed', 'cancelled']);
});

test('EnumSchemaHelper emits x-enum-varnames for int-backed enums', function () {
    $schema = EnumSchemaHelper::buildSchema(OrderPaymentStatus::class);

    expect($schema->type)->toBe('integer')
        ->and($schema->enum)->toBe([0, 1, 2, 3])
        ->and($schema->x)->toHaveKey('enum-varnames')
        ->and($schema->x['enum-varnames'])->toBe(['Pending', 'Paid', 'Refunded', 'Cancelled']);
});

test('ArrayQueryParameter uses style=form, explode=true', function () {
    $param = new ArrayQueryParameter('ids', itemType: 'integer', minItems: 1);

    expect($param->style)->toBe('form')
        ->and($param->explode)->toBeTrue()
        ->and($param->schema->type)->toBe('array')
        ->and($param->schema->items->type)->toBe('integer')
        ->and($param->schema->minItems)->toBe(1);
});

test('PaginationQueryParameters::make returns page + per_page params', function () {
    $params = PaginationQueryParameters::make(defaultPerPage: 20, maxPerPage: 50);

    expect($params)->toHaveCount(2)
        ->and($params[0]->name)->toBe('page')
        ->and($params[0]->schema->minimum)->toBe(1)
        ->and($params[1]->name)->toBe('per_page')
        ->and($params[1]->schema->default)->toBe(20)
        ->and($params[1]->schema->maximum)->toBe(50);
});

test('SortQueryParameter emits enum schema with -prefix variants', function () {
    $param = new SortQueryParameter(allowedFields: ['name', 'createdAt']);

    expect($param->name)->toBe('sort')
        ->and($param->description)->toContain('name')
        ->and($param->description)->toContain('-name')
        ->and($param->description)->toContain('createdAt')
        ->and($param->description)->toContain('-createdAt')
        ->and($param->schema->enum)->toBe(['name', '-name', 'createdAt', '-createdAt'])
        // Default example is the first descending-sort token so Postman has
        // a meaningful placeholder rather than randexp-generated gibberish.
        ->and($param->example)->toBe('-name');
});

test('SortQueryParameter respects an explicit example argument', function () {
    $param = new SortQueryParameter(allowedFields: ['name', 'createdAt'], example: 'createdAt');

    expect($param->example)->toBe('createdAt');
});

test('SearchQueryParameter defaults to "q"', function () {
    $param = new SearchQueryParameter;

    expect($param->name)->toBe('q')
        ->and($param->schema->type)->toBe('string');
});
