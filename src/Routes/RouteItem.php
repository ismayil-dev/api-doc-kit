<?php

namespace IsmayilDev\ApiDocKit\Routes;

class RouteItem
{
    /**
     * @param  array<int, array{name: string, optional: bool}>  $parameters
     */
    public function __construct(
        public string $className,
        public string $method,
        public string $path,
        public string $functionName,
        public string $name,
        public array $parameters = [],
        public bool $isSingleAction = false,
    ) {}
}
