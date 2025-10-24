# Server Configuration - Customize OpenAPI Server URLs

Configure which server URLs appear in your generated OpenAPI documentation.

## Default Behavior (Zero Configuration)

By default, the package uses smart defaults based on your Laravel configuration:

```bash
php artisan doc:generate
```

**Automatic server configuration:**
- **URL**: Uses `APP_URL` from `.env` (falls back to `http://localhost`)
- **Description**: Environment-aware (`Production`, `Staging`, `Local Development`, etc.)

### Example Generated Server

**.env:**
```env
APP_URL=https://api.example.com
APP_ENV=production
```

**Generated OpenAPI:**
```yaml
servers:
  - url: https://api.example.com
    description: Production
```

## Custom Server Configuration

Override the default behavior by binding servers in your service provider.

### Single Server

```php
// app/Providers/AppServiceProvider.php

use OpenApi\Attributes\Server;

public function boot()
{
    $this->app->bind('api-doc-kit.servers', function () {
        return [
            new Server(
                url: config('app.url'),
                description: 'Production API'
            ),
        ];
    });
}
```

### Multiple Servers (Environments)

```php
use OpenApi\Attributes\Server;

public function boot()
{
    $this->app->bind('api-doc-kit.servers', function () {
        return [
            new Server(
                url: 'https://api.example.com',
                description: 'Production'
            ),
            new Server(
                url: 'https://staging-api.example.com',
                description: 'Staging'
            ),
            new Server(
                url: 'http://localhost:8000',
                description: 'Local Development'
            ),
        ];
    });
}
```

### Servers with Variables

Use server variables for dynamic URLs (e.g., versioned APIs, multi-tenant):

```php
use OpenApi\Attributes\Server;
use OpenApi\Attributes\ServerVariable;

public function boot()
{
    $this->app->bind('api-doc-kit.servers', function () {
        return [
            new Server(
                url: 'https://{environment}.example.com/api/{version}',
                description: 'Configurable Environment',
                variables: [
                    new ServerVariable(
                        serverVariable: 'environment',
                        default: 'api',
                        enum: ['api', 'staging', 'dev'],
                        description: 'Environment subdomain'
                    ),
                    new ServerVariable(
                        serverVariable: 'version',
                        default: 'v1',
                        enum: ['v1', 'v2'],
                        description: 'API Version'
                    ),
                ]
            ),
        ];
    });
}
```

**Generated URLs in OpenAPI:**
- `https://api.example.com/api/v1`
- `https://staging.example.com/api/v1`
- `https://api.example.com/api/v2`

## Environment-Based Configuration

Configure different servers based on environment:

```php
use OpenApi\Attributes\Server;

public function boot()
{
    $this->app->bind('api-doc-kit.servers', function () {
        $servers = [];

        // Always include production
        $servers[] = new Server(
            url: 'https://api.example.com',
            description: 'Production'
        );

        // Add staging/local in non-production
        if (app()->environment(['local', 'staging'])) {
            $servers[] = new Server(
                url: 'https://staging-api.example.com',
                description: 'Staging'
            );

            $servers[] = new Server(
                url: 'http://localhost:8000',
                description: 'Local Development'
            );
        }

        return $servers;
    });
}
```

## Use Cases

### 1. Multi-Tenant API

```php
new Server(
    url: 'https://{tenant}.example.com/api',
    description: 'Tenant-specific API',
    variables: [
        new ServerVariable(
            serverVariable: 'tenant',
            default: 'demo',
            description: 'Tenant subdomain'
        ),
    ]
)
```

### 2. Regional APIs

```php
$this->app->bind('api-doc-kit.servers', function () {
    return [
        new Server(
            url: 'https://us-api.example.com',
            description: 'US Region'
        ),
        new Server(
            url: 'https://eu-api.example.com',
            description: 'EU Region'
        ),
        new Server(
            url: 'https://asia-api.example.com',
            description: 'Asia Region'
        ),
    ];
});
```

### 3. Versioned API

```php
new Server(
    url: 'https://api.example.com/{version}',
    description: 'Versioned API',
    variables: [
        new ServerVariable(
            serverVariable: 'version',
            default: 'v2',
            enum: ['v1', 'v2', 'v3'],
            description: 'API Version'
        ),
    ]
)
```

### 4. Protocol Options

```php
$this->app->bind('api-doc-kit.servers', function () {
    return [
        new Server(
            url: 'https://api.example.com',
            description: 'Production (HTTPS)'
        ),
        new Server(
            url: 'http://api.example.com',
            description: 'Production (HTTP)'
        ),
    ];
});
```

## Testing Your Configuration

After configuring servers, generate documentation and verify:

```bash
php artisan doc:generate
```

Check `storage/app/documentation/openapi.yaml`:

```yaml
servers:
  - url: https://api.example.com
    description: Production
  - url: https://staging-api.example.com
    description: Staging
```

## OpenAPI Server Object Reference

### Server

```php
new Server(
    url: string,           // Required: Server URL
    description: string,   // Optional: Human-readable description
    variables: array,      // Optional: Array of ServerVariable objects
)
```

### ServerVariable

```php
new ServerVariable(
    serverVariable: string,  // Required: Variable name (matches {name} in URL)
    default: string,         // Required: Default value
    enum: array,            // Optional: Allowed values
    description: string,    // Optional: Human-readable description
)
```

## Best Practices

1. **Use Environment Variables**
   ```php
   url: config('app.url')  // ✅ Good - respects .env
   url: 'https://hardcoded.com'  // ❌ Avoid hardcoding
   ```

2. **Provide Clear Descriptions**
   ```php
   description: 'Production API'  // ✅ Clear
   description: 'Server 1'        // ❌ Not helpful
   ```

3. **Order by Usage Frequency**
   ```php
   // ✅ Put most commonly used server first
   return [
       new Server(url: config('app.url'), description: 'Production'),
       new Server(url: 'http://localhost', description: 'Local'),
   ];
   ```

4. **Document Server Variables**
   ```php
   new ServerVariable(
       serverVariable: 'version',
       default: 'v1',
       enum: ['v1', 'v2'],
       description: 'API Version'  // ✅ Helps API consumers
   )
   ```

5. **Keep It Simple**
   - For most projects, 1-3 servers is enough
   - Only add variables if truly needed
   - Default behavior works great for simple use cases

## Troubleshooting

### Servers Not Appearing in Documentation

**Problem:** Generated OpenAPI shows `http://localhost` instead of custom servers

**Solution:** Make sure you're binding in the `boot()` method:

```php
// ✅ Correct
public function boot()
{
    $this->app->bind('api-doc-kit.servers', ...);
}

// ❌ Wrong
public function register()
{
    $this->app->bind('api-doc-kit.servers', ...);
}
```

### Server Variables Not Working

**Problem:** Variable name doesn't match URL placeholder

```php
// ❌ Wrong - variable name doesn't match {version}
new Server(
    url: 'https://api.example.com/{version}',
    variables: [
        new ServerVariable(serverVariable: 'api_version', ...)  // Wrong name!
    ]
)

// ✅ Correct
new Server(
    url: 'https://api.example.com/{version}',
    variables: [
        new ServerVariable(serverVariable: 'version', ...)  // Matches {version}
    ]
)
```

### APP_URL Not Being Used

**Problem:** Default server shows `http://localhost` instead of `APP_URL`

**Solution:** Make sure `APP_URL` is set in `.env`:

```env
APP_URL=https://api.example.com
```

Then regenerate:
```bash
php artisan config:clear
php artisan doc:generate
```

## Summary

| Configuration | Servers | Use Case |
|---------------|---------|----------|
| **None** | Auto-detects from `APP_URL` | Simple projects, MVP |
| **Single custom** | Your production URL | Most common case |
| **Multiple** | Prod + Staging + Local | Team development |
| **With variables** | Dynamic URLs | Multi-tenant, versioned APIs |

The package is designed to work perfectly with zero configuration while providing full flexibility when needed!
