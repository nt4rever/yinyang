---
name: project-code-conventions
description: "Applies YinYang Laravel project architecture, naming, repository, service, request, resource, database, and testing conventions. Use when creating or reviewing project code, adding Laravel classes, designing APIs, naming methods, or deciding where logic belongs."
---

# Project Code Conventions

## Project Context

YinYang is a Laravel 12 application using PostgreSQL, Redis, MinIO-compatible S3 storage, Sanctum authentication, and Telescope monitoring. Follow the existing Laravel Boost guidance and local code style when a nearby file shows a stronger convention.

## When to Apply

Use this skill when:

- Creating or reviewing controllers, services, repositories, models, form requests, resources, exceptions, middleware, migrations, factories, or tests.
- Choosing class, method, variable, table, column, foreign key, pivot table, or UUID conventions.
- Deciding whether logic belongs in the HTTP, service, repository, or model layer.
- Designing API responses, validation flow, data access, caching, or business exceptions.

## Architecture Layers

- Controllers live in `app/Http/Controllers/`. Use `{Model}Controller`; put API endpoints under `Api/`. Controllers handle HTTP concerns and delegate business work to services.
- Services live in `app/Services/`. Use `{Model}Service`; services orchestrate business logic and depend on repositories or other services.
- Repositories live in `app/Repositories/`. Use `{Model}Repository`; use `{Model}ReadRepository` and `{Model}WriteRepository` for contracts when separating read and write behavior.
- Models live in `app/Models/`. Use singular PascalCase names; models own Eloquent relationships, casts, scopes, and ORM behavior.
- Requests live in `app/Http/Requests/`. Use `{Model}{Action}Request`; requests own validation and authorization.
- Resources live in `app/Http/Resources/`. Use `{Model}Resource`, `{Model}Collection`, or `{Model}{Purpose}Resource`; resources transform API responses.
- Exceptions live in `app/Exceptions/`. Use descriptive exception classes for custom error handling and business rule failures.
- Middleware live in `app/Http/Middleware/` when present. Use `{Purpose}Middleware`; middleware handles request and response processing.

## Naming Rules

- Controllers: `UserController`, `Api\UserController`.
- Services: `UserService`, `AuthService`.
- Repositories: `UserRepository`, `EloquentUserRepository`, `UserReadRepository`, `UserWriteRepository`.
- Models: `User`, `PersonalAccessToken`.
- Requests: `UserCreateRequest`, `UserUpdateRequest`, `UserIndexRequest`, `UserShowRequest`, `UserDeleteRequest`.
- Resources: `UserResource`, `UserCollection`, `UserNameListResource`.
- Exceptions: `ConflictException`, `ValidationException`, or a clear domain exception name.
- Middleware: `SetLocale`, `Authenticate`.
- Variables: use `$user` for one model, `$users` for many models, `$userCollection` for collections, `$userService` for services, and `$userRepository` for repositories.

## Method Rules

- Controller methods should use conventional REST names: `index()`, `show()`, `store()`, `update()`, and `destroy()`.
- Service methods should use clear verbs such as `create()`, `update()`, `delete()`, `getAll()`, and `find()`.
- Repository methods should use data access names such as `find()`, `create()`, `update()`, `delete()`, and `getBy...()`.
- Use `get...` methods when the record is expected to exist and the method should throw a not-found exception.
- Use `find...` methods when absence is valid and the method should return `null` or an empty collection.
- Use `findOneBy{Field}()` for nullable single-record lookups.
- Use `findBy{Field}()` or `findAllBy{Field}()` for collection lookups.
- Use special names such as `get{Model}OrFail()` and `find{Model}OrCreate()` only when the behavior is explicit and useful.

## Database Rules

- Tables use plural snake_case names, such as `users` and `personal_access_tokens`.
- Columns use snake_case names, such as `user_id` and `email_verified_at`.
- Foreign keys use `{model}_id`, such as `user_id` and `token_id`.
- Pivot tables use singular model names in snake_case, such as `user_role` and `model_tag`.
- Use UUIDv7 primary keys for all models unless nearby code shows a different established pattern.
- In models with UUID primary keys, set `public $keyType = 'string'` and `public $incrementing = false`.
- In migrations, use `uuid()` for UUID columns.

## Design Principles

- Keep each class focused on one responsibility.
- Use constructor dependency injection for dependencies.
- Keep the layered flow: Controllers -> Services -> Repositories -> Models.
- Prefer interfaces when they clarify repository read and write responsibilities.
- Use custom exceptions for business logic errors.
- Use explicit PHP parameter and return types.
- Use descriptive names over abbreviations.
- Reuse existing components and patterns before adding new abstractions.

## Verification

Before finalizing code changes:

- Check sibling files for matching structure, namespace, naming, and style.
- Run the relevant targeted tests for the changed behavior.
- Run the configured formatter or linter required by the project.
- Confirm the code follows this skill and Laravel Boost guidance.
