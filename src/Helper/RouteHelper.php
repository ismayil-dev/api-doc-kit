<?php

namespace IsmayilDev\ApiDocKit\Helper;

use Illuminate\Routing\Route as RouteItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use IsmayilDev\ApiDocKit\Entities\RouteItem as RouteItemEntity;

class RouteHelper
{
    /** @var Collection<RouteItem> */
    public Collection $routes;

    public function __construct()
    {
        $this->prepareRoutes();
    }

    public function prepareRoutes(): void
    {
        $this->routes = collect(Route::getRoutes())
            ->filter(function ($route) {
                // @TODO add support for custom namespaces
                return str_starts_with($route->getActionName(), 'App\\');
            })
            ->mapWithKeys(function (RouteItem $route) {
                [$controller, $functionName] = $this->resolveControllerWithFunction($route);

                return [$controller => new RouteItemEntity(
                    className: $controller,
                    method: implode('|', $route->methods()),
                    path: $route->uri(),
                    functionName: $functionName,
                    name: $route->getName(),
                    parameters: $route->parameterNames(),
                    isSingleAction: $functionName === '__invoke',
                )];
            })
            ->unique()
            ->values();
    }

    public function findByController(string $controller): ?RouteItemEntity
    {
        return $this->routes->first(fn (RouteItemEntity $route) => $route->className === $controller);
    }

    protected function resolveControllerWithFunction(RouteItem $route): array
    {
        $action = $route->getActionName();

        if (str_contains($action, '@')) {
            return explode('@', $action);
        }

        return [$action, '__invoke'];
    }
}
