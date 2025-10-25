# API Doc Kit for Laravel

API Doc Kit is a **developer-friendly package** designed to simplify API documentation for Laravel projects. It automates the generation of robust OpenAPI documentation while allowing developers to retain flexibility and control. This package reduces the repetitive effort of manually defining API specs, making the process intuitive, maintainable, and efficient.

---

## **Key Features**

| **Feature**                                      | **Description**                                                                                                         | **Status** |
|--------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------|------------|
| **Semi-Automated Documentation**                | Leverages PHP attributes to generate OpenAPI documentation with minimal configuration.                                   | ✅ Available |
| **Model-Aware Operations**                      | Automatically associates routes and parameters with Eloquent models for intelligent documentation generation.            | ✅ Available |
| **Flexible Parameter Management**               | Detects route parameters, automatically assigns types, and generates descriptions with the ability to override globally. | ✅ Available |
| **Request & Response Handling**                 | Automates OpenAPI property definitions for request classes and resource responses based on rules and model attributes.   | ✅ Available |
| **Built-in Validation**                         | Ensures all controllers, request classes, and responses adhere to defined documentation rules and highlights discrepancies.| ✅ Available |
| **SDK Generation**                              | Generates TypeScript SDKs for front-end integration, including prebuilt methods for API calls (e.g., `createUser()`).    | 🚀 Coming Soon |
| **Predefined Responses**                        | Comes with customizable default responses like `ValidationError`, `Unauthorized`, and more.                             | ✅ Available |
| **Customizable Workflow**                       | Supports custom route discovery, parameter overrides, and developer-defined extensions for unique project needs.         | ✅ Available |
| **Global Configuration**                        | Offers configuration for overriding parameter definitions, request behavior, and other package behavior.                | ✅ Available |
| **CLI Warnings and Reports**                    | Highlights issues like undefined parameters or missing descriptions directly in the CLI.                                | ✅ Available |

---

## **Installation**

⚠️ **This package is currently in active development and NOT production-ready.**

Install via Composer:

```bash
composer require ismayil-dev/api-doc-kit:dev-main
```

Publish the config file:

```bash
php artisan vendor:publish --tag=api-doc-kit-config
```

> **Development Notice:** This is an MVP release for testing and gathering feedback. APIs and features may change significantly. Use in production at your own risk.

---

## **Quick Start**

### 1. Mark your controller methods

```php
use IsmayilDev\ApiDocKit\Attributes\Resources\ApiEndpoint;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\CollectionResponse;
use IsmayilDev\ApiDocKit\Http\Responses\Contracts\SingleResourceResponse;

class UserController extends Controller
{
    #[ApiEndpoint(entity: User::class)]
    public function index(): CollectionResponse
    {
        return new ResourceResponse(User::all());
    }

    #[ApiEndpoint(entity: User::class)]
    public function show(User $user): SingleResourceResponse
    {
        return new ResourceResponse($user);
    }
}
```

### 2. Generate documentation

```bash
php artisan doc:generate
```

### 3. View generated OpenAPI docs

Your OpenAPI YAML will be at `storage/app/documentation/openapi.yaml`

Import this into Swagger UI, Postman, or any OpenAPI-compatible tool.

---

## **Why API Doc Kit?**

- **Time-Saving:** Focus on building your application while Laravel Doc Kit handles the repetitive parts of API documentation.
- **Flexible:** Use predefined rules or customize behavior to match your project’s structure and conventions.
- **Developer-Friendly:** Clean syntax, powerful defaults, and detailed documentation ensure a smooth experience.

---

## ✅ **What's Already Working**

This MVP release includes:

- **OpenAPI Documentation Generation** - Generate complete OpenAPI YAML files via `php artisan doc:generate`
- **Attribute-Based Documentation** - Use `#[ApiEndpoint]`, `#[DataSchema]`, `#[Enum]` attributes
- **DataSchema Support** - Auto-generate schemas from DTOs and data classes
- **Enum Support** - Proper OpenAPI enum generation with SDK-friendly extensions
- **Static String Entities** - Use simple strings instead of requiring model classes
- **Strict Mode Validation** - CLI warnings for undocumented endpoints and missing attributes
- **Route Coverage Validation** - Ensure all API routes have proper documentation
- **Smart Defaults** - Automatic route parameter detection, type inference, and example generation
- **Flexible Configuration** - Global config for response schemas, error codes, and validation rules

---

## 🚀 **Coming Soon**

Features in active development:

- **Laravel Resource Support** - Generate schema from Laravel Resource classes and Advanced Eloquent Model Analyse and Support
- **TypeScript SDK Generation** - Auto-generate TypeScript SDKs with type-safe methods (working in test project, will be added to package)
- **Support Query Strings** - Support for query strings in request
- **Advanced SDK Versioning** - Semantic versioning for generated SDKs
- **AI Integration** - AI-powered documentation differentiation in human-readable and machine-readable formats
- **Custom Processor Extensibility** - Public API for custom Swagger processors
- **Middleware Support** - Support for middleware in API routes

---

## 📚 **Documentation**

Detailed guides for advanced features:

- **[DataSchema Usage](docs/DATA_SCHEMA_USAGE.md)** - Auto-generate schemas from DTOs and data classes
- **[Enum Support](docs/ENUM_USAGE.md)** - Proper OpenAPI enum handling for better SDK generation
- **[Static String Entities](docs/STATIC_STRING_ENTITIES.md)** - Use strings instead of model classes
- **[Documentation Coverage](docs/DOCUMENTATION_COVERAGE.md)** - Validate all routes are documented

> More documentation coming soon as features are added!

---

## **How It Works**

API Doc Kit uses a combination of PHP attributes and smart defaults to document your API. Developers can define documentation attributes directly in controllers, request classes, and resources, while the package handles the heavy lifting.

---

## **Contributing**

Contributions are welcome! If you have ideas, bug reports, or feature requests, feel free to open an issue or submit a pull request.

---

## **License**

Laravel Doc Kit is open-source software licensed under the MIT license.
