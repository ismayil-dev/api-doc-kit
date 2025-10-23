# Documentation Coverage - Ensure All Routes Have #[ApiEndpoint]

The package can validate that all your API routes have proper documentation via the `#[ApiEndpoint]` attribute.

## Overview

By default, routes without `#[ApiEndpoint]` are silently skipped during documentation generation. This validation feature helps you identify undocumented routes before they reach production.

## Quick Start

### Enable Strict Mode

```php
// config/api-doc-kit.php
return [
    'schema' => [
        'strict_mode' => true, // Throws exception for missing attributes
    ],
];
```

When strict mode is **enabled**:
- Documentation generation **fails** if routes lack `#[ApiEndpoint]`
- Provides detailed error messages with route information
- Ensures complete API documentation coverage

When strict mode is **disabled** (default):
- Missing attributes generate **warning logs**
- Documentation generation continues normally
- Useful for gradual adoption

## Configuration

### Excluding Routes from Validation

Some routes shouldn't be documented (admin panels, internal endpoints, webhooks):

```php
// config/api-doc-kit.php
return [
    'documentation' => [
        'exclude_patterns' => [
            '^admin/',      // Admin routes (admin/users, admin/settings)
            '^internal/',   // Internal API routes (internal/metrics)
            '^webhook/',    // Webhook handlers
        ],
    ],
];
```

**Built-in Exclusions** (automatically applied):
- `^_debugbar` - Laravel Debugbar
- `^telescope` - Laravel Telescope
- `^horizon` - Laravel Horizon
- `^(up|health)$` - Health check endpoints

### Example Configuration

```php
// config/api-doc-kit.php
return [
    'schema' => [
        'strict_mode' => true, // Require all routes to have #[ApiEndpoint]
    ],

    'documentation' => [
        'exclude_patterns' => [
            '^admin/',      // Skip admin panel routes
            '^internal/',   // Skip internal monitoring endpoints
            '^dev/',        // Skip development-only routes
        ],
    ],
];
```

## Usage Examples

### Scenario 1: Warning Mode (Development)

**Config:**
```php
'schema' => ['strict_mode' => false],
```

**Result:**
```bash
php artisan doc:generate

[WARNING] Route missing #[ApiEndpoint] attribute: GET /users (App\Http\Controllers\UserController@index)
[WARNING] Route missing #[ApiEndpoint] attribute: POST /orders (App\Http\Controllers\OrderController@store)

Documentation generated successfully!
```

Documentation is generated, but warnings alert you to missing attributes.

### Scenario 2: Strict Mode (CI/Production)

**Config:**
```php
'schema' => ['strict_mode' => true],
```

**Result:**
```bash
php artisan doc:generate

Strict mode: 2 route(s) are missing #[ApiEndpoint] attribute:

1. GET /users
   Controller: App\Http\Controllers\UserController@index
   Add: #[ApiEndpoint(entity: YourEntity::class)]

2. POST /orders
   Controller: App\Http\Controllers\OrderController@store
   Add: #[ApiEndpoint(entity: YourEntity::class)]

To fix: Add #[ApiEndpoint] attribute to these controller methods.
To exclude: Add patterns to config/api-doc-kit.php 'exclude_patterns'.
To disable validation: Set 'strict_mode' => false.

Command failed with exception!
```

Documentation generation **fails** until all routes are documented or excluded.

### Scenario 3: Excluding Internal Routes

**Config:**
```php
'documentation' => [
    'exclude_patterns' => [
        '^admin/',
        '^internal/',
    ],
],
```

**Controller:**
```php
// ❌ Will trigger validation error (public API route)
class UserController extends Controller
{
    public function index(): CollectionResponse
    {
        // Missing #[ApiEndpoint]
    }
}

// ✅ Excluded from validation (matches ^admin/ pattern)
class AdminController extends Controller
{
    public function dashboard()
    {
        // No #[ApiEndpoint] needed
    }
}
```

## Error Messages

The validation provides detailed, actionable error messages:

```
Strict mode: 3 route(s) are missing #[ApiEndpoint] attribute:

1. GET /users
   Controller: App\Http\Controllers\UserController@index
   Add: #[ApiEndpoint(entity: YourEntity::class)]

2. POST /users
   Controller: App\Http\Controllers\UserController@store
   Add: #[ApiEndpoint(entity: YourEntity::class)]

3. DELETE /users/{user}
   Controller: App\Http\Controllers\UserController@destroy
   Add: #[ApiEndpoint(entity: YourEntity::class)]

To fix: Add #[ApiEndpoint] attribute to these controller methods.
To exclude: Add patterns to config/api-doc-kit.php 'exclude_patterns'.
To disable validation: Set 'strict_mode' => false.
```

Each error shows:
- Route number and HTTP method
- Full route path
- Controller and method name
- Example attribute to add

## Integration with CI/CD

### Enable Strict Mode in Production Environments

```php
// config/api-doc-kit.php
return [
    'schema' => [
        'strict_mode' => env('API_DOC_STRICT_MODE', false),
    ],
];
```

**.env.production:**
```
API_DOC_STRICT_MODE=true
```

**.env (local):**
```
API_DOC_STRICT_MODE=false
```

### GitHub Actions Example

```yaml
name: Validate API Documentation

on: [push, pull_request]

jobs:
  validate-docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install Dependencies
        run: composer install

      - name: Generate API Documentation
        env:
          API_DOC_STRICT_MODE: true
        run: php artisan doc:generate
```

This ensures pull requests with undocumented routes are blocked.

## Best Practices

1. **Enable Strict Mode in CI/CD** - Catch missing documentation early
2. **Use Warning Mode Locally** - More forgiving during development
3. **Document Exclusion Patterns** - Add comments explaining why routes are excluded
4. **Review Exclusions Regularly** - Ensure patterns don't accidentally exclude public APIs
5. **Add Attributes When Creating Routes** - Make documentation part of the development workflow

## Common Patterns

### Excluding Auth/Admin Routes

```php
'exclude_patterns' => [
    '^admin/',
    '^auth/',
    '^password/',
],
```

### Excluding Internal Monitoring

```php
'exclude_patterns' => [
    '^internal/metrics',
    '^internal/health',
    '^_profiler',
],
```

### Excluding Third-Party Webhooks

```php
'exclude_patterns' => [
    '^webhooks/stripe',
    '^webhooks/github',
    '^callbacks/',
],
```

## Troubleshooting

### "Route missing attribute" but I added #[ApiEndpoint]

**Possible causes:**
1. Attribute is on wrong method (check method name matches route)
2. Class namespace doesn't match (route uses different controller)
3. Cache not cleared: `php artisan route:clear && php artisan cache:clear`

**Solution:**
```php
// ❌ Wrong - attribute on different method
class UserController {
    #[ApiEndpoint(entity: User::class)]
    public function show() { }

    public function index() { } // Route points here
}

// ✅ Correct
class UserController {
    #[ApiEndpoint(entity: User::class)]
    public function index() { } // Attribute on correct method
}
```

### "Route missing attribute" for excluded pattern

**Possible cause:** Pattern doesn't match route path

**Solution:** Test your regex pattern
```php
// Route: api/v1/admin/users
'exclude_patterns' => [
    '^api/v1/admin/',  // ✅ Matches
    '^admin/',          // ❌ Doesn't match (missing api/v1/)
],
```

### Too many routes to document at once

**Gradual adoption strategy:**

1. **Week 1:** Add exclusions for all undocumented areas
```php
'exclude_patterns' => [
    '^admin/',
    '^internal/',
    '^legacy/',
],
```

2. **Week 2:** Document new endpoints, keep strict mode off
```php
'strict_mode' => false, // Warnings only
```

3. **Week 3:** Document one excluded area, remove from exclusions
```php
'exclude_patterns' => [
    // '^admin/',  // Now documented!
    '^internal/',
    '^legacy/',
],
```

4. **Week 4:** Enable strict mode after all routes documented
```php
'strict_mode' => true,
'exclude_patterns' => [
    '^internal/',  // Only keep genuinely internal routes
],
```

## Integration with Other Features

### Works with Strict Mode for Schema

The `strict_mode` setting controls **both**:
- Route validation (missing `#[ApiEndpoint]`)
- Schema validation (computed fields, array types in `#[DataSchema]`)

```php
// config/api-doc-kit.php
'schema' => [
    'strict_mode' => true, // Enables ALL strict validations
],
```

When enabled:
- Missing `#[ApiEndpoint]` → Exception
- Computed fields in DTOs without property attributes → Exception
- Array properties without `#[ArrayOf]` → Exception

This ensures complete, accurate documentation in production.

## Summary

| Mode | Behavior | Use Case |
|------|----------|----------|
| **Strict OFF** (default) | Log warnings, continue | Local development, gradual adoption |
| **Strict ON** | Throw exception, fail build | CI/CD, production environments |
| **Exclude Patterns** | Skip specific routes | Internal/admin routes, health checks |

**Recommendation:** Use strict mode in CI/CD to maintain documentation quality, keep it off locally for flexibility.
