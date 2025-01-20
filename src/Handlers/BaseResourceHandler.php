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

    protected bool $usePluralEntity = false;

    public function __construct(protected Operation $annotation)
    {
        $this->routeHelper = new RouteHelper;
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
        $entityName = $this->usePluralEntity ? $entity->getPluralName() : $entity->name();

        return Str::camel("{$actionName}{$entityName}");
    }

    protected function guessActionName(RouteItem $route, Entity $model): string
    {
        if (! $route->isSingleAction) {
            return $route->functionName;
        }

        $actionName = $this->annotation->_context->class;
        $controllerName = $actionName;

        if (Str::contains($controllerName, 'controller', true)) {
            $actionName = Str::replace(
                search: 'controller',
                replace: '',
                subject: $controllerName,
                caseSensitive: false
            );
        }

        $possibleModelNames = [$model->name(), Str::plural($model->name())];

        if (Str::contains($controllerName, $possibleModelNames, true)) {
            $actionName = Str::replace(
                search: $possibleModelNames,
                replace: '',
                subject: $actionName,
                caseSensitive: false
            );
        }

        return $actionName;
    }

    protected function getClassWithNameSpace(): string
    {
        return "{$this->annotation->_context->namespace}\\{$this->annotation->_context->class}";
    }
}
