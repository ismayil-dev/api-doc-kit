<?php

namespace IsmayilDev\LaravelDocKit\Handlers;

use Illuminate\Support\Str;
use IsmayilDev\LaravelDocKit\Entities\Entity;
use IsmayilDev\LaravelDocKit\Entities\RouteItem;
use IsmayilDev\LaravelDocKit\Helper\RouteHelper;
use OpenApi\Annotations\Operation;

abstract class BaseResourceHandler
{
    protected RouteHelper $routeHelper;

    public function __construct(protected Operation $annotation)
    {
        $this->routeHelper = new RouteHelper;
        $this->routeHelper->prepareRoutes();
    }

    abstract public function process(): void;

    protected function guessDescription(RouteItem $route, Entity $entity): ?string
    {
        $actionName = $this->guessActionName($route, $entity);

        return $entity->description($actionName);
    }

    protected function guessOperationId(RouteItem $route, Entity $entity): string
    {
        $actionName = $this->guessActionName($route, $entity);

        return Str::camel("{$actionName}{$entity->name()}");
    }

    protected function guessActionName(RouteItem $route, Entity $model): string
    {
        if ($route->isSingleAction) {
            return $route->functionName;
        }

        $actionName = $route->name;
        $controllerName = $route->className;

        if (Str::contains($controllerName, 'controller', true)) {
            $actionName = Str::replace(
                search: 'controller',
                replace: '',
                subject:  $controllerName,
                caseSensitive:  false
            );
        }

        $possibleModelNames = [$model->name(), Str::plural($model->name())];

        if (Str::contains($controllerName, $possibleModelNames, true)) {
            $actionName = Str::replace(
                search: $possibleModelNames,
                replace: '',
                subject:  $actionName,
                caseSensitive:  false
            );
        }

        return $actionName;
    }

    protected function getClassWithNameSpace(): string
    {
        return "{$this->annotation->_context->namespace}\\{$this->annotation->_context->class}";
    }
}