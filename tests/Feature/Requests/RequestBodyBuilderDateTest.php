<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IsmayilDev\ApiDocKit\Http\Requests\RequestBodyBuilder;

class CreateBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'birthday' => ['sometimes', 'date'],
            'deadline' => ['required', 'before:2030-01-01'],
            'note' => ['sometimes', 'string'],
        ];
    }
}

test('date rules emit type string with format date', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateBookingRequest::class)->build();

    $properties = collect($requestBody->content[0]->schema->properties);

    foreach (['from', 'to', 'birthday', 'deadline'] as $name) {
        $property = $properties->firstWhere('property', $name);
        expect($property)->not->toBeNull()
            ->and($property->type)->toBe('string', "property [$name]")
            ->and($property->format)->toBe('date', "property [$name]");
    }
});

test('date_format rules with time components emit format date-time', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateBookingRequest::class)->build();

    $properties = collect($requestBody->content[0]->schema->properties);

    $startsAt = $properties->firstWhere('property', 'starts_at');
    expect($startsAt)->not->toBeNull()
        ->and($startsAt->type)->toBe('string')
        ->and($startsAt->format)->toBe('date-time');
});

test('no property is emitted with an invalid date or datetime type', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateBookingRequest::class)->build();

    $properties = collect($requestBody->content[0]->schema->properties);

    $invalid = $properties->filter(fn ($p) => in_array($p->type, ['date', 'datetime'], true));
    expect($invalid)->toBeEmpty();
});

test('non-date properties keep their primitive type without format', function () {
    $builder = new RequestBodyBuilder;
    $requestBody = $builder->requestClass(CreateBookingRequest::class)->build();

    $properties = collect($requestBody->content[0]->schema->properties);

    $note = $properties->firstWhere('property', 'note');
    expect($note)->not->toBeNull()
        ->and($note->type)->toBe('string')
        ->and($note->format)->toBe('@OA\Generator::UNDEFINED🙈');
});
