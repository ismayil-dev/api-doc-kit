<?php declare(strict_types=1);

namespace IsmayilDev\LaravelDocKit\Tests\Doubles\Controllers;

use IsmayilDev\LaravelDocKit\Attributes\Resources\ListResource;
use IsmayilDev\LaravelDocKit\Tests\Doubles\Models\User;

class OrderListController
{
    #[ListResource(User::class)]
    public function __invoke()
    {
        //return some response
    }
}