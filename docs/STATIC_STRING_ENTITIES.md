# Static String Entities - Using Simple Strings Instead of Model Classes

The `#[ApiEndpoint]` attribute supports both Laravel model classes and simple static strings as entity identifiers.

## Overview

Previously, you were required to pass a model class reference:
```php
#[ApiEndpoint(entity: Product::class)]
```

Now you can use simple strings:
```php
#[ApiEndpoint(entity: 'Product')]
```

This is useful when:
- You don't have a corresponding Eloquent model
- Working with external APIs or microservices
- Creating documentation for future endpoints
- Dealing with non-database resources

## Quick Start

### Basic Static String

```php
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;

class ProductController extends Controller
{
    #[ApiEndpoint(entity: 'Product')]
    public function show(string $id): SingleResourceResponse
    {
        // Your logic here
    }
}
```

**Generated Documentation:**
- Operation ID: `getProduct`
- Tags: `Products`
- Description: `Get Product`
- Route parameter type: `integer` (default)
- Example ID: `product-id`

### Model Class (Original Behavior)

```php
use App\Models\Product;

#[ApiEndpoint(entity: Product::class)]
public function show(Product $product): SingleResourceResponse
{
    // Your logic here
}
```

**Generated Documentation:**
- Uses model's `getKeyType()` for parameter type
- Uses model-specific example ID generation
- All other fields work the same

## Customizing Static String Entities

### Override Key Type

By default, static string entities use `integer` for route parameters. Override with `keyType`:

```php
#[ApiEndpoint(
    entity: 'Product',
    keyType: 'string'  // UUID, slug, or any string identifier
)]
public function show(string $id): SingleResourceResponse
```

**Supported Key Types:**
- `'int'` or `'integer'` (default)
- `'string'`
- `'uuid'`
- Any custom type string

### Override Example ID

Customize the example ID shown in documentation:

```php
#[ApiEndpoint(
    entity: 'Product',
    exampleId: 'prod-123'
)]
public function show(string $id): SingleResourceResponse
```

### Both Overrides

```php
#[ApiEndpoint(
    entity: 'Product',
    keyType: 'uuid',
    exampleId: '550e8400-e29b-41d4-a716-446655440000'
)]
public function show(string $id): SingleResourceResponse
```

**Generated Parameter Documentation:**
```yaml
parameters:
  - name: id
    in: path
    required: true
    schema:
      type: string
      format: uuid
    example: "550e8400-e29b-41d4-a716-446655440000"
```

## Use Cases

### 1. External API Integration

When proxying requests to an external service:

```php
#[ApiEndpoint(
    entity: 'ThirdPartyProduct',
    keyType: 'string',
    exampleId: 'ext-prod-12345'
)]
public function getExternalProduct(string $id): SingleResourceResponse
{
    $product = Http::get("https://api.external.com/products/{$id}");
    return new ResourceResponse($product);
}
```

### 2. Microservice Architecture

Documenting endpoints that interact with other services:

```php
#[ApiEndpoint(
    entity: 'InventoryItem',
    keyType: 'uuid'
)]
public function checkInventory(string $itemId): SingleResourceResponse
{
    // Call inventory microservice
}
```

### 3. Non-Database Resources

For endpoints that don't map to database models:

```php
#[ApiEndpoint(
    entity: 'Report',
    keyType: 'string',
    exampleId: 'monthly-sales-2024'
)]
public function getReport(string $reportName): SingleResourceResponse
{
    // Generate dynamic report
}
```

### 4. API Versioning

Different API versions with same model:

```php
// V1 API - uses integer IDs
#[ApiEndpoint(entity: 'Product', exampleId: 123)]
public function showV1(int $id): SingleResourceResponse

// V2 API - uses UUIDs
#[ApiEndpoint(entity: 'Product', keyType: 'uuid')]
public function showV2(string $id): SingleResourceResponse
```

## Defaults and Resolution

### Static String Defaults

| Property | Default Value | Override Parameter |
|----------|--------------|-------------------|
| Key Type | `'int'` | `keyType` |
| Example ID | `'{entity-name}-id'` | `exampleId` |
| Resource Name | `'{entity}Dto'` | `responseEntity` |

### Model Class Behavior

| Property | Behavior | Override Parameter |
|----------|----------|-------------------|
| Key Type | From `$model->getKeyType()` | `keyType` |
| Example ID | From `ModelExampleIdGenerator` | `exampleId` |
| Resource Name | `'{ModelName}Dto'` | `responseEntity` |

### Priority Order

1. **Explicit attribute parameter** (highest priority)
2. **Model class properties** (if entity is a model)
3. **Static string defaults** (if entity is a string)

## Examples

### Simple Integer ID (Default)

```php
#[ApiEndpoint(entity: 'Order')]
public function show(int $id): SingleResourceResponse
```

Generated docs show:
- Route: `/orders/{id}`
- Parameter type: `integer`
- Example: `order-id`

### UUID-Based Resource

```php
#[ApiEndpoint(
    entity: 'Session',
    keyType: 'string',
    exampleId: '550e8400-e29b-41d4-a716-446655440000'
)]
public function show(string $sessionId): SingleResourceResponse
```

Generated docs show:
- Route: `/sessions/{sessionId}`
- Parameter type: `string`
- Example: `550e8400-e29b-41d4-a716-446655440000`

### Slug-Based Resource

```php
#[ApiEndpoint(
    entity: 'Article',
    keyType: 'string',
    exampleId: 'getting-started-with-laravel'
)]
public function show(string $slug): SingleResourceResponse
```

Generated docs show:
- Route: `/articles/{slug}`
- Parameter type: `string`
- Example: `getting-started-with-laravel`

## Comparison Table

| Feature | Model Class (`Product::class`) | Static String (`'Product'`) |
|---------|-------------------------------|----------------------------|
| **Syntax** | `entity: Product::class` | `entity: 'Product'` |
| **Requires Model** | ✅ Yes | ❌ No |
| **Default Key Type** | From model | `'int'` |
| **Default Example ID** | From model | `'product-id'` |
| **Can Override Key Type** | ✅ Yes | ✅ Yes |
| **Can Override Example ID** | ✅ Yes | ✅ Yes |
| **Resource Name** | `ProductDto` | `ProductDto` |
| **Tags** | `Products` | `Products` |
| **Operation ID** | `getProduct` | `getProduct` |

## Best Practices

1. **Use Model Classes When Available** - If you have an Eloquent model, prefer using `Model::class` for automatic key type detection

2. **Static Strings for Flexibility** - Use static strings when:
   - No model exists
   - Working with external resources
   - Documenting future endpoints
   - Need entity name that differs from model

3. **Always Set Key Type for UUIDs** - Don't rely on defaults for non-integer IDs:
   ```php
   // ❌ Bad - defaults to integer
   #[ApiEndpoint(entity: 'Product')]

   // ✅ Good - explicit UUID type
   #[ApiEndpoint(entity: 'Product', keyType: 'uuid')]
   ```

4. **Provide Realistic Example IDs** - Help API consumers with meaningful examples:
   ```php
   // ❌ Less helpful
   #[ApiEndpoint(entity: 'Product', exampleId: 'example-id')]

   // ✅ More helpful
   #[ApiEndpoint(entity: 'Product', exampleId: 'prod_1234567890')]
   ```

5. **Keep Entity Names Singular** - Follow Laravel conventions:
   ```php
   // ✅ Correct
   #[ApiEndpoint(entity: 'Product')]

   // ❌ Incorrect
   #[ApiEndpoint(entity: 'Products')]
   ```

## Backward Compatibility

This feature is **100% backward compatible**. All existing code using `Model::class` continues to work exactly as before. Static strings are a new, optional feature.

### Migration Example

**Before:**
```php
use App\Models\Product;

#[ApiEndpoint(entity: Product::class)]
public function show(Product $product): SingleResourceResponse
```

**After (optional migration):**
```php
#[ApiEndpoint(entity: 'Product')]
public function show(string $id): SingleResourceResponse
```

## Advanced Usage

### Custom DTO Response Names

Combine static string entities with custom response entities:

```php
#[ApiEndpoint(
    entity: 'Product',
    responseEntity: 'ProductDetailDto'  // Custom DTO name
)]
public function show(string $id): SingleResourceResponse
{
    return new ResourceResponse(new ProductDetailDto(...));
}
```

### Multiple Endpoints, One Entity

Different endpoints for the same entity:

```php
class ProductController
{
    #[ApiEndpoint(entity: 'Product')]
    public function show(string $id): SingleResourceResponse

    #[ApiEndpoint(entity: 'Product')]
    public function index(): CollectionResponse

    #[ApiEndpoint(entity: 'Product')]
    public function store(CreateProductRequest $request): CreatedResponse
}
```

All generate consistent documentation under the `Products` tag.

## Troubleshooting

### "Cannot instantiate static string entity"

**Error:**
```
RuntimeException: Cannot instantiate static string entity 'Product'
```

**Cause:** Internal code tried to create model instance for static string

**Solution:** This should not happen in normal usage. If you see this, it's likely a bug in the package. Report it with your controller code.

### Wrong Key Type in Documentation

**Problem:** Documentation shows `integer` but your IDs are UUIDs

**Solution:** Add explicit `keyType`:
```php
#[ApiEndpoint(entity: 'Product', keyType: 'uuid')]
```

### Example ID Not Showing

**Problem:** Example ID shows as `product-id` instead of custom value

**Solution:** Add explicit `exampleId`:
```php
#[ApiEndpoint(entity: 'Product', exampleId: 'your-example')]
```

## Summary

Static string entities provide flexibility when model classes aren't available or appropriate. Use them to:

- Document non-model resources
- Work with external APIs
- Prototype endpoints
- Customize key types and example IDs

The feature is fully backward compatible - use whichever approach makes sense for your use case.
