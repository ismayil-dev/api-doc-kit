# Enum Schema Generation

The `#[Enum]` attribute enables proper OpenAPI schema generation for PHP enums, with special handling for integer-backed enums to ensure correct SDK generation.

## Quick Start

### String-Backed Enums

```php
use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

#[Enum]
enum OrderStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
```

**Generated OpenAPI:**
```yaml
OrderStatus:
  type: string
  enum:
    - draft
    - pending
    - completed
    - cancelled
  x-enum-varnames: ['Draft', 'Pending', 'Completed', 'Cancelled']  # ← Added for all enums!
```

**Generated TypeScript SDK:**
```typescript
export enum OrderStatus {
    Draft = 'draft',      // ✓ Correct!
    Pending = 'pending',
    Completed = 'completed',
    Cancelled = 'cancelled',
}
```

> **Note:** `x-enum-varnames` is now added for all backed enums (both string and integer). This is especially useful when enum case names differ from values (e.g., `InProgress` vs `'in_progress'`).

### Integer-Backed Enums

```php
use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

#[Enum]  // ← This fixes SDK generation!
enum OrderPaymentStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Refunded = 2;
    case Cancelled = 3;
}
```

**Generated OpenAPI (Before #[Enum]):**
```yaml
OrderPaymentStatus:
  type: integer
  enum: [0, 1, 2, 3]
  # Missing names causes SDK generators to create _0, _1, _2, _3
```

**Generated OpenAPI (After with #[Enum]):**
```yaml
OrderPaymentStatus:
  type: integer
  enum: [0, 1, 2, 3]
  x-enum-varnames: ['Pending', 'Paid', 'Refunded', 'Cancelled']  # ← Added automatically!
```

**Generated TypeScript SDK (Before):**
```typescript
export enum OrderPaymentStatus {
    _0 = 0,  // ❌ Wrong!
    _1 = 1,
    _2 = 2,
    _3 = 3,
}
```

**Generated TypeScript SDK (After):**
```typescript
export enum OrderPaymentStatus {
    Pending = 0,   // ✓ Correct!
    Paid = 1,
    Refunded = 2,
    Cancelled = 3,
}
```

## How It Works

### The x-enum-varnames Extension

The `x-enum-varnames` is a widely-supported OpenAPI extension that maps enum values to their proper names:

- **Standard**: Supported by OpenAPI Generator, Redocly, openapi-typescript, and most SDK generators
- **Purpose**: Provides variable names for enum values (important for integers and when string case names differ from values)
- **Automatic**: The `EnumSchemaProcessor` adds this automatically for all backed enums

### Processing Flow

1. **Enum Detection**: Processor finds all enums with `#[Enum]` attribute
2. **Type Detection**: Determines if enum is string-backed or int-backed
3. **Case Extraction**: Extracts all enum cases (names and values)
4. **Schema Generation**:
   - Sets appropriate type (`integer` or `string`)
   - Sets enum values array
   - Adds `x-enum-varnames` array with case names for all backed enums
5. **SDK Generation**: SDK generators use `x-enum-varnames` to create proper enums with correct case names

## Advanced Usage

### With Custom Title and Description

```php
#[Enum(
    title: 'Payment Status',
    description: 'Status of order payment processing'
)]
enum OrderPaymentStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Refunded = 2;
}
```

**Generated OpenAPI:**
```yaml
OrderPaymentStatus:
  title: Payment Status
  description: Status of order payment processing
  type: integer
  enum: [0, 1, 2]
  x-enum-varnames: ['Pending', 'Paid', 'Refunded']
```

### Unit Enums (No Backing Value)

```php
#[Enum]
enum Color
{
    case Red;
    case Green;
    case Blue;
}
```

**Generated OpenAPI:**
```yaml
Color:
  type: string
  enum: ['Red', 'Green', 'Blue']  # Case names used as values
```

## Using Enums in API Responses

### In DTOs

```php
#[DataSchema]
class OrderDto implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly OrderPaymentStatus $status,  // ← Enum property
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,  // Export the value
        ];
    }
}
```

The `ResponseDataParser` automatically detects enum types and maps them to string/integer in the schema.

### In Controllers

```php
#[ApiEndpoint(entity: Order::class)]
public function show(Order $order): SingleResourceResponse
{
    return new ResourceResponse([
        'id' => $order->id,
        'status' => $order->payment_status,  // OrderPaymentStatus enum
    ]);
}
```

## SDK Generator Compatibility

### Tested With

- ✅ **Redocly @redocly/openapi-sdk-codegen** - Full support
- ✅ **OpenAPI Generator** - Full support
- ✅ **openapi-typescript-codegen** - Full support
- ✅ **openapi-typescript** - Full support

### Example SDK Output

**Input (PHP):**
```php
#[Enum]
enum Priority: int {
    case Low = 0;
    case Medium = 1;
    case High = 2;
}
```

**Output (TypeScript - Redocly):**
```typescript
export enum Priority {
    Low = 0,
    Medium = 1,
    High = 2,
}
```

**Output (Java - OpenAPI Generator):**
```java
public enum Priority {
    LOW(0),
    MEDIUM(1),
    HIGH(2);

    private Integer value;
}
```

## Best Practices

1. **Always use `#[Enum]`** - Mark all enums used in API responses to ensure proper SDK generation
2. **Prefer string enums** - Better API clarity for developers consuming your API
3. **Use int enums for IDs** - When you need specific numeric values (e.g., database IDs, status codes)
4. **Descriptive case names** - Use clear case names that translate well to SDKs (e.g., `InProgress`, not `in_progress`)
5. **Add descriptions** - Help API consumers understand enum meanings and usage

## Migration Guide

### If You Have Existing Integer Enums

**Before (generating _0, _1, _2 in SDKs):**
```php
enum OrderStatus: int {
    case Pending = 0;
    case Paid = 1;
}
```

**After (generates proper names):**
```php
use IsmayilDev\ApiDocKit\Attributes\Schema\Enum;

#[Enum]  // ← Add this!
enum OrderStatus: int {
    case Pending = 0;
    case Paid = 1;
}
```

Then regenerate your OpenAPI docs:
```bash
php artisan doc:generate
```

Your SDKs will now generate with proper enum names!

## Troubleshooting

### SDK Still Generates _0, _1, _2

1. Ensure `#[Enum]` attribute is present on enum
2. Regenerate OpenAPI docs: `php artisan doc:generate`
3. Check YAML contains `x-enum-varnames` array
4. Regenerate SDK from updated OpenAPI file

### x-enum-varnames Not Appearing

- Should be added for all **backed enums** (both string and integer)
- Unit enums (without backing values) don't get `x-enum-varnames`
- Verify enum extends `BackedEnum` (has `: string` or `: int` declaration)

## References

- [OpenAPI x-enum-varnames Extension](https://github.com/OpenAPITools/openapi-generator/blob/master/docs/customization.md#enum-variable-naming)
- [Enum Mappings in OpenAPI](https://mykeels.medium.com/enums-mappings-in-openapi-a76be95fec07)
- [PHP Enums Documentation](https://www.php.net/manual/en/language.enumerations.php)
