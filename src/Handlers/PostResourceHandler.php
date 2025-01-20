<?php

namespace IsmayilDev\LaravelDocKit\Handlers;

use IsmayilDev\LaravelDocKit\Resolvers\EntityResolver;
use OpenApi\Generator;

class PostResourceHandler extends BaseResourceHandler
{
    public function process(): void
    {
        $controllerWithNamespace = $this->getClassWithNameSpace();
        $route = $this->routeHelper->findByController($controllerWithNamespace);

        // @TODO check controller has a attribute
        if ($route === null) {
            return;
        }

        $this->annotation->path = "/{$route?->path}";
        $entity = EntityResolver::fromModel($this->annotation->getModel());

        if ($this->annotation->description === Generator::UNDEFINED) {
            $this->annotation->description = $this->guessDescription(
                $route,
                $entity
            );
        }

        if ($this->annotation->operationId === Generator::UNDEFINED) {
            $this->annotation->operationId = $this->guessOperationId(
                $route,
                $entity
            );
        }

        $this->annotation->tags = $entity->tags();
    }
}
