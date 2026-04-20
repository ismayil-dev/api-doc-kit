# `operationId` Generation

The OpenAPI `operationId` for each endpoint is derived from the controller automatically. This guide explains how it works, and how to customize it.

---

## How `operationId` is generated

For single-action (`__invoke`) controllers, the operationId is built from:

1. The **controller class short name**, with the `Controller` suffix stripped
2. Any occurrences of the entity name removed (case-insensitive)
3. Optionally, the entity name (plural for collections, singular for single-resource) appended
4. The whole string camelCased

For multi-action controllers, the method name is used as the action name.

### Default behavior — entity suffix appended

By default, the entity is appended to reduce collisions when multiple endpoints share the same action name across different entities:

```php
// AccountListController with entity: 'Account'
// operationId: accountListAccounts
```

This is safe but often produces awkward names.

---

## Configuration

### `operation_id.append_entity`

Controls whether the entity name is appended to the operationId.

**Default:** `true` (backward compatible).

**When set to `false`:** the operationId is derived purely from the action name:

```php
// config/api-doc-kit.php
'operation_id' => [
    'append_entity' => false,
],
```

### Example

With `append_entity => false` and verb-first controller naming:

| Controller | operationId |
|---|---|
| `ListAccountsController` | `listAccounts` |
| `SelectAccountController` | `selectAccount` |
| `SetTeamSizeController` | `setTeamSize` |
| `CompleteOnboardingController` | `completeOnboarding` |

This pairs naturally with a project convention where controllers are named `{Verb}{Resource}Controller` and the domain lives in the namespace, not the class name.

---

## Per-endpoint override

You can always override the operationId explicitly on a specific endpoint, regardless of the global setting:

```php
#[ApiEndpoint(
    entity: User::class,
    operationId: 'listActiveUsers',
)]
public function __invoke() { /* ... */ }
```

Explicit overrides take precedence over the global guess logic.

---

## Choosing the right setting

- **`append_entity => true` (default):** safer for projects with many controllers named after the entity (e.g. `ListController` inside `UserController` directory). Produces unique IDs out of the box.
- **`append_entity => false`:** cleaner IDs if your controllers are already verb-first and disambiguated by name. Recommended if you also consume the generated OpenAPI to build a TypeScript SDK, where clean operationIds become function names.

The setting only affects the guess logic. Per-endpoint `operationId` overrides work in both modes.