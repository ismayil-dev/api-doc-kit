<?php

namespace IsmayilDev\ApiDocKit\Entities;

class RouteItem
{
    public function __construct(
        public string $className,
        public string $method,
        public string $path,
        public string $functionName,
        public string $name,
        public $parameters = [],
        public $isSingleAction = false,
    ) {}
}
