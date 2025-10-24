# Route Filtering - Control Which Routes are Documented

Configure smart route filtering to control which Laravel routes are included in your OpenAPI documentation.

## The Problem

Laravel applications often have routes that shouldn't appear in API documentation:

- **System routes**: Health checks (`/up`), CSRF endpoints (`sanctum/csrf-cookie`)
- **Debug tools**: Telescope, Horizon, Debugbar, Ignition
- **Internal endpoints**: Admin panels, webhooks, background jobs
- **Closure routes**: Routes without traditional controllers

Without filtering, these routes can cause errors or clutter your documentation.

## Three-Layer Filtering System

The package uses a smart three-layer filtering approach:

### Layer 1: Route File Filtering
Only scan specific route files (e.g., `api.php`, `web.php`)

### Layer 2: Path Pattern Exclusion
Exclude routes matching regex patterns (e.g., `^sanctum/`, `^admin/`)

### Layer 3: Controller-less Route Skipping
Automatically skip routes without controllers (closure routes, etc.)

---

## Default Behavior (Zero Configuration)

By default, the package applies sensible filters:

```bash
php artisan doc:generate
```

**Default configuration:**
- **Route files**: Only `api.php` (ignores `web.php`, `console.php`, etc.)
- **Excluded paths**: Sanctum, Ignition, Livewire, Telescope, Horizon, Debugbar
- **Skip controller-less**: `true` (automatically skips closure routes)

This works great for most Laravel applications with minimal setup!

---

## Configuration

All route filtering is configured in `config/api-doc-kit.php`:

```php
'routes' => [
    // Layer 1: Route file filtering
    'files' => ['api.php'],

    // Layer 2: Path pattern exclusion
    'exclude_paths' => [
        '^sanctum/',
        '^_ignition/',
        '^livewire/',
        '^telescope',
        '^horizon',
        '^_debugbar',
    ],

    // Layer 3: Controller-less route skipping
    'skip_controller_less' => true,
],
```

---

## Layer 1: Route File Filtering

### Only API Routes (Default)

```php
'files' => ['api.php'],
```

This is perfect for API-only documentation. Routes from `web.php`, `console.php`, etc. are ignored.

### API + Web Routes

```php
'files' => ['api.php', 'web.php'],
```

Use this if you have web routes that return JSON and should be documented.

### Multiple API Versions

```php
'files' => ['api.php', 'api-v2.php', 'api-v3.php'],
```

Useful for versioned APIs where each version has its own route file.

### All Route Files

```php
'files' => ['*'],  // Not recommended - includes web.php, channels.php, console.php, etc.
```

⚠️ **Warning**: This will include ALL routes, including web routes, which may not be appropriate for API documentation.

---

## Layer 2: Path Pattern Exclusion

### Default Exclusions

The default configuration excludes common Laravel system routes:

```php
'exclude_paths' => [
    '^sanctum/',        // Sanctum CSRF cookie endpoint
    '^_ignition/',      // Ignition debug screens
    '^livewire/',       // Livewire endpoints
    '^telescope',       // Laravel Telescope
    '^horizon',         // Laravel Horizon
    '^_debugbar',       // Laravel Debugbar
],
```

### Custom Exclusions

Add your own patterns to exclude specific routes:

```php
'exclude_paths' => [
    // ... default exclusions ...

    // Admin panel
    '^admin/',

    // Internal API
    '^internal/',

    // Webhook handlers (if not documented)
    '^webhooks/',

    // Legacy endpoints
    '^legacy/',
    '^v1/deprecated/',

    // Background job endpoints
    '^jobs/',
    '^queue/',
],
```

### Pattern Syntax

Patterns use **regex syntax** without delimiters:

| Pattern | Matches | Examples |
|---------|---------|----------|
| `^admin/` | Starts with "admin/" | `admin/users`, `admin/settings` |
| `api/v1$` | Ends with "api/v1" | `api/v1` |
| `^(up\|health)$` | Exactly "up" or "health" | `up`, `health` |
| `test` | Contains "test" | `api/test`, `test/endpoint`, `latest` |
| `^api/(internal\|private)/` | Starts with "api/internal/" or "api/private/" | `api/internal/stats` |

**Example matches:**

```php
// Pattern: '^admin/'
'admin/users'           // ✅ Matches
'admin/posts/123'       // ✅ Matches
'api/admin/settings'    // ❌ Doesn't match (doesn't START with admin/)

// Pattern: '^api/v[0-9]+/internal'
'api/v1/internal/stats' // ✅ Matches
'api/v2/internal/logs'  // ✅ Matches
'api/internal/stats'    // ❌ Doesn't match (missing version)
```

---

## Layer 3: Controller-less Route Skipping

### Enable (Default)

```php
'skip_controller_less' => true,
```

Routes without controllers are automatically skipped. This includes:
- Closure routes: `Route::get('/test', fn() => 'test')`
- Laravel system routes: `/up` health check
- View routes: `Route::view('/welcome', 'welcome')`

### Disable

```php
'skip_controller_less' => false,
```

⚠️ **Warning**: Disabling this may cause errors if you have closure routes, as they can't be processed for documentation.

---

## Use Cases

### 1. API-Only Documentation (Default)

**Scenario**: Document only API routes, exclude web and system routes

```php
'routes' => [
    'files' => ['api.php'],
    'exclude_paths' => [
        '^sanctum/',
        '^_ignition/',
        '^livewire/',
        '^telescope',
        '^horizon',
        '^_debugbar',
    ],
    'skip_controller_less' => true,
],
```

### 2. Multi-Version API

**Scenario**: Document multiple API versions from separate route files

```php
'routes' => [
    'files' => ['api-v1.php', 'api-v2.php'],
    'exclude_paths' => [
        '^sanctum/',
    ],
    'skip_controller_less' => true,
],
```

### 3. Public + Internal APIs

**Scenario**: Document all API routes but exclude internal/admin endpoints

```php
'routes' => [
    'files' => ['api.php'],
    'exclude_paths' => [
        '^api/internal/',
        '^api/admin/',
        '^api/webhooks/',  // Webhooks might have different docs
        '^sanctum/',
    ],
    'skip_controller_less' => true,
],
```

### 4. Mixed Web + API Application

**Scenario**: Document both web and API routes that return JSON

```php
'routes' => [
    'files' => ['api.php', 'web.php'],
    'exclude_paths' => [
        // Exclude typical web routes
        '^login',
        '^register',
        '^password/',
        '^email/verify',

        // Exclude admin panel
        '^admin/',

        // System routes
        '^sanctum/',
        '^telescope',
        '^horizon',
    ],
    'skip_controller_less' => true,
],
```

### 5. Development vs Production

**Scenario**: Include debug routes in local, exclude in production

**AppServiceProvider.php:**
```php
public function boot()
{
    if (app()->environment('local')) {
        // In local, allow all routes
        config(['api-doc-kit.routes.exclude_paths' => ['^sanctum/']]);
    } else {
        // In production, exclude more routes
        config([
            'api-doc-kit.routes.exclude_paths' => [
                '^sanctum/',
                '^_ignition/',
                '^telescope',
                '^horizon',
                '^api/internal/',
            ],
        ]);
    }
}
```

---

## Troubleshooting

### Routes Not Appearing in Documentation

**Problem:** Expected routes are missing from generated OpenAPI documentation

**Possible causes:**

1. **Route file not in `files` array**

   ```php
   // ❌ Wrong - api-v2.php not included
   'files' => ['api.php'],

   // ✅ Correct
   'files' => ['api.php', 'api-v2.php'],
   ```

2. **Path matches exclusion pattern**

   Check if route path matches any pattern in `exclude_paths`:

   ```php
   // If you have this pattern:
   'exclude_paths' => ['^api/'],

   // Then this route will be excluded:
   Route::get('api/users', [UserController::class, 'index']);

   // ✅ Fix: Remove overly broad pattern
   'exclude_paths' => ['^api/internal/'],
   ```

3. **Controller-less route**

   ```php
   // This route will be skipped if skip_controller_less = true
   Route::get('/test', fn() => 'test');

   // ✅ Fix: Add a controller or set skip_controller_less = false
   Route::get('/test', [TestController::class, 'show']);
   ```

### Errors During Generation

**Problem:** `php artisan doc:generate` fails with route-related errors

**Solution 1**: Check logs for "Route not found" debug messages

```bash
tail -f storage/logs/laravel.log
```

Look for:
```
Route not found for #[ApiEndpoint] on App\Http\Controllers\UserController@index.
This could be due to route filtering settings or missing route registration.
```

**Solution 2**: Verify route is registered

```bash
php artisan route:list --path=users
```

**Solution 3**: Check route file matches configuration

```bash
# List all routes and their source files
php artisan route:list -v
```

### System Routes Still Appearing

**Problem:** Sanctum/Telescope routes appear in documentation despite exclusion

**Solution:** Ensure patterns are correct

```php
// ❌ Wrong - missing leading ^
'exclude_paths' => ['sanctum/'],  // This matches 'api/sanctum/' too

// ✅ Correct
'exclude_paths' => ['^sanctum/'],  // Only matches routes STARTING with 'sanctum/'
```

### Too Many Routes Excluded

**Problem:** Legitimate routes are being excluded

**Solution:** Make patterns more specific

```php
// ❌ Too broad - excludes 'api/users', 'api/posts', etc.
'exclude_paths' => ['^api/'],

// ✅ More specific
'exclude_paths' => [
    '^api/internal/',
    '^api/admin/',
    '^api/webhooks/',
],
```

---

## Best Practices

### 1. Start with Defaults

The default configuration works for most applications. Only customize if needed.

```php
// ✅ Good - use defaults
'files' => ['api.php'],

// ❌ Avoid over-configuring
'files' => ['api.php', 'web.php', 'channels.php'],  // Probably too much
```

### 2. Use Specific Patterns

Prefer specific patterns over broad ones:

```php
// ✅ Good - specific
'exclude_paths' => [
    '^admin/dashboard',
    '^admin/users',
],

// ❌ Avoid - too broad
'exclude_paths' => ['^admin/'],  // Might exclude too much
```

### 3. Document Your Exclusions

Add comments explaining why routes are excluded:

```php
'exclude_paths' => [
    '^sanctum/',        // Authentication endpoints
    '^admin/',          // Admin panel - separate documentation
    '^api/internal/',   // Internal microservice communication
],
```

### 4. Test Your Configuration

After changing filters, verify the results:

```bash
php artisan doc:generate
cat storage/app/documentation/openapi.yaml | grep "paths:" -A 50
```

### 5. Keep Controller-less Skipping Enabled

Unless you have a specific reason, keep this enabled:

```php
'skip_controller_less' => true,  // ✅ Recommended
```

---

## Advanced: Dynamic Filtering

### Environment-Based Filtering

**AppServiceProvider.php:**
```php
public function boot()
{
    // Customize filters based on environment
    $excludePaths = config('api-doc-kit.routes.exclude_paths');

    if (app()->environment('production')) {
        // Exclude debug routes in production
        $excludePaths = array_merge($excludePaths, [
            '^_debugbar',
            '^telescope',
            '^horizon',
        ]);
    }

    config(['api-doc-kit.routes.exclude_paths' => $excludePaths]);
}
```

### Feature Flag Integration

```php
public function boot()
{
    $excludePaths = config('api-doc-kit.routes.exclude_paths');

    // Exclude experimental API routes if feature is disabled
    if (!config('features.new_api_enabled')) {
        $excludePaths[] = '^api/v2/';
    }

    config(['api-doc-kit.routes.exclude_paths' => $excludePaths]);
}
```

---

## Summary

| Layer | What It Does | Use Case |
|-------|--------------|----------|
| **Route Files** | Only scan specific route files | Separate API from web routes |
| **Path Exclusion** | Exclude routes by regex pattern | Filter admin, internal, system routes |
| **Controller-less Skip** | Skip closure/view routes | Avoid errors from non-controller routes |

**Most common configuration:**

```php
'routes' => [
    'files' => ['api.php'],                    // API routes only
    'exclude_paths' => [/* system routes */],  // Use defaults
    'skip_controller_less' => true,            // Skip closures
],
```

The package is designed to work perfectly with zero configuration while providing full flexibility when needed!
