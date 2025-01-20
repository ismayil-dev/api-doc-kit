<?php

namespace IsmayilDev\LaravelDocKit\Processors;

use Illuminate\Support\Collection;
use IsmayilDev\LaravelDocKit\Attributes\Resources\ListResource;
use IsmayilDev\LaravelDocKit\Attributes\Resources\PostResource;
use IsmayilDev\LaravelDocKit\Entities\RouteItem;
use IsmayilDev\LaravelDocKit\Handlers\ListResourceHandler;
use IsmayilDev\LaravelDocKit\Handlers\PostResourceHandler;
use IsmayilDev\LaravelDocKit\Helper\RouteHelper;
use OpenApi\Analysis;
use OpenApi\Annotations\Operation;

readonly class ResourceDiscoverProcessor
{
    private RouteHelper $routeHelper;

    public function __construct()
    {
        $this->routeHelper = new RouteHelper;
        $this->routeHelper->prepareRoutes();
    }

    public function __invoke(Analysis $analysis): void
    {
        /** @var Collection<RouteItem> $routes */
        $annotations = $analysis->annotations;

        /** @var Operation $annotation */
        foreach ($annotations as $annotation) {

            $handler = match (get_class($annotation)) {
                PostResource::class => new PostResourceHandler($annotation),
                ListResource::class => new ListResourceHandler($annotation),
                default => null,
            };

            $handler?->process();
        }
    }
}
