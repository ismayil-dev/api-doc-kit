<?php

namespace IsmayilDev\ApiDocKit\Processors;

use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiResource;
use IsmayilDev\ApiDocKit\Attributes\Responses\SuccessResponse;
use IsmayilDev\ApiDocKit\Entities\Entity;
use IsmayilDev\ApiDocKit\Entities\RouteItem;
use IsmayilDev\ApiDocKit\Helper\RouteHelper;
use IsmayilDev\ApiDocKit\Resolvers\EntityResolver;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Post;
use OpenApi\Generator;

class ApiResourceProcessor
{
    protected bool $usePluralEntity = false;

    public function __construct(
        private readonly RouteHelper $routeHelper,
        private readonly EntityResolver $entityResolver,
    ) {}

    public function __invoke(Analysis $analysis): void
    {
        $annotations = $analysis->annotations;

        /** @var Operation $annotation */
        foreach ($annotations as $annotation) {

            if (! $annotation instanceof ApiResource) {
                return;
            }

            $controllerWithNamespace = $this->getClassWithNameSpace($annotation);
            $route = $this->routeHelper->findByController($controllerWithNamespace);
            // @TODO check controller has a attribute
            if ($route === null) {
                return;
            }

            $path = "/{$route->path}";
            $description = null;
            $operationId = null;
            //            $annotation->path = "/{$route?->path}";
            $entity = $this->entityResolver->resolve($annotation->getModel());

            if ($annotation->description === Generator::UNDEFINED) {
                $description = $this->guessDescription($annotation, $route, $entity);
            }

            if ($annotation->operationId === Generator::UNDEFINED) {
                $operationId = $this->guessOperationId($annotation, $route, $entity);
            }

            $tags = $entity->tags();

            $resourceClass = match ($route->method) {
                'POST' => Post::class,
                'GET' => Get::class,
                default => throw new \RuntimeException("Unsupported method {$route->method}"),
            };

            $annotations->detach($annotation);

            $newAnnotation = new $resourceClass(
                path: $path,
                operationId: $operationId,
                description: $description,
                tags: $tags,
                responses: [
                    new SuccessResponse,
                ]
            );

            $annotations->attach($newAnnotation);
        }
    }

    protected function guessOperationId(
        Operation $annotation,
        RouteItem $route,
        Entity $entity
    ): string {
        $actionName = $this->guessActionName($annotation, $route, $entity);
        $entityName = $this->usePluralEntity ? $entity->getPluralName() : $entity->name();

        return Str::camel("{$actionName}{$entityName}");
    }

    protected function guessActionName(Operation $annotation, RouteItem $route, Entity $model): string
    {
        if (! $route->isSingleAction) {
            return $route->functionName;
        }

        $actionName = $annotation->_context->class;
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

    protected function guessDescription(Operation $annotation, RouteItem $route, Entity $entity): ?string
    {
        $actionName = $this->guessActionName($annotation, $route, $entity);

        return $entity->description($actionName, $this->usePluralEntity);
    }

    protected function getClassWithNameSpace(Operation $annotation): string
    {
        return "{$annotation->_context->namespace}\\{$annotation->_context->class}";
    }
}
