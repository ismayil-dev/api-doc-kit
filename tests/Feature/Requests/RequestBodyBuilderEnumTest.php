<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use IsmayilDev\ApiDocKit\Http\Requests\RequestBodyBuilder;
use IsmayilDev\ApiDocKit\Tests\Doubles\Enums\OrderStatus;
use IsmayilDev\ApiDocKit\Tests\Doubles\Enums\PaymentStatus;

class CreateOrderWithEnumRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'paymentStatus' => ['sometimes', Rule::enum(PaymentStatus::class)],
            'quantity' => 'required|integer',
        ];
    }
}

test('RequestBodyBuilder handles Rule::enum() with schema reference', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateOrderWithEnumRequest::class)->build();

    $schema = $requestBody->content[0]->schema;
    $properties = $schema->properties;

    // Find the 'status' property
    $statusProperty = collect($properties)->firstWhere('property', 'status');
    expect($statusProperty)->not->toBeNull()
        ->and($statusProperty->ref)->toBe('#/components/schemas/OrderStatus')
        ->and($statusProperty->description)->toBe('The Status');

    // Find the 'paymentStatus' property
    $paymentStatusProperty = collect($properties)->firstWhere('property', 'paymentStatus');
    expect($paymentStatusProperty)->not->toBeNull()
        ->and($paymentStatusProperty->ref)->toBe('#/components/schemas/PaymentStatus')
        ->and($paymentStatusProperty->description)->toBe('The Paymentstatus');
    // Str::title() doesn't handle camelCase well

    // Verify non-enum properties still work normally
    $titleProperty = collect($properties)->firstWhere('property', 'title');
    expect($titleProperty)->not->toBeNull()
        ->and($titleProperty->type)->toBe('string')
        ->and($titleProperty->ref)->toBe('@OA\Generator::UNDEFINED🙈');

    $quantityProperty = collect($properties)->firstWhere('property', 'quantity');
    expect($quantityProperty)->not->toBeNull()
        ->and($quantityProperty->type)->toBe('integer')
        ->and($quantityProperty->ref)->toBe('@OA\Generator::UNDEFINED🙈');
});

test('RequestBodyBuilder enum properties have correct required status', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateOrderWithEnumRequest::class)->build();

    $schema = $requestBody->content[0]->schema;
    $required = $schema->required;

    // 'status' is required
    expect($required)->toContain('status')
        // 'paymentStatus' is sometimes (not required)
        ->and($required)->not->toContain('paymentStatus');
});
