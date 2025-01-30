<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Builders;

use Illuminate\Support\Collection;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Routes\RoutePathIntegerParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Routes\RoutePathStringParameter;
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiResource;
use IsmayilDev\ApiDocKit\Entities\DocEntity;
use IsmayilDev\ApiDocKit\Entities\RouteItem;
use IsmayilDev\ApiDocKit\Entities\RoutePathParameter;
use IsmayilDev\ApiDocKit\Routes\RoutePathParameterResolver;
use ReflectionAttribute;
use ReflectionClass;

class RoutePathParameterBuilder
{
    public function __construct(private RoutePathParameterResolver $pathResolver) {}

    public function build(RouteItem $route, DocEntity $entity): Collection
    {
        $parameters = collect();

        $reflection = new ReflectionClass($route->className);
        $method = $reflection->getMethod($route->functionName);
        $resourceAttributes = collect($method->getAttributes())
            ->filter(function (ReflectionAttribute $attribute) {
                return $attribute->getName() === ApiResource::class;
            });

        /** @var ReflectionAttribute $attribute */
        foreach ($resourceAttributes as $attribute) {
            $arguments = $attribute->getArguments();
            if (array_key_exists('parameters', $arguments)) {
                $parameters->push(...$arguments['parameters']);
            }
        }

        $routePathParameters = array_filter($route->parameters, function ($pathParameter) use ($parameters) {
            return is_null($parameters->firstWhere('name', $pathParameter['name']));
        });

        if (! empty($routePathParameters)) {
            $routeParameters = $this->pathResolver->resolve($routePathParameters, $entity)->get();
            $mappedParameters = $this->mapToOpenApiAttribute($routeParameters);
            $parameters->push(...$mappedParameters);
        }

        return $parameters;
    }

    private function mapToOpenApiAttribute(Collection $routeParameters): Collection
    {
        return $routeParameters->map(function (RoutePathParameter $parameter) {
            $propertyClass = match ($parameter->type) {
                OpenApiPropertyType::INTEGER => RoutePathIntegerParameter::class,
                default => RoutePathStringParameter::class,
            };

            return new $propertyClass(
                name: $parameter->name,
                description: $parameter->description,
                required: ! $parameter->optional,
                example: $parameter->example,
            );
        });
    }
}
