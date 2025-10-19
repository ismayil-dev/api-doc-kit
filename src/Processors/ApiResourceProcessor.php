<?php

namespace IsmayilDev\ApiDocKit\Processors;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;
use IsmayilDev\ApiDocKit\Attributes\Responses\ApiResponse;
use IsmayilDev\ApiDocKit\Attributes\Responses\JsonCollectionContent;
use IsmayilDev\ApiDocKit\Attributes\Responses\JsonErrorContent;
use IsmayilDev\ApiDocKit\Attributes\Responses\JsonPaginatedContent;
use IsmayilDev\ApiDocKit\Http\Requests\RequestBodyBuilder;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CreatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\EmptyResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\PaginatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\UpdatedResponse;
use IsmayilDev\ApiDocKit\Models\DocEntity;
use IsmayilDev\ApiDocKit\Models\EntityResolver;
use IsmayilDev\ApiDocKit\Routes\RouteItem;
use IsmayilDev\ApiDocKit\Routes\RouteMapper;
use IsmayilDev\ApiDocKit\Routes\RoutePathParameterBuilder;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Patch;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Put;
use OpenApi\Generator;
use ReflectionClass;
use ReflectionException;
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
            if (! $annotation instanceof ApiEndpoint) {
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
            $responseType = $this->detectResponseType($controllerWithNamespace, $route);
            $this->usePluralEntity = $this->isCollectionEntity($responseType);

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
                'PUT' => Put::class,
                'DELETE' => Delete::class,
                default => throw new RuntimeException("Unsupported method {$route->method}"),
            };

            if ($annotation->getResponseEntity() !== null) {
                $responseRef = '#/components/schemas/'.$annotation->getResponseEntity();
            } else {
                $responseRef = '#/components/schemas/'.$entity->getResourceName();
            }

            $response = $this->getResponse($responseType, $responseRef);

            $requestBody = $this->getRequestBody($annotation, $controllerWithNamespace, $route);

            $newAnnotation = new $resourceClass(
                path: $path,
                operationId: $operationId,
                description: $description,
                requestBody: $requestBody,
                tags: $tags,
                responses: [
                    $response,
                    ...$this->getErrorResponses(),
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

    protected function isCollectionEntity(string $entityResource): bool
    {
        return in_array($entityResource, [
            PaginatedResponse::class,
            CollectionResponse::class,
        ]);
    }

    /**
     * @throws ReflectionException
     */
    protected function detectResponseType(string $controller, RouteItem $route): ?string
    {
        $reflectedClass = new ReflectionClass($controller);
        $method = $reflectedClass->getMethod($route->functionName);
        $returnType = $method->getReturnType();

        if (! $returnType) {
            throw new RuntimeException("Method {$route->functionName} in {$controller} does not have a return type");
        }

        $returnTypeName = $returnType instanceof \ReflectionNamedType
            ? $returnType->getName()
            : null;

        if (! $returnTypeName) {
            throw new RuntimeException("Method {$route->functionName} in {$controller} does not have a return type");
        }

        return $returnTypeName;
    }

    /**
     * @throws ReflectionException
     */
    protected function getResponse(string $responseType, string $responseRef): ?ApiResponse
    {
        return match ($responseType) {
            CreatedResponse::class => new ApiResponse(201, 'Created response', $responseRef),
            CollectionResponse::class => new ApiResponse(
                statusCode: 200,
                description: 'Collection response',
                content: new JsonCollectionContent($responseRef)
            ),
            PaginatedResponse::class => new ApiResponse(
                statusCode: 200,
                description: 'Paginated response',
                content: new JsonPaginatedContent($responseRef)
            ),
            EmptyResponse::class => new ApiResponse(204, 'Empty response', null),
            SingleResourceResponse::class => new ApiResponse(200, 'Single resource response', $responseRef),
            UpdatedResponse::class => new ApiResponse(200, 'Updated response', $responseRef),
            default => null,
        };
    }

    protected function getErrorResponses(): array
    {
        return [
            new ApiResponse(
                statusCode: 400,
                description: 'Bad request',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 401,
                description: 'Unauthorized',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 403,
                description: 'Forbidden',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 404,
                description: 'Not found',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 405,
                description: 'Method not allowed',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 422,
                description: 'Validation failed',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 429,
                description: 'Too many requests',
                content: new JsonErrorContent
            ),
            new ApiResponse(
                statusCode: 500,
                description: 'Internal server error',
                content: new JsonErrorContent
            ),
        ];
    }
}
