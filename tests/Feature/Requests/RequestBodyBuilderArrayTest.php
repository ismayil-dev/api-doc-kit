<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IsmayilDev\ApiDocKit\Http\Requests\RequestBodyBuilder;
use OpenApi\Attributes\Items;

class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => 'sometimes|string',
            'paymentStatus' => 'sometimes|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string',
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'prices' => 'array',
            'prices.*' => 'numeric',
            'flags' => 'array',
            'flags.*' => 'boolean',
            'metadata' => 'array', // No nested rule - should default to string
        ];
    }
}

test('RequestBodyBuilder handles array types with Items', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateOrderRequest::class)->build();

    $schema = $requestBody->content[0]->schema;
    $properties = $schema->properties;

    // Find the 'tags' property
    $tagsProperty = collect($properties)->firstWhere('property', 'tags');
    expect($tagsProperty)->not->toBeNull();
    expect($tagsProperty->type)->toBe('array');
    expect($tagsProperty->items)->toBeInstanceOf(Items::class);
    expect($tagsProperty->items->type)->toBe('string');

    // Find the 'ids' property
    $idsProperty = collect($properties)->firstWhere('property', 'ids');
    expect($idsProperty)->not->toBeNull();
    expect($idsProperty->type)->toBe('array');
    expect($idsProperty->items)->toBeInstanceOf(Items::class);
    expect($idsProperty->items->type)->toBe('integer');

    // Find the 'prices' property
    $pricesProperty = collect($properties)->firstWhere('property', 'prices');
    expect($pricesProperty)->not->toBeNull();
    expect($pricesProperty->type)->toBe('array');
    expect($pricesProperty->items)->toBeInstanceOf(Items::class);
    expect($pricesProperty->items->type)->toBe('number');

    // Find the 'flags' property
    $flagsProperty = collect($properties)->firstWhere('property', 'flags');
    expect($flagsProperty)->not->toBeNull();
    expect($flagsProperty->type)->toBe('array');
    expect($flagsProperty->items)->toBeInstanceOf(Items::class);
    expect($flagsProperty->items->type)->toBe('boolean');

    // Find the 'metadata' property (no nested rule - should default to string)
    $metadataProperty = collect($properties)->firstWhere('property', 'metadata');
    expect($metadataProperty)->not->toBeNull();
    expect($metadataProperty->type)->toBe('array');
    expect($metadataProperty->items)->toBeInstanceOf(Items::class);
    expect($metadataProperty->items->type)->toBe('string'); // Default
});

test('RequestBodyBuilder does not include nested array rules as separate properties', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateOrderRequest::class)->build();

    $schema = $requestBody->content[0]->schema;
    $properties = $schema->properties;

    // Ensure 'tags.*', 'ids.*', etc. are not included as separate properties
    $propertyNames = collect($properties)->pluck('property')->toArray();

    expect($propertyNames)->not->toContain('tags.*');
    expect($propertyNames)->not->toContain('ids.*');
    expect($propertyNames)->not->toContain('prices.*');
    expect($propertyNames)->not->toContain('flags.*');
    expect($propertyNames)->not->toContain('metadata.*');
});
