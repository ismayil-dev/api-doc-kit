<?php

namespace IsmayilDev\ApiDocKit\Processors;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;
use IsmayilDev\ApiDocKit\Attributes\Responses\ApiResponse;
use IsmayilDev\ApiDocKit\Exceptions\MissingApiEndpointException;
use IsmayilDev\ApiDocKit\Http\Requests\RequestBodyBuilder;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\PaginatedResponse;
use IsmayilDev\ApiDocKit\Http\Responses\ResponseSchemaBuilder;
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
        private readonly ResponseSchemaBuilder $responseSchemaBuilder,
    ) {}

    public function __invoke(Analysis $analysis): void
    {
        $annotations = $analysis->annotations;
        $annotationsToDetach = [];
        $annotationsToAttach = [];
        $processedRoutes = [];

        /** @var Operation $annotation */
        foreach ($annotations as $annotation) {
            if (! $annotation instanceof ApiEndpoint) {
                continue;
            }

            $controllerWithNamespace = $this->getClassWithNameSpace($annotation);
            $route = $this->routeMapper->findByController($controllerWithNamespace, $annotation->_context->method);

            if ($route === null) {
                $annotationsToDetach[] = $annotation;

                continue;
            }

            // Track this route as processed
            $processedRoutes[] = $route;

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

            $customResponse = $annotation->getResponseEntity();
            if ($customResponse !== null) {
                if (class_exists($customResponse)) {
                    $responseRef = '#/components/schemas/'.class_basename($customResponse);
                } else {
                    $responseRef = '#/components/schemas/'.$customResponse;
                }
            } else {
                $responseRef = '#/components/schemas/'.$entity->getResourceName();
            }

            $response = $this->getResponse($responseType, $responseRef, $annotation);

            $requestBody = $this->getRequestBody($annotation, $controllerWithNamespace, $route);

            $newAnnotation = new $resourceClass(
                path: $path,
                operationId: $operationId,
                description: $description,
                requestBody: $requestBody,
                tags: $tags,
                responses: [
                    $response,
                    ...$this->getErrorResponses($route->method, $annotation),
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

        // Validate that all routes have #[ApiEndpoint] attribute
        $this->validateAllRoutesHaveAttributes($processedRoutes);
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
    protected function getResponse(string $responseType, string $responseRef, ?Operation $annotation = null): ?ApiResponse
    {
        // Check if annotation has custom success response schema
        $customSchema = null;
        if ($annotation instanceof ApiEndpoint && $annotation->getSuccessResponseSchema() !== null) {
            $customSchema = $annotation->getSuccessResponseSchema();
        }

        // Use ResponseSchemaBuilder to build the response
        return $this->responseSchemaBuilder->buildSuccessResponse(
            responseType: $responseType,
            responseRef: $responseRef,
            customSchema: $customSchema
        );
    }

    /**
     * Get default error status codes based on HTTP method
     *
     * @return array<int>
     */
    protected function getDefaultErrorCodesForMethod(string $httpMethod): array
    {
        $method = strtoupper($httpMethod);

        // Check for config override first
        $configOverride = config("api-doc-kit.responses.error.defaults_per_method.{$method}");
        if (is_array($configOverride)) {
            return $configOverride;
        }

        // Use package defaults
        return match ($method) {
            'GET' => [
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_FORBIDDEN,
                Response::HTTP_NOT_FOUND,
                Response::HTTP_TOO_MANY_REQUESTS,
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            'POST' => [
                Response::HTTP_BAD_REQUEST,
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_FORBIDDEN,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::HTTP_TOO_MANY_REQUESTS,
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            'PATCH', 'PUT' => [
                Response::HTTP_BAD_REQUEST,
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_FORBIDDEN,
                Response::HTTP_NOT_FOUND,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::HTTP_TOO_MANY_REQUESTS,
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            'DELETE' => [
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_FORBIDDEN,
                Response::HTTP_NOT_FOUND,
                Response::HTTP_TOO_MANY_REQUESTS,
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            default => [
                Response::HTTP_BAD_REQUEST,
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_FORBIDDEN,
                Response::HTTP_NOT_FOUND,
                Response::HTTP_METHOD_NOT_ALLOWED,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::HTTP_TOO_MANY_REQUESTS,
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
        };
    }

    protected function getErrorResponses(string $httpMethod, ?Operation $annotation = null): array
    {
        $defaultCodes = $this->getDefaultErrorCodesForMethod($httpMethod);

        // Check if annotation has ErrorResponses attribute
        if ($annotation instanceof ApiEndpoint && $annotation->getErrorResponses() !== null) {
            $errorResponsesAttr = $annotation->getErrorResponses();
            $defaultCodes = $errorResponsesAttr->filter($defaultCodes);
        }

        // Get custom error response schemas from annotation if available
        $customSchemas = [];
        if ($annotation instanceof ApiEndpoint && $annotation->getErrorResponseSchemas() !== null) {
            $customSchemas = $annotation->getErrorResponseSchemas();
        }

        // Build error responses using ResponseSchemaBuilder
        $errorResponses = [];
        foreach ($defaultCodes as $statusCode) {
            $customSchema = $customSchemas[$statusCode] ?? null;
            $errorResponses[] = $this->responseSchemaBuilder->buildErrorResponse(
                statusCode: $statusCode,
                customSchema: $customSchema
            );
        }

        return $errorResponses;
    }

    /**
     * Validate that all routes have #[ApiEndpoint] attribute
     *
     * @param  array<RouteItem>  $processedRoutes
     */
    protected function validateAllRoutesHaveAttributes(array $processedRoutes): void
    {
        // Get all application routes
        $allRoutes = $this->routeMapper->getAllRoutes();

        // Find routes that weren't processed (missing #[ApiEndpoint])
        $missingRoutes = array_filter($allRoutes, function (RouteItem $route) use ($processedRoutes) {
            // Check if this route was processed
            foreach ($processedRoutes as $processedRoute) {
                if ($processedRoute->path === $route->path &&
                    $processedRoute->method === $route->method &&
                    $processedRoute->className === $route->className) {
                    return false; // Route was processed
                }
            }

            // Check if route should be excluded
            if ($this->shouldExcludeRoute($route)) {
                return false;
            }

            return true; // Route is missing #[ApiEndpoint]
        });

        if (empty($missingRoutes)) {
            return; // All routes are documented
        }

        $strictMode = config('api-doc-kit.schema.strict_mode', false);

        if ($strictMode) {
            throw MissingApiEndpointException::forRoutes(array_values($missingRoutes));
        }

        // Log warnings for each missing route
        foreach ($missingRoutes as $route) {
            Log::warning(
                "Route missing #[ApiEndpoint] attribute: {$route->method} /{$route->path} ({$route->className}@{$route->functionName})"
            );
        }
    }

    /**
     * Check if route should be excluded from validation
     */
    protected function shouldExcludeRoute(RouteItem $route): bool
    {
        $path = $route->path;

        // Built-in exclusions
        $builtInPatterns = [
            '^_debugbar',      // Laravel Debugbar
            '^telescope',      // Laravel Telescope
            '^horizon',        // Laravel Horizon
            '^(up|health)$',   // Health checks
        ];

        foreach ($builtInPatterns as $pattern) {
            if (preg_match("/{$pattern}/", $path)) {
                return true;
            }
        }

        // User-configured exclusions
        $userPatterns = config('api-doc-kit.documentation.exclude_patterns', []);
        foreach ($userPatterns as $pattern) {
            if (preg_match("/{$pattern}/", $path)) {
                return true;
            }
        }

        return false;
    }
}
