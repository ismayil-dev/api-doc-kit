<?php

namespace IsmayilDev\LaravelDocKit\Handlers;

use IsmayilDev\LaravelDocKit\Resolvers\EntityResolver;
use OpenApi\Generator;

class ListResourceHandler extends BaseResourceHandler
{
    protected bool $usePluralEntity = true;

    public function process(): void
    {
        $controllerWithNamespace = $this->getClassWithNameSpace();

        $route = $this->routeHelper->findByController($controllerWithNamespace);

        $this->annotation->path = "/{$route?->path}";

        $entity = EntityResolver::fromModel($this->annotation->getModel());

        if ($this->annotation->description === Generator::UNDEFINED) {
            $this->annotation->description = $this->guessDescription(
                $route,
                $entity
            );
        }

        if ($this->annotation->operationId === Generator::UNDEFINED || empty($this->annotation->operationId)) {
            $this->annotation->operationId = $this->guessOperationId(
                route: $route,
                entity: $entity,
            );
        }

        $this->annotation->tags = $entity->tags();
    }
}
