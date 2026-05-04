<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Feature\Parameters;

use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\ListQueryParameters;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\StringQueryParameter;
use IsmayilDev\ApiDocKit\Contracts\ListQueryParametersContributor;
use IsmayilDev\ApiDocKit\Models\DocEntity;
use IsmayilDev\ApiDocKit\Routes\RouteItem;
use IsmayilDev\ApiDocKit\Routes\RoutePathParameterBuilder;
use IsmayilDev\ApiDocKit\Routes\RoutePathParameterResolver;
use OpenApi\Attributes\Parameter;
use ReflectionClass;

class StubContributor implements ListQueryParametersContributor
{
    public function __construct(public string $tag = 'default') {}

    /** @return list<Parameter> */
    public function toOpenApiParameters(): array
    {
        return [
            new StringQueryParameter(name: "filter[name][eq]_{$this->tag}"),
            new StringQueryParameter(name: "q_{$this->tag}"),
        ];
    }
}

class StubListController
{
    #[ListQueryParameters(contributor: StubContributor::class, arguments: ['tag' => 'svc'])]
    public function __invoke(): void {}
}

test('RoutePathParameterBuilder invokes ListQueryParameters contributors', function () {
    // Resolver is unused in this test (no path parameters); bypass its constructor.
    $resolver = (new ReflectionClass(RoutePathParameterResolver::class))->newInstanceWithoutConstructor();
    $builder = new RoutePathParameterBuilder($resolver);

    $route = new RouteItem(
        className: StubListController::class,
        method: 'GET',
        path: '/stub',
        functionName: '__invoke',
        name: 'stub.list',
        parameters: [],
        isSingleAction: true,
    );

    $entity = new DocEntity(entity: 'Stub');
    $params = $builder->build($route, $entity);

    $names = $params->pluck('name')->all();

    expect($names)->toContain('filter[name][eq]_svc')
        ->and($names)->toContain('q_svc');
});
