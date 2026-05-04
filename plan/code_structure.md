# SmartShop Mini Code Structure

## 1. Architecture Pattern
- Livewire components handle UI interaction and call services.
- Data transfer objects (DTOs) centralize validation rules and payload mapping.
- Services contain business/auth orchestration.
- Repositories wrap reusable data access logic.
- API controllers are thin and return JSON resources.

## 2. Folder Layout
- `app/Livewire/Auth`: web auth components (`Login`, `Register`, etc.).
- `app/Data/Auth`: DTOs for auth input (`LoginData`, `RegisterData`).
- `app/Services/Auth`: auth use-case services (`LoginUserService`, `RegisterUserService`, `ApiTokenService`).
- `app/Repositories`: base repository and concrete repositories (`UserRepository`).
- `app/Http/Controllers/Api/V1/Auth`: API auth endpoints.
- `app/Http/Requests/Api/V1/Auth`: API validation request classes for docs + validation metadata.
- `app/Http/Resources/Api/V1`: API response resources (`UserResource`).
- `routes/web`: modular web route files loaded via `RouteLoader`.
- `routes/api/v1`: modular API route files loaded via `RouteLoader`.

## 3. Auth Flow (Web)
- Register:
  - `Register` component validates using `RegisterData::rules()`.
  - Calls `RegisterUserService`, which persists through `UserRepository`.
  - Logs in via session and redirects.
- Login:
  - `Login` component validates using `LoginData::rules()`.
  - Calls `LoginUserService` for rate-limit + credential checks.
  - Handles 2FA redirect or session login.

## 4. Auth Flow (API Token)
- `POST /api/v1/register`: creates user and returns bearer token.
- `POST /api/v1/login`: authenticates and returns bearer token.
- `GET /api/v1/me`: returns current authenticated user (`auth:api`).
- `POST /api/v1/logout`: revokes current user token.

## 5. Routing Strategy
- `bootstrap/app.php` loads both `web` and `api` route files.
- `routes/web.php` and `routes/api.php` group routes and delegate to route shards via `RouteLoader`.
- Auth API uses `auth:api` guard (token driver).

## 6. Validation Strategy
- Web Livewire forms: DTO static rules/messages (`RegisterData`, `LoginData`).
- API endpoints: FormRequests (`RegisterApiRequest`, `LoginApiRequest`) with:
  - `rules()`
  - `queryParameters()`
  - `bodyParameters()`

## 7. Documentation Strategy
- Scribe is installed and configured in `config/scribe.php`.
- API controllers contain `@group`, per-action `@response`, and `@note` blocks.
- Generated docs:
  - Blade docs: `resources/views/scribe/`
  - Assets: `public/vendor/scribe/`
  - OpenAPI/Postman: `storage/app/private/scribe/`

## 8. Repository Base Contract (Practical)
- `BaseRepository` provides common methods:
  - `query()`, `all()`, `find()`, `findOrFail()`, `create()`, `update()`, `delete()`.
- Concrete repositories extend it and add model-specific queries (example: `UserRepository::findByEmail()`).
