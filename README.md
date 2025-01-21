# Laravel Doc Kit

Laravel Doc Kit is a **developer-friendly package** designed to simplify API documentation for Laravel projects. It automates the generation of robust OpenAPI documentation while allowing developers to retain flexibility and control. This package reduces the repetitive effort of manually defining API specs, making the process intuitive, maintainable, and efficient.

---

## **Key Features**

| **Feature**                                      | **Description**                                                                                                         |
|--------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------|
| **Semi-Automated Documentation**                | Leverages PHP attributes to generate OpenAPI documentation with minimal configuration.                                   |
| **Model-Aware Operations**                      | Automatically associates routes and parameters with Eloquent models for intelligent documentation generation.            |
| **Flexible Parameter Management**               | Detects route parameters, automatically assigns types, and generates descriptions with the ability to override globally. |
| **Request & Response Handling**                 | Automates OpenAPI property definitions for request classes and resource responses based on rules and model attributes.   |
| **Built-in Validation**                         | Ensures all controllers, request classes, and responses adhere to defined documentation rules and highlights discrepancies.|
| **SDK Generation**                              | Generates TypeScript SDKs for front-end integration, including prebuilt methods for API calls (e.g., `createUser()`).    |
| **Predefined Responses**                        | Comes with customizable default responses like `ValidationError`, `Unauthorized`, and more.                             |
| **Customizable Workflow**                       | Supports custom route discovery, parameter overrides, and developer-defined extensions for unique project needs.         |
| **Global Configuration**                        | Offers configuration for overriding parameter definitions, request behavior, and other package behavior.                |
| **CLI Warnings and Reports**                    | Highlights issues like undefined parameters or missing descriptions directly in the CLI.                                |

---

## **Installation**

Coming soon...

---

## **Why Laravel Doc Kit?**

- **Time-Saving:** Focus on building your application while Laravel Doc Kit handles the repetitive parts of API documentation.
- **Flexible:** Use predefined rules or customize behavior to match your project’s structure and conventions.
- **Developer-Friendly:** Clean syntax, powerful defaults, and detailed documentation ensure a smooth experience.

---

## **Planned Features**

This package is still under development. Here's a glimpse of what's coming:

- **Dynamic OpenAPI Documentation:** Fully generated OpenAPI YAML/JSON files for integration with tools like Swagger UI or Postman.
- **Advanced SDK Versioning:** Include semantic versioning for SDKs, ensuring compatibility across environments.
- **Custom Processor Extensibility:** Allow developers to define custom Swagger processors for their specific needs.
- **Detailed Warnings:** CLI-based warnings for undocumented endpoints, inconsistent parameter definitions, and more.

---

## **How It Works**

Laravel Doc Kit uses a combination of PHP attributes and smart defaults to document your API. Developers can define documentation attributes directly in controllers, request classes, and resources, while the package handles the heavy lifting.

---

## **Contributing**

Contributions are welcome! If you have ideas, bug reports, or feature requests, feel free to open an issue or submit a pull request.

---

## **License**

Laravel Doc Kit is open-source software licensed under the MIT license.
