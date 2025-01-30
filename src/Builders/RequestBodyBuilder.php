<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Builders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;

class RequestBodyBuilder
{
    protected string $requestClass;

    protected Request|FormRequest $instance;

    private const PROCESSABLE_RULES = [
        'required',
        'string',
        'boolean',
        'integer',
        'numeric',
        'array',
        'file',
        'image',
        'mimes',
        'mimetypes',
        'in',
        'not_in',
        'size',
        'min',
        'max',
        'between',
        'confirmed',
        'different',
        'email',
        'exists',
        'digits',
        'digits_between',
        'ip',
        'ipv4',
        'ipv6',
        'mac_address',
        'regex',
        'required_if',
        'required_with',
        'required_with_all',
        'required_without',
        'required_without_all',
        'same',
        'unique',
        'url',
        'timezone',
    ];

    public function requestClass(string $requestClass): self
    {
        if (! is_subclass_of($requestClass, Request::class)) {
            throw new InvalidArgumentException("Request class must be a subclass of Illuminate\Http\Request");
        }

        $this->requestClass = $requestClass;
        $this->instance = new $requestClass;

        return $this;
    }

    public function build(): RequestBody
    {
        if (! isset($this->requestClass) | ! isset($this->instance)) {
            throw new InvalidArgumentException('Request class and instance must be set');
        }

        $rules = $this->instance->rules();

        return $this->buildRequestBody($rules);
    }

    private function buildRequestBody(array $attributes): RequestBody
    {
        return new RequestBody(
            request: $this->requestClass,
            description: 'Request body',
            content: new MediaType(
                mediaType: 'application/json',
                schema: new Schema(
                    required: $this->prepareRequiredParameters($attributes),
                    properties: $this->prepareParameter($attributes),
                    type: OpenApiPropertyType::OBJECT->value,
                )
            )
        );
    }

    private function prepareRequiredParameters(array $attributes): array
    {
        $required = [];

        // TODO Find more efficient way to do this
        foreach ($attributes as $key => $rule) {
            $rule = $this->prepareRules($rule);

            if (in_array('required', $rule, true)) {
                $required[] = $key;
            }
        }

        return $required;
    }

    private function prepareParameter(array $attributes): array
    {
        $parameters = [];

        foreach ($attributes as $key => $rule) {
            $rule = $this->prepareRules($rule);

            $parameters[] = $this->buildParameters($key, $rule);
        }

        return $parameters;
    }

    private function buildParameters(string $name, array $rules): Property
    {
        $type = $this->getPrimitiveType($rules);

        return new Property(
            property: $name,
            description: 'The '.Str::title($name),
            type: $type->value,
            example: "<$type->value>",
            nullable: ! empty($rules['nullable']),
        );
    }

    /**
     * @param  array<string>  $rules
     */
    private function getPrimitiveType(array $rules): OpenApiPropertyType
    {
        $typeMap = $this->typeMap();

        foreach ($typeMap as $type => $ruleSet) {
            foreach ($rules as $rule) {
                $baseRule = explode(':', $rule)[0];
                if (in_array($baseRule, $ruleSet, true)) {
                    return OpenApiPropertyType::from($type);
                }
            }
        }

        return OpenApiPropertyType::UNDEFINED;
    }

    /**
     * @return array<string, array<string>>
     */
    private function typeMap(): array
    {
        return [
            OpenApiPropertyType::STRING->value => [
                'string', 'ulid', 'uuid', 'email', 'ip', 'ipv4', 'ipv6', 'url', 'active_url',
                'regex', 'alpha', 'alpha_num', 'alpha_dash', 'ascii', 'lowercase', 'uppercase',
                'password', 'timezone', 'starts_with', 'ends_with', 'unique', 'exists',
            ],
            OpenApiPropertyType::INTEGER->value => [
                'integer', 'digits', 'digits_between', 'int',
            ],
            OpenApiPropertyType::NUMBER->value => [
                'numeric', 'float', 'decimal',
            ],
            OpenApiPropertyType::BOOLEAN->value => [
                'boolean', 'accepted', 'declined', 'filled',
            ],
            OpenApiPropertyType::ARRAY->value => [
                'array', 'json',
            ],
            OpenApiPropertyType::OBJECT->value => [
                'object', 'json',
            ],
            OpenApiPropertyType::FILE->value => [
                'file', 'image', 'mimes', 'mimetypes', 'max', 'min', 'dimensions',
            ],
            OpenApiPropertyType::DATE->value => [
                'date', 'date_equals', 'date_format', 'before', 'before_or_equal', 'after', 'after_or_equal',
            ],
            OpenApiPropertyType::DATETIME->value => [
                'date', 'date_format', 'before', 'before_or_equal', 'after', 'after_or_equal',
            ],
        ];
    }

    private function prepareRules(string|array $rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        return array_filter($rules, 'is_string');
    }
}
