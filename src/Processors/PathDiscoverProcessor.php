<?php

namespace IsmayilDev\LaravelDocKit\Processors;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use IsmayilDev\LaravelDocKit\Attributes\Resources\PostResource;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;

class PathDiscoverProcessor
{
    public function __invoke(Analysis $analysis)
    {
        $routes = $this->getRoutes();
        dd($routes);
        $annotations = $analysis->annotations;

        /** @var Operation $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation instanceof PostResource) {
                $classWithNamespace = "{$annotation->_context->namespace}\\{$annotation->_context->class}";
                $findRoute = $routes->get($classWithNamespace);
                $annotation->description = 'Ismayil Aliyev';
                $annotation->path = "/{$findRoute['path']}";
            }
        }
    }

    private function getRoutes(): Collection
    {
        return collect(Route::getRoutes())
            ->filter(function ($route) {
                return str_starts_with($route->getActionName(), 'App\\');
            })
            ->mapWithKeys(function ($route) {
                $action = $route->getActionName();
                if (str_contains($action, '@')) {
                    [$controller, $functionName] = explode('@', $action);
                } else {
                    $controller = $action;
                    $functionName = '__invoke';
                }

                return [
                    $controller => [
                        'path' => $route->uri(),
                        'method' => implode('|', $route->methods()),
                        'function_name' => $functionName,
                        'name' => $route->getName(),
                    ],
                ];
            })
            ->unique();
    }
}
