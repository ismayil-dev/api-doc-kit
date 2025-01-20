<?php

namespace IsmayilDev\LaravelDocKit\Handlers;

use IsmayilDev\LaravelDocKit\Resolvers\EntityResolver;
use OpenApi\Generator;

class ListResourceHandler extends BaseResourceHandler
{
    public function process(): void
    {
        $controllerWithNamespace = $this->getClassWithNameSpace();

        $route = $this->routeHelper->findByController($controllerWithNamespace);

        $this->annotation->path = "/{$route?->path}";

        $entity = EntityResolver::fromModel($this->annotation->getModel());

        if ($this->annotation->operationId === Generator::UNDEFINED || empty($this->annotation->operationId)) {
            $this->annotation->operationId = $this->guessOperationId(
                route: $route,
                entity: $entity,
                isPluralEntity: true
            );
        }

        $this->annotation->tags = $entity->tags();
    }
}
