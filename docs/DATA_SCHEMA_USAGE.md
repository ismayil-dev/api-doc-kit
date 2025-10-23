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

Enums with the `#[Enum]` attribute are automatically referenced via `$ref`:

```php
use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

#[Enum]
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
            'status' => $this->status->value,
        ];
    }
}
```

Generated OpenAPI:
```yaml
OrderDto:
  properties:
    status:
      $ref: '#/components/schemas/OrderStatus'
```

### Handling Date/Time Properties

Carbon and DateTime properties support customizable formatting with three priority levels.

**Priority Resolution:**
1. **Property-level attribute** (highest priority)
2. **DataSchema properties parameter**
3. **Global config** (lowest priority)

#### Option 1: Property-Level Attribute

```php
use IsmayilDev\ApiDocKit\Attributes\Properties\DateTime;
use Carbon\Carbon;

#[DataSchema]
class UserDto implements Arrayable
{
    public function __construct(
        public readonly int $id,
        #[DateTime(format: 'Y-m-d')]  // Literal format string
        public readonly Carbon $birthDate,
        #[DateTime(type: 'datetime')]  // Semantic type (uses config)
        public readonly Carbon $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'birth_date' => $this->birthDate->format('Y-m-d'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
```

Generated OpenAPI:
```yaml
UserDto:
  properties:
    birth_date:
      type: string
      format: date
      x-format: Y-m-d
      example: "2024-01-15"
    created_at:
      type: string
      format: date-time
      x-format: Y-m-d H:i:s
      example: "2024-01-15 14:30:00"
```

#### Option 2: DataSchema Properties Parameter

```php
use IsmayilDev\ApiDocKit\Attributes\Schema\DateTimeProperty;

#[DataSchema(properties: [
    new DateTimeProperty(property: 'created_at', type: 'datetime'),
    new DateTimeProperty(property: 'published_at', format: 'd/m/Y H:i'),
])]
class PostDto implements Arrayable
{
    public function __construct(
        public readonly Carbon $createdAt,
        public readonly Carbon $publishedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'published_at' => $this->publishedAt->format('d/m/Y H:i'),
        ];
    }
}
```

#### Option 3: Global Config (Default)

```php
// config/api-doc-kit.php
'schema' => [
    'date_formats' => [
        'date' => 'Y-m-d',           // Date only (2024-01-15)
        'time' => 'H:i:s',           // Time only (14:30:00)
        'datetime' => 'Y-m-d H:i:s', // Date and time (2024-01-15 14:30:00)
    ],
],
```

```php
#[DataSchema]
class EventDto implements Arrayable
{
    public function __construct(
        public readonly Carbon $eventDate,  // Uses global 'datetime' format
    ) {}

    public function toArray(): array
    {
        return [
            'event_date' => $this->eventDate->format('Y-m-d H:i:s'),
        ];
    }
}
```

**DateTime Attribute Options:**

```php
#[DateTime(type: 'date')]           // Uses config format for 'date'
#[DateTime(type: 'time')]           // Uses config format for 'time'
#[DateTime(type: 'datetime')]       // Uses config format for 'datetime'
#[DateTime(format: 'Y-m-d')]        // Literal format (highest priority)
#[DateTime(type: 'date', format: 'd/m/Y')]  // Type + custom override
```

**What Gets Generated:**
- `type: string` - All dates are strings in OpenAPI
- `format: date|time|date-time` - Standard OpenAPI format field
- `x-format: Y-m-d H:i:s` - Custom extension with PHP format for SDK generation
- `example: "2024-01-15 14:30:00"` - Auto-generated example matching the format

### Nested Objects and Value Objects

DTOs can reference other classes marked with `#[DataSchema]`. These are automatically detected and referenced via `$ref`:

```php
// Value object
#[DataSchema]
final readonly class Email
{
    public function __construct(
        private string $email
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: {$email}");
        }
    }

    public function getValue(): string
    {
        return $this->email;
    }
}

// DTO using value object
#[DataSchema]
class UserDto implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly Email $email,  // Value object with #[DataSchema]
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email->getValue(),
        ];
    }
}
```

Generated OpenAPI:
```yaml
UserDto:
  properties:
    id:
      type: integer
    email:
      $ref: '#/components/schemas/Email'  # Automatically referenced!

Email:
  type: object
  properties:
    email:
      type: string
```

**For nested DTOs:**
```php
#[DataSchema]
class AddressDto implements Arrayable
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}

    public function toArray(): array
    {
        return ['street' => $this->street, 'city' => $this->city];
    }
}

#[DataSchema]
class UserDto implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly AddressDto $address,  // Nested DTO
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'address' => $this->address->toArray(),
        ];
    }
}
```

Generated OpenAPI:
```yaml
UserDto:
  properties:
    id:
      type: integer
    address:
      $ref: '#/components/schemas/AddressDto'
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
7. **Use `#[DateTime]` for dates** - Specify format expectations explicitly for API consumers
8. **Mark enums with `#[Enum]`** - Enables automatic `$ref` generation and better SDK support
9. **Use value objects** - Encapsulate validation logic and mark with `#[DataSchema]` for reusability

## Why Not Auto-Detect from Models?

We deliberately **do not** auto-detect schemas from Eloquent models because:

- Models have database fields that may differ from API responses
- Hidden fields (`$hidden`) would cause incorrect documentation
- Computed/appended attributes aren't in the schema
- Large projects have complex transformations

DTOs provide **explicit, accurate** API schemas that match what's actually returned.
