<?php

namespace IsmayilDev\ApiDocKit\Routes;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use IsmayilDev\ApiDocKit\Routes\RouteItem as RouteItemEntity;

class RouteMapper
{
    /** @var Collection<RouteItemEntity> */
    public Collection $routes;

    public function __construct()
    {
        $this->prepareRoutes();
    }

    public function prepareRoutes(): void
    {
        $this->routes = collect(Route::getRoutes()->getRoutes())
            ->filter(function (LaravelRoute $route) {
                return $this->shouldIncludeRoute($route);
            })->map(function (LaravelRoute $route) {
                [$controller, $functionName] = $this->resolveControllerWithFunction($route);
                $symfonyRoute = $route->toSymfonyRoute();
                $parameters = $this->getParameters(
                    parameters: $route->parameterNames(),
                    defaults: $symfonyRoute->getDefaults(),
                );

                return new RouteItemEntity(
                    className: $controller,
                    method: head($route->methods()),
                    path: substr($symfonyRoute->getPath(), 1),
                    functionName: $functionName,
                    name: $route->getName(),
                    parameters: $parameters,
                    isSingleAction: $functionName === '__invoke',
                );
            })
            ->values();
    }

    /**
     * Three-layer filtering system to determine if a route should be included
     *
     * Layer 1: Route file filtering (only include routes from specified files)
     * Layer 2: Path pattern exclusion (exclude routes matching regex patterns)
     * Layer 3: Controller-less route skipping (skip closure routes)
     */
    protected function shouldIncludeRoute(LaravelRoute $route): bool
    {
        // Layer 1: Route file filtering
        if (! $this->isFromAllowedRouteFile($route)) {
            return false;
        }

        // Layer 2: Path pattern exclusion
        if ($this->matchesExclusionPattern($route)) {
            return false;
        }

        // Layer 3: Controller-less route skipping
        if ($this->shouldSkipControllerLess($route)) {
            return false;
        }

        // Legacy filter: Only include App\\ controllers
        return str_starts_with($route->getControllerClass(), 'App\\');
    }

    /**
     * Layer 1: Check if route is from an allowed route file
     */
    protected function isFromAllowedRouteFile(LaravelRoute $route): bool
    {
        $allowedFiles = config('api-doc-kit.routes.files', ['api.php']);

        // Get the route action to check the file
        $action = $route->getAction();

        // If no file information is available, allow the route (backward compatibility)
        if (! isset($action['file'])) {
            return true;
        }

        // Check if the route file matches any allowed file
        foreach ($allowedFiles as $allowedFile) {
            if (str_ends_with($action['file'], $allowedFile)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Layer 2: Check if route path matches any exclusion pattern
     */
    protected function matchesExclusionPattern(LaravelRoute $route): bool
    {
        $excludePatterns = config('api-doc-kit.routes.exclude_paths', []);

        if (empty($excludePatterns)) {
            return false;
        }

        $path = $route->uri();

        foreach ($excludePatterns as $pattern) {
            if (preg_match('/'.$pattern.'/', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Layer 3: Check if controller-less routes should be skipped
     */
    protected function shouldSkipControllerLess(LaravelRoute $route): bool
    {
        $skipControllerLess = config('api-doc-kit.routes.skip_controller_less', true);

        if (! $skipControllerLess) {
            return false;
        }

        // Check if route has a controller
        $controllerClass = $route->getControllerClass();

        // If getControllerClass() returns null or empty string, it's a controller-less route
        return empty($controllerClass);
    }

    public function findByController(string $controller, string $functionName): ?RouteItemEntity
    {
        return $this->routes->first(function (RouteItemEntity $route) use ($controller, $functionName) {
            return $route->className === $controller && $route->functionName === $functionName;
        });
    }

    /**
     * Get all mapped routes
     *
     * @return array<RouteItemEntity>
     */
    public function getAllRoutes(): array
    {
        return $this->routes->toArray();
    }

    protected function resolveControllerWithFunction(LaravelRoute $route): array
    {
        $action = $route->getActionName();

        if (str_contains($action, '@')) {
            return explode('@', $action);
        }

        return [$action, '__invoke'];
    }

    protected function getParameters(array $parameters, array $defaults): array
    {
        return array_map(function ($parameter) use ($defaults) {
            return [
                'name' => $parameter,
                'optional' => array_key_exists($parameter, $defaults),
            ];
        }, $parameters);
    }
}
