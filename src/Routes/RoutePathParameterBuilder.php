<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Routes;

use Illuminate\Support\Collection;
use IsmayilDev\ApiDocKit\Entities\Entity;
use IsmayilDev\ApiDocKit\Entities\RouteItem;
use OpenApi\Attributes\Get;
use ReflectionClass;

class RoutePathParameterBuilder
{
    public Collection $parameters;

    public function __construct(RoutePathParameterResolver $pathResolver)
    {
        $this->parameters = collect();
    }

    public function build(RouteItem $route, Entity $entity): self
    {
        $reflection = new ReflectionClass($route->className);
        $method = $reflection->getMethod($route->functionName);
        $resourceAttributes = collect($method->getAttributes());
        $attribute = $method->getAttributes()[0];
        dd($attribute);

        //        dd(is_subclass_of($resourceAttributes->first()->getName(), Get::class));

        //        $resourceArguments = $resourceAttribute->getArguments();
        //
        //        if (array_key_exists('parameters', $resourceArguments)) {
        //            dd($resourceArguments['parameters'][0]);
        //        }

        return $this;
    }

    public function buildCoreParameter(object $pathReflectionObject)
    {
        //        return match (get_class($pathReflectionObject)) {
        //
        //        };
    }
}
