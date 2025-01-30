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
                return str_starts_with($route->getControllerClass(), 'App\\');
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

    public function findByController(string $controller, string $functionName): ?RouteItemEntity
    {
        return $this->routes->first(function (RouteItemEntity $route) use ($controller, $functionName) {
            return $route->className === $controller && $route->functionName === $functionName;
        });
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
