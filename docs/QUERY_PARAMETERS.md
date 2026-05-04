# Query, Header, and List-Query Parameters

This guide covers the typed parameter primitives shipped under `IsmayilDev\ApiDocKit\Attributes\Parameters\` and the contributor extension point for auto-deriving list-endpoint parameters.

## Quick Reference

### Query Parameters

| Attribute                   | OpenAPI emission                                       |
|-----------------------------|--------------------------------------------------------|
| `StringQueryParameter`      | `type: string`                                         |
| `IntQueryParameter`         | `type: integer`                                        |
| `FloatQueryParameter`       | `type: number`, `format: float`, optional `min/max`    |
| `BoolQueryParameter`        | `type: boolean`                                        |
| `DateQueryParameter`        | `type: string`, `format: date` (or `date-time`)        |
| `EnumQueryParameter`        | `type: string\|integer` with `enum: [...]` from a backed PHP enum |
| `ArrayQueryParameter`       | `type: array`, `style: form`, `explode: true`          |
| `SortQueryParameter`        | `type: string`, allow-listed sort tokens (incl. `-prefix`) |
| `SearchQueryParameter`      | `type: string`, free-text `q`                          |
| `PaginationQueryParameters` | static factory returning `[page, per_page]` parameters |

### Header Parameters

| Attribute                | Description                          |
|--------------------------|--------------------------------------|
| `StringHeaderParameter`  | Untyped string header                |
| `IntHeaderParameter`     | Integer header                       |
| `UuidHeaderParameter`    | String header with `format: uuid`    |

## Usage

```php
use IsmayilDev\ApiDocKit\Attributes\Parameters\Headers\UuidHeaderParameter;
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\{
    BoolQueryParameter,
    EnumQueryParameter,
    PaginationQueryParameters,
    SearchQueryParameter,
    SortQueryParameter,
};
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;

#[ApiEndpoint(
    entity: 'Service',
    responseEntity: 'ServiceData',
    parameters: [
        ...PaginationQueryParameters::make(defaultPerPage: 15, maxPerPage: 100),
        new SortQueryParameter(allowedFields: ['name', 'createdAt']),
        new SearchQueryParameter(),
        new BoolQueryParameter('isActive'),
        new EnumQueryParameter('status', enumClass: ServiceStatus::class),
        new UuidHeaderParameter('X-Account-Id', required: true),
    ],
)]
public function __invoke(...) {}
```

## List-Query Contributor Extension Point

For projects that have a structured filter/sort/search definition (per-field operator whitelists, relation paths, etc.), api-doc-kit provides a contributor extension point so the OpenAPI parameters stay in sync with the runtime definition automatically.

### 1. Implement the contributor (project-side)

```php
use IsmayilDev\ApiDocKit\Contracts\ListQueryParametersContributor;
use OpenApi\Attributes\Parameter;

final class DefinitionParametersContributor implements ListQueryParametersContributor
{
    public function __construct(private string $definition) {}

    /** @return list<Parameter> */
    public function toOpenApiParameters(): array
    {
        $def = new $this->definition();
        // Walk $def->filters() / $def->sorts() / $def->search() and emit one
        // parameter per (field, operator) using the typed primitives.
        return [/* ... */];
    }
}
```

### 2. Attach the marker to a controller method

```php
use IsmayilDev\ApiDocKit\Attributes\Parameters\Query\ListQueryParameters;

#[ApiEndpoint(entity: 'Service', responseEntity: 'ServiceData')]
#[ListQueryParameters(
    contributor: DefinitionParametersContributor::class,
    arguments: ['definition' => ServiceListDefinition::class],
)]
public function __invoke(...) {}
```

At doc-gen time, `RoutePathParameterBuilder` instantiates the contributor (forwarding `arguments` as named constructor args), calls `toOpenApiParameters()`, and merges the result into the operation's parameter list. Manual `parameters: [...]` on `ApiEndpoint` are still honored and merged alongside; deduplication by parameter `name` is automatic.

api-doc-kit ships only the interface and the marker attribute — the runtime filter system stays in your project so it can stay coupled to whatever ORM / validation library you use.
