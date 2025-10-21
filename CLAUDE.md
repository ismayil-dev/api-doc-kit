# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**api-doc-kit** is a Laravel package for generating OpenAPI documentation and TypeScript SDKs. It uses PHP attributes to semi-automate API documentation generation, reducing manual effort while maintaining flexibility.

## Development Commands

### Testing
```bash
# Run all tests
vendor/bin/pest

# Run a specific test
vendor/bin/pest tests/Feature/Entities/DocEntityTest.php
```

### Code Style
```bash
# Run code linter (Laravel Pint)
composer lint
# or
vendor/bin/pint
```

### Documentation Generation
```bash
# Generate OpenAPI documentation (requires Laravel app context)
php artisan doc:generate

# Scan models
php artisan doc:scan-models

# Publish config file
php artisan vendor:publish --tag=api-doc-kit-config
```

## Architecture Overview

### Core Processing Flow

The package uses a processor-based architecture that intercepts OpenAPI generation:

1. **ApiResourceProcessor** (`src/Processors/ApiResourceProcessor.php`) - Main processor that:
   - Finds `#[ApiEndpoint]` attributes on controller methods
   - Matches them to Laravel routes via `RouteMapper`
   - Resolves entities and response types using reflection
   - Auto-detects request classes from controller method signatures
   - Generates OpenAPI operations (GET, POST, PATCH, PUT, DELETE)
   - Adds smart HTTP method-based error responses (see Smart Error Response Handling below)

2. **ResponseResourceProcessor** (`src/Processors/ResponseResourceProcessor.php`) - Processes `#[ResponseResource]` attributes to generate OpenAPI schemas for API resources

3. **ModelSchemaProcessor** (`src/Processors/ModelSchemaProcessor.php`) - Processes model attributes to generate schemas

### Key Components

**Attributes System:**
- `#[ApiEndpoint]` - Applied to controller methods to mark them for documentation
  - Parameters: `entity`, `requestClass`, `actionName`, `responseEntity`
  - Optional: `errorResponses`, `successResponseSchema`, `errorResponseSchemas`
  - Auto-generates `operationId`, `description`, and `tags` based on entity
- `#[ErrorResponses]` - Controls which error responses are included
  - `only: [400, 422]` - Include only specific status codes
  - `except: [404]` - Exclude specific status codes from defaults
- `#[ResponseResource]` - Applied to API resource classes to define response schemas
- `#[ApiResponse]` - Wraps OpenAPI responses with status codes and content types

**Entity Resolution:**
- `DocEntity` - Represents a model/entity in documentation context
  - Generates plural names, descriptions, tags, and parameter descriptions
  - Convention: Entity name + "Resource" for resource schemas
- `EntityResolver` - Resolves entity strings to `DocEntity` instances

**Request Body Building:**
- `RequestBodyBuilder` - Automatically generates OpenAPI request body schemas from Laravel FormRequest validation rules
  - Maps Laravel validation rules to OpenAPI types (string, integer, boolean, etc.)
  - Extracts required fields from validation rules
  - Supports nested rules and complex types

**Route System:**
- `RouteMapper` - Extracts Laravel routes and matches them to controllers
  - Filters routes starting with 'App\\'
  - Detects route parameters and their optional status
  - Handles both standard controller methods and invokable controllers
- `RoutePathParameterBuilder` - Builds OpenAPI parameter objects for route parameters

**Response Types:**
The package uses contract interfaces to determine response structure:
- `SingleResourceResponse` - Single entity response (200)
- `CollectionResponse` - Array of entities (200)
- `PaginatedResponse` - Paginated collection (200)
- `CreatedResponse` - Resource created (201)
- `UpdatedResponse` - Resource updated (200)
- `EmptyResponse` - No content (204)

Controller methods must have return type hints using these contracts.

**Response Content Builders:**
- `JsonRefContent` - References a schema
- `JsonCollectionContent` - Wraps ref in array/collection structure
- `JsonPaginatedContent` - Wraps ref in pagination structure
- `JsonErrorContent` - Standard error response format

### Processing Pipeline

The package hooks into the zircote/swagger-php generation pipeline:
1. Processors are inserted before `BuildPaths`
2. `ResponseResourceProcessor` runs first
3. `ApiResourceProcessor` runs second
4. Each processor transforms annotations before final OpenAPI generation

### Directory Structure

- `src/Attributes/` - PHP attribute definitions
  - `Resources/ApiEndpoint.php` - Main controller attribute
  - `Responses/` - Response-related attributes
  - `Schema/` - Schema attributes (ResponseResource, ModelSchema)
  - `Parameters/` - Query and route parameter attributes
  - `Properties/` - Property type attributes
- `src/Processors/` - OpenAPI processors that transform attributes
- `src/Models/` - Entity resolution and model mapping
- `src/Routes/` - Route discovery and parameter building
- `src/Http/` - Request body building and response contracts
- `src/Console/Commands/` - Artisan commands
- `src/Enums/` - OpenAPI type enums

### Conventions

**Naming:**
- Entity resources are named `{Entity}Resource` (e.g., `UserResource`)
- Operation IDs are camelCase: `{action}{Entity}` or `{action}{Entities}` for collections
- Tags are plural headline case: `Str::plural(Str::headline($entityName))`

**Type Detection:**
- Request classes are auto-detected from controller method parameters
- Response types are determined by return type hints
- Route parameters are extracted from Laravel route definitions

**Description Generation:**
- Auto-generated from entity name and action name
- Example: "Update User", "Get Users", "Create Post"

## Smart Error Response Handling

The package intelligently determines which error responses to include based on HTTP method, reducing documentation noise while maintaining accuracy.

### Default Error Responses by HTTP Method

- **GET**: 401, 403, 404, 429, 500
- **POST**: 400, 401, 403, 422, 429, 500
- **PATCH/PUT**: 400, 401, 403, 404, 422, 429, 500
- **DELETE**: 401, 403, 404, 429, 500

### Overriding Error Responses

**Global Config Override (affects all endpoints):**

In `config/api-doc-kit.php`:

```php
'responses' => [
    'error' => [
        'defaults_per_method' => [
            'GET' => [
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_FORBIDDEN,
                Response::HTTP_NOT_FOUND,
            ],
            'POST' => [
                Response::HTTP_BAD_REQUEST,
                Response::HTTP_UNAUTHORIZED,
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ],
            // PATCH, PUT, DELETE...
        ],
    ],
],
```

**Per-Endpoint Override (using attributes):**

```php
// Include only specific errors
#[ApiEndpoint(
    entity: User::class,
    errorResponses: new ErrorResponses(only: [
        Response::HTTP_UNAUTHORIZED,
        Response::HTTP_UNPROCESSABLE_ENTITY,
    ])
)]
public function store(CreateUserRequest $request): CreatedResponse

// Exclude specific errors from defaults
#[ApiEndpoint(
    entity: User::class,
    errorResponses: new ErrorResponses(except: [Response::HTTP_NOT_FOUND])
)]
public function index(): CollectionResponse
```

## Custom Response Schema Overrides

Developers can override response structures at three levels: per-endpoint (attribute), globally (config), or use defaults.

### ResponseSchemaBuilder Service

`src/Http/Responses/ResponseSchemaBuilder.php` handles response schema resolution:
- Checks for attribute-level overrides first
- Falls back to config-level overrides
- Uses default implementations as last resort

### Per-Endpoint Custom Schemas (Attribute-Level)

```php
// Custom success response schema
#[ApiEndpoint(
    entity: User::class,
    successResponseSchema: CustomUserResponseContent::class
)]
public function show(User $user): SingleResourceResponse

// Custom error response schemas per status code
#[ApiEndpoint(
    entity: User::class,
    errorResponseSchemas: [
        422 => CustomValidationErrorContent::class,
        400 => CustomBadRequestContent::class,
    ]
)]
public function store(CreateUserRequest $request): CreatedResponse
```

### Global Custom Schemas (Config-Level)

In `config/api-doc-kit.php`:

```php
'responses' => [
    'success' => [
        'single' => \App\OpenApi\Schemas\SingleResourceContent::class,
        'collection' => \App\OpenApi\Schemas\CollectionContent::class,
        'paginated' => \App\OpenApi\Schemas\PaginatedContent::class,
        'created' => null,  // Use default
        'updated' => null,  // Use default
        'empty' => null,    // Use default
    ],
    'error' => [
        // Global error schema for all error responses
        'schema' => \App\OpenApi\Schemas\ErrorContent::class,

        // Per-status-code error schemas (overrides global)
        'per_status' => [
            400 => \App\OpenApi\Schemas\BadRequestContent::class,
            422 => \App\OpenApi\Schemas\ValidationErrorContent::class,
        ],

        // Custom descriptions for error responses
        'descriptions' => [
            400 => 'Invalid request parameters provided',
            401 => 'Authentication required',
            422 => 'The given data failed validation',
        ],
    ],
],
```

### Resolution Priority

**Success Responses:**
1. Attribute `successResponseSchema` parameter
2. Config `responses.success.{type}`
3. Default implementation

**Error Responses:**
1. Attribute `errorResponseSchemas[$statusCode]`
2. Config `responses.error.per_status[$statusCode]`
3. Config `responses.error.schema` (global override)
4. Default `JsonErrorContent`

**Error Status Codes (which errors to include):**
1. Attribute `#[ErrorResponses]` (only/except)
2. Config `responses.error.defaults_per_method[{METHOD}]`
3. Package default for HTTP method

**Error Descriptions:**
1. Attribute-level custom description (not yet implemented in attribute)
2. Config `responses.error.descriptions[$statusCode]`
3. Default descriptions
