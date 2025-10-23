<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Validation;

use IsmayilDev\ApiDocKit\Exceptions\MissingApiEndpointException;
use IsmayilDev\ApiDocKit\Routes\RouteItem;

test('MissingApiEndpointException includes detailed error message', function () {
    $routes = [
        new RouteItem(
            className: 'App\Http\Controllers\UserController',
            method: 'GET',
            path: 'users',
            functionName: 'index',
            name: 'users.index',
            parameters: [],
            isSingleAction: false
        ),
        new RouteItem(
            className: 'App\Http\Controllers\UserController',
            method: 'POST',
            path: 'users',
            functionName: 'store',
            name: 'users.store',
            parameters: [],
            isSingleAction: false
        ),
    ];

    $exception = MissingApiEndpointException::forRoutes($routes);

    $message = $exception->getMessage();

    // Assert message contains key information
    expect($message)
        ->toContain('Strict mode: 2 route(s) are missing #[ApiEndpoint] attribute')
        ->toContain('1. GET /users')
        ->toContain('Controller: App\Http\Controllers\UserController@index')
        ->toContain('2. POST /users')
        ->toContain('Controller: App\Http\Controllers\UserController@store')
        ->toContain('Add: #[ApiEndpoint(entity: YourEntity::class)]')
        ->toContain('To fix: Add #[ApiEndpoint] attribute')
        ->toContain("To exclude: Add patterns to config/api-doc-kit.php 'exclude_patterns'")
        ->toContain("To disable validation: Set 'strict_mode' => false");
});
