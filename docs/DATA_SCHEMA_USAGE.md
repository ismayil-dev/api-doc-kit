# DataSchema - Auto-Generate OpenAPI Schemas from Data Classes

The `#[DataSchema]` attribute allows you to automatically generate OpenAPI schemas from typed PHP classes (DTOs, value objects, etc.).

## Quick Start

### 1. Mark Your DTO with `#[DataSchema]`

```php
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;
use Illuminate\Contracts\Support\Arrayable;

#[DataSchema]
class OrderDto implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly string $description,
        public readonly int $total,
        public readonly OrderStatus $status,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'total' => $this->total,
            'status' => $this->status->value,
            'formatted_total' => '$' . number_format($this->total / 100, 2),
        ];
    }
}
```

### 2. Reference It in Your Controller

```php
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;

class OrderController
{
    #[ApiEndpoint(
        entity: Order::class,
        responseEntity: 'OrderDto'  // Reference your DTO
    )]
    public function show(Order $order): SingleResourceResponse
    {
        $dto = new OrderDto(
            id: $order->id,
            description: $order->description,
            total: $order->total_cents,
            status: $order->status,
        );

        return new ResourceResponse($dto);
    }
}
```

### 3. Generate Documentation

```bash
php artisan doc:generate
```

The generated OpenAPI schema will include:
- All fields from constructor properties
- All fields from `toArray()` method (including computed fields like `formatted_total`)
- Proper type mapping (string, integer, boolean, enum → string, etc.)

## How It Works

### Property Detection

The parser extracts schema information from two sources:

1. **Constructor Promoted Properties** - Type information from constructor parameters
2. **toArray() Method** - Actual API structure including computed fields

### Type Mapping

PHP types are automatically mapped to OpenAPI types:

| PHP Type | OpenAPI Type |
|----------|--------------|
| `string` | `string` |
| `int`, `integer` | `integer` |
| `float`, `double` | `number` |
| `bool`, `boolean` | `boolean` |
| `array` | `array` |
| `DateTimeInterface` | `datetime` |
| Backed Enums | `string` or `integer` |

### With or Without `toArray()`

**Option 1: Simple DTO without toArray**
```php
#[DataSchema]
class UserDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
```
Schema includes: `id` (integer), `name` (string)

**Option 2: DTO with toArray (Recommended)**
```php
#[DataSchema]
class UserDto implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => strtoupper($this->name), // Computed field
        ];
    }
}
```
Schema includes: `id` (integer), `name` (string), `display_name` (string)

## Advanced Usage

### Custom Schema Title and Description

```php
#[DataSchema(
    title: 'Order Response',
    description: 'Detailed order information with computed fields'
)]
class OrderDto implements Arrayable
{
    // ...
}
```

### Handling Enums

```php
enum OrderStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
}

#[DataSchema]
class OrderDto implements Arrayable
{
    public function __construct(
        public readonly OrderStatus $status,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status->value, // Auto-detected as string
        ];
    }
}
```

### Nested Objects

For nested DTOs, manually reference them:

```php
#[DataSchema]
class UserDto implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}

    public function toArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name];
    }
}

#[DataSchema]
class OrderDto implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly UserDto $user,  // Nested DTO
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user->toArray(),  // Manual nesting
        ];
    }
}
```

## Strict Mode

Enable strict mode for production environments to ensure accurate documentation:

```php
// config/api-doc-kit.php
'schema' => [
    'strict_mode' => true, // Require explicit properties for computed fields
],
```

**Strict Mode Behavior:**
- **ON**: Computed fields with unknown types MUST be explicitly defined or build fails
- **OFF (default)**: Computed fields default to `string` with warning logged

**Example - Strict Mode Error:**
```php
// This will throw StrictModeException in strict mode:
#[DataSchema]
class OrderDto implements Arrayable
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,  // ✓ Auto-detected from constructor
            'computed' => someComplexCalculation(),  // ❌ Unknown type!
        ];
    }
}

// Fix by adding explicit property:
#[DataSchema(
    properties: [
        new IntProperty(property: 'computed', description: 'Computed value', example: '100')
    ]
)]
class OrderDto implements Arrayable { ... }
```

## Best Practices

1. **Always implement `Arrayable`** - Provides better schema detection
2. **Use typed properties** - Ensures accurate OpenAPI type mapping
3. **Mark with `#[DataSchema]`** - Explicit opt-in prevents accidental schema generation
4. **Keep DTOs simple** - Avoid complex logic in `toArray()` for better parsing
5. **Enable strict mode in production** - Prevents incorrect documentation
6. **Define computed fields explicitly** - Use property attributes for clarity

## Why Not Auto-Detect from Models?

We deliberately **do not** auto-detect schemas from Eloquent models because:

- Models have database fields that may differ from API responses
- Hidden fields (`$hidden`) would cause incorrect documentation
- Computed/appended attributes aren't in the schema
- Large projects have complex transformations

DTOs provide **explicit, accurate** API schemas that match what's actually returned.
