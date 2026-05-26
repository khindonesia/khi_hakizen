# Coding Conventions & Standards

This document establishes the official coding standards, formatting guidelines, and architectural conventions for the KHI Hakizen application.

## 1. General Standards

- **Editor Configuration**: UTF-8 encoding, LF line endings, 4-space indentation, and trailing whitespaces removed (governed by `.editorconfig`).
- **Formatting & Style**: Match surrounding styles. Ensure code looks clean and cohesive.

## 2. PHP Language Standards

- **Control Structures**: Always use curly braces for control structures, even for single-line bodies.
  ```php
  if ($condition) {
      return true;
  }
  ```
- **Constructor Promotion**: Use PHP 8 constructor property promotion in `__construct()` for dependency injection.
  ```php
  public function __construct(
      public GitHub $github,
      private readonly XenditInvoiceGateway $xenditGateway,
  ) {
  }
  ```
  Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.
- **Type Declarations**: Always use explicit return type declarations and appropriate parameter type hints.
  ```php
  protected function isAccessible(User $user, ?string $path = null): bool
  {
      // ...
  }
  ```
- **Enums**: Keys in Enums must be `TitleCase` (e.g., `FavoritePerson`, `BestLake`, `Monthly`).
- **Comments**:
  - Prefer PHPDoc blocks over inline comments.
  - Never write comments inside the code block itself unless the logic is exceptionally complex.
  - Add array shape type definitions in PHPDoc blocks when appropriate.

## 3. Laravel Framework Conventions

- **Artisan Generators**: Always use `php artisan make:` commands with `--no-interaction` to generate new files (migrations, controllers, models, etc.) to keep them idiomatic.
- **Eloquent ORM**:
  - Eager load relationships using `with()` to prevent N+1 query performance issues.
  - Prefer Eloquent models and relationship methods over raw DB queries.
  - Avoid `DB::`; prefer `Model::query()`.
  - Casts must be set in a `casts()` method on the model rather than the legacy `$casts` property.
- **Controllers & Validation**:
  - Keep controllers thin. Delegate complex logic to **Action** classes.
  - Always create Form Request classes in `app/Http/Requests/` for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- **Authentication & Authorization**:
  - Leverage Laravel's built-in authentication (via Wave hooks / Spatie Permissions).
  - Protect routes using middleware blocks.
- **URL Generation**:
  - Always use named routes and the `route()` function for generating URLs.

## 4. Livewire & Volt Reactivity

- State lives on the server; the UI reflects it.
- **Volt Components**: Use Livewire Volt for single-file components located in the active theme folder. Check sibling Volt files to ensure consistency between class-based and functional APIs.
- Validate and authorize directly in Volt actions.

## 5. Filament server-driven UI

- Use Filament resources and pages for administrative dashboard operations.
- Always utilize static `make()` methods for component initialization.
- Use the `relationship()` method on form select, checkbox, and repeater fields to let Filament handle options and DB saving natively.

---
*Last updated: 2026-05-26 after initialization*
