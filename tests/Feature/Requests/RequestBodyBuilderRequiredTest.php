<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IsmayilDev\ApiDocKit\Http\Requests\RequestBodyBuilder;
use OpenApi\Generator;

class AllOptionalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => 'sometimes|integer',
            'duration' => 'sometimes|integer',
            'is_enabled' => 'sometimes|boolean',
        ];
    }
}

class MixedRequiredRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_id' => 'required|string',
            'price' => 'required|integer',
            'duration' => 'sometimes|integer',
        ];
    }
}

test('schema omits `required` entirely when every rule is sometimes', function () {
    $builder = new RequestBodyBuilder;
    $body = $builder->requestClass(AllOptionalRequest::class)->build();

    $schema = $body->content[0]->schema;

    // OpenAPI 3.0 / JSON Schema forbid `required: []` — must be absent or
    // have at least one entry. swagger-php's Generator::UNDEFINED sentinel
    // strips the key during serialization.
    expect($schema->required)->toBe(Generator::UNDEFINED);
});

test('schema preserves `required` when at least one rule uses required', function () {
    $builder = new RequestBodyBuilder;
    $body = $builder->requestClass(MixedRequiredRequest::class)->build();

    $schema = $body->content[0]->schema;

    expect($schema->required)
        ->toBeArray()
        ->and($schema->required)
        ->toEqualCanonicalizing(['service_id', 'price']);
});
