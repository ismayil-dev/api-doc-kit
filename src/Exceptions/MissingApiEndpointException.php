<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Exceptions;

use Exception;
use IsmayilDev\ApiDocKit\Routes\RouteItem;

/**
 * Thrown when API routes are missing #[ApiEndpoint] attribute in strict mode
 */
class MissingApiEndpointException extends Exception
{
    /**
     * Create exception for multiple missing routes
     *
     * @param  array<RouteItem>  $missingRoutes
     */
    public static function forRoutes(array $missingRoutes): self
    {
        $count = count($missingRoutes);
        $routeList = [];

        foreach ($missingRoutes as $index => $route) {
            $num = $index + 1;
            $routeList[] = "{$num}. {$route->method} /{$route->path}";
            $routeList[] = "   Controller: {$route->className}@{$route->functionName}";
            $routeList[] = '   Add: #[ApiEndpoint(entity: YourEntity::class)]';
            $routeList[] = '';
        }

        $message = "Strict mode: {$count} route(s) are missing #[ApiEndpoint] attribute:\n\n".
            implode("\n", $routeList).
            "\nTo fix: Add #[ApiEndpoint] attribute to these controller methods.\n".
            "To exclude: Add patterns to config/api-doc-kit.php 'exclude_patterns'.\n".
            "To disable validation: Set 'strict_mode' => false.";

        return new self($message);
    }
}
