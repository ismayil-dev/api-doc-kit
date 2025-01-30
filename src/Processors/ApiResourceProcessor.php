<?php

namespace IsmayilDev\ApiDocKit\Processors;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiResource;
use IsmayilDev\ApiDocKit\Attributes\Responses\SuccessResponse;
use IsmayilDev\ApiDocKit\Builders\RequestBodyBuilder;
use IsmayilDev\ApiDocKit\Builders\RoutePathParameterBuilder;
use IsmayilDev\ApiDocKit\Entities\DocEntity;
use IsmayilDev\ApiDocKit\Entities\RouteItem;
use IsmayilDev\ApiDocKit\Mappers\RouteMapper;
use IsmayilDev\ApiDocKit\Resolvers\EntityResolver;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\Post;
use OpenApi\Generator;
use ReflectionClass;
use RuntimeException;

class ApiResourceProcessor
{
    protected bool $usePluralEntity = false;

    public function __construct(
        private readonly RouteMapper $routeMapper,
        private readonly EntityResolver $entityResolver,
        private readonly RoutePathParameterBuilder $routeParameterBuilder,
        private readonly RequestBodyBuilder $requestBodyBuilder,
    ) {}

    public function __invoke(Analysis $analysis): void
    {
        $annotations = $analysis->annotations;
        $annotationsToDetach = [];
        $annotationsToAttach = [];

        /** @var Operation $annotation */
        foreach ($annotations as $annotation) {
            if (! $annotation instanceof ApiResource) {
                continue;
            }

            $controllerWithNamespace = $this->getClassWithNameSpace($annotation);
            $route = $this->routeMapper->findByController($controllerWithNamespace, $annotation->_context->method);

            // @TODO check controller has a attribute
            if ($route === null) {
                $annotationsToDetach[] = $annotation;

                continue;
            }

            $path = "/{$route->path}";
            $description = null;
            $operationId = null;
            $entity = $this->entityResolver->resolve($annotation->getEntity());
            $this->usePluralEntity = $annotation->isList();

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
                'PATCH' => Patch::class,
                default => throw new RuntimeException("Unsupported method {$route->method}"),
            };

            // temporary hacky way to test response works or not
            // @TODO make this automatic, for testing purposes it is written like this
            if ($controllerWithNamespace === 'App\Http\Controllers\Orders\OrderRetrieveController') {
                $responseRef = '#/components/schemas/OrderResource';
            } else {
                $responseRef = null;
            }

            $requestBody = $this->getRequestBody($annotation, $controllerWithNamespace, $route);

            $newAnnotation = new $resourceClass(
                path: $path,
                operationId: $operationId,
                description: $description,
                requestBody: $requestBody,
                tags: $tags,
                responses: [
                    new SuccessResponse(ref: $responseRef),
                ]
            );

            if (! empty($route->parameters)) {
                $newAnnotation->parameters = $this->routeParameterBuilder
                    ->build($route, $entity)
                    ->toArray();
            }

            $annotationsToAttach[] = $newAnnotation;
            $annotationsToDetach[] = $annotation;
        }

        foreach ($annotationsToDetach as $itemToDetach) {
            $annotations->detach($itemToDetach);
        }

        foreach ($annotationsToAttach as $annotation) {
            $annotations->attach($annotation);
        }
    }

    protected function guessOperationId(
        Operation $annotation,
        RouteItem $route,
        DocEntity $entity
    ): string {
        $actionName = $this->guessActionName($annotation, $route, $entity);
        $entityName = $this->usePluralEntity ? $entity->getPluralName() : $entity->name();

        return Str::camel("{$actionName}{$entityName}");
    }

    protected function guessActionName(Operation $annotation, RouteItem $route, DocEntity $model): string
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

    protected function guessDescription(Operation $annotation, RouteItem $route, DocEntity $entity): ?string
    {
        $actionName = $this->guessActionName($annotation, $route, $entity);

        return $entity->description($actionName, $this->usePluralEntity);
    }

    protected function getClassWithNameSpace(Operation $annotation): string
    {
        return "{$annotation->_context->namespace}\\{$annotation->_context->class}";
    }

    protected function getRequestBody(Operation $annotation, string $controller, RouteItem $route)
    {
        $requestBody = null;
        $requestClass = $annotation->getRequestClass() ?? $this->extractRequestClassFromController($controller, $route);

        if (! empty($requestClass)) {
            $requestBody = $this->requestBodyBuilder->requestClass($requestClass)->build();
        }

        return $requestBody;
    }

    protected function extractRequestClassFromController(string $controller, RouteItem $route): ?string
    {
        $reflectedClass = new ReflectionClass($controller);
        $method = $reflectedClass->getMethod($route->functionName);
        $parameters = $method->getParameters();
        $findRequestClass = array_filter($parameters, function ($parameter) {
            return is_subclass_of(strtolower($parameter->getType()->getName()), Request::class);
        });

        if (! empty($findRequestClass)) {
            return head($findRequestClass)->getType()->getName();
        }

        return null;
    }
}
