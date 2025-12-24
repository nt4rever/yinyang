# YinYang Project Rules & Standards

## Project Overview
- **Framework**: Laravel 12.0+
- **PHP**: 8.2+
- **Database**: PostgreSQL 17
- **Cache**: Redis 7.4
- **Storage**: MinIO (S3-compatible)
- **Auth**: Laravel Sanctum
- **Monitoring**: Laravel Telescope

## Architecture Layers

### 1. Controllers (`app/Http/Controllers/`)
- **Naming**: `{Model}Controller` (e.g., `UserController`)
- **Location**: `Api/` for API endpoints, root for web
- **Responsibility**: Handle HTTP requests, delegate to services
- **Methods**: `index`, `show`, `store`, `update`, `destroy`

### 2. Services (`app/Services/`)
- **Naming**: `{Model}Service` (e.g., `UserService`)
- **Responsibility**: Business logic, orchestration
- **Dependencies**: Inject repositories, other services
- **Methods**: `create()`, `update()`, `delete()`, `getAll()`

### 3. Repositories (`app/Repositories/`)
- **Naming**: `{Model}Repository` (e.g., `UserRepository`)
- **Interfaces**: `{Model}ReadRepository`, `{Model}WriteRepository`
- **Responsibility**: Data access, caching, query logic
- **Methods**: `find()`, `create()`, `update()`, `delete()`

### 4. Models (`app/Models/`)
- **Naming**: `{Model}` (singular, PascalCase)
- **Responsibility**: Eloquent ORM, relationships, scopes
- **Conventions**: Use fillable/guarded, implement relationships

### 5. Requests (`app/Http/Requests/`)
- **Naming**: `{Model}{Action}Request` (e.g., `UserCreateRequest`)
- **Types**: `Create`, `Update`, `Index`, `Show`, `Delete`
- **Responsibility**: Validation, authorization
- **Methods**: `rules()`, `authorize()`, `messages()`

### 6. Resources (`app/Http/Resources/`)
- **Single**: `{Model}Resource` (e.g., `UserResource`)
- **Collection**: `{Model}Collection` (e.g., `UserCollection`)
- **Custom**: `{Model}{Purpose}Resource` (e.g., `UserNameListResource`)
- **Responsibility**: Data transformation, API response formatting

### 7. Exceptions (`app/Exceptions/`)
- **Naming**: `{Model}Exception` (e.g., `ConflictException`)
- **Responsibility**: Custom error handling, business logic exceptions

### 8. Middleware (`app/Http/Middleware/`)
- **Naming**: `{Purpose}Middleware` (e.g., `SetLocale`)
- **Responsibility**: Request/response processing, authentication

## Naming Conventions

### Files & Classes
- **Controllers**: `UserController`, `Api\UserController`
- **Services**: `UserService`, `AuthService`
- **Repositories**: `UserRepository`, `EloquentUserRepository`
- **Models**: `User`, `PersonalAccessToken`
- **Requests**: `UserCreateRequest`, `UserUpdateRequest`
- **Resources**: `UserResource`, `UserCollection`
- **Exceptions**: `ConflictException`, `ValidationException`
- **Middleware**: `SetLocale`, `Authenticate`

### Methods
- **CRUD**: `index()`, `show()`, `store()`, `update()`, `destroy()`
- **Services**: `create()`, `update()`, `delete()`, `getAll()`, `find()`
- **Repositories**: `find()`, `create()`, `update()`, `delete()`, `getBy()`

#### **Repository Method Naming: `find` vs `get`**
- **`get` methods**: Expect object to exist, throw `NotFound` exception if not found
  - `getById(1)` - Returns single object or throws exception
  - `getUser(1)` - Returns user or throws exception
- **`find` methods**: Object might not exist, return `null` or empty collection
  - `findById(1)` - Returns object or `null`
  - `findByEmail('user@example.com')` - Returns user or `null`
  - `findByStatus('active')` - Returns collection (empty if no matches)
  - `findOneByEmail('user@example.com')` - Returns single object or `null`

#### **Method Naming Patterns**
- **Single Object**: `get{Model}()`, `find{Model}()`, `findOneBy{Field}()`
- **Multiple Objects**: `findBy{Field}()`, `findAllBy{Field}()`
- **Special Cases**: `get{Model}OrFail()`, `find{Model}OrCreate()`

### Variables & Properties
- **Models**: `$user`, `$users`
- **Collections**: `$userCollection`
- **Services**: `$userService`
- **Repositories**: `$userRepository`

### Database
- **Tables**: `users`, `personal_access_tokens` (snake_case, plural)
- **Columns**: `user_id`, `email_verified_at` (snake_case)
- **Foreign Keys**: `user_id`, `token_id`
- **Pivot Tables**: `user_role`, `model_tag`
- **Primary Keys**: Use UUIDv7 for all models. Set `public $keyType = 'string'` and `public $incrementing = false` in models. Use `uuid()` in migrations

## Directory Structure
```
app/
├── Exceptions/          # Custom exceptions
├── Factory/            # Model factories
├── Http/               # HTTP layer
│   ├── Controllers/    # Controllers
│   ├── Middleware/     # Middleware
│   ├── Requests/       # Form requests
│   └── Resources/      # API resources
├── ModelFilters/       # Eloquent filters
├── Models/             # Eloquent models
├── Providers/          # Service providers
├── Repositories/       # Data access layer
└── Services/           # Business logic layer
```

## Key Principles
1. **Single Responsibility**: Each class has one clear purpose
2. **Dependency Injection**: Use constructor injection for dependencies
3. **Interface Segregation**: Separate read/write repository interfaces
4. **Layered Architecture**: Controllers → Services → Repositories → Models
5. **Consistent Naming**: Follow established patterns across all layers
6. **Type Safety**: Use strict typing and return types
7. **Error Handling**: Custom exceptions for business logic errors

## Quick Commands
```bash
# Generate classes
php artisan make:controller Api/UserController
php artisan make:service UserService
php artisan make:repository UserRepository
php artisan make:request User/CreateUserRequest
php artisan make:resource UserResource
php artisan make:exception UserException

# Development
make up                    # Start containers
composer test             # Run tests
php artisan migrate       # Run migrations
```

---
*Version: 1.0 - Focused on Architecture & Naming* 
