# SmartShop Mini

SmartShop Mini is a minimal Laravel e-commerce demo with:

- Authentication (register, login, logout, password reset, email verification)
- Product listing and product detail pages
- Session cart + authenticated cart persistence
- Checkout simulation
- AI-powered recommendations with fallback strategy
- TailwindCSS + Alpine-friendly Blade UI

## Stack

- PHP 8.4
- Laravel 12
- Livewire 4
- TailwindCSS 4
- Pest 4

## Setup

1. Install dependencies:

```bash
composer install
npm install
```

2. Prepare environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env` (SQLite or MySQL).

4. Run migrations and seed demo data:

```bash
php artisan migrate --seed --no-interaction
```

5. Build frontend assets:

```bash
npm run build
```

6. Start app:

```bash
composer run dev
```

## Demo Credentials

- Email: `user@example.com`
- Password: `password`

## AI Configuration

Configure in `.env` and `config/services.php`:

- `AI_PROVIDER` (`openai`, `gemini`, `claude`)
- `OPENAI_API_KEY`, `OPENAI_MODEL`, `OPENAI_TIMEOUT`, `OPENAI_RETRIES`
- `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_TIMEOUT`, `GEMINI_RETRIES`
- `CLAUDE_API_KEY`, `CLAUDE_MODEL`, `CLAUDE_TIMEOUT`, `CLAUDE_RETRIES`

Provider selection behavior:

- `RecommendationService` resolves provider by `services.ai.provider`
- Provider output is normalized by a parser
- Invalid/malformed provider output falls back to deterministic recommendations from product repository queries

## Recommendation Flow

1. Product detail requests recommendations from `RecommendationService`.
2. Service builds strict prompt + calls selected provider adapter.
3. Response parser validates normalized schema.
4. If provider fails, times out, or returns invalid data, fallback recommendations are returned.
5. UI always renders recommendation section and shows `Source: ai|fallback`.

Known limitations:

- External provider quality depends on API latency and model behavior.
- Fallback is deterministic and category/price-based, not semantic.
- Recommendations are read-only suggestions and do not alter catalog ranking.

## Architecture (Repository-Service-Controller)

- Controllers: request/response only; use Form Requests and delegate logic.
- Services: orchestration and business rules (cart, checkout, recommendations, auth).
- Repositories: reusable query and persistence logic.
- Data objects: DTO-like structures for validated payload mapping.

## Bonus Features

- Authenticated cart persistence:
  - Session cart is persisted to `cart_items`
  - On login, session + persisted cart are merged with stock-aware clamping
- Wishlist/favorites:
  - Auth users can add/remove products from wishlist
  - Wishlist listing page: `/wishlist`
- Recommendation rationale label:
  - UI displays `Why this is suggested: ...`
- Demo reset command:

```bash
php artisan smartshop:demo-refresh
php artisan smartshop:demo-refresh --fresh
```

- Basic analytics events stored in `store_analytics_events`:
  - `product_view`
  - `add_to_cart`
  - `checkout_simulation`

## Quality Commands

- Format:

```bash
composer format
```

- Static analysis:

```bash
composer analyse
```

- Full lint pipeline:

```bash
composer lint
```

- Tests:

```bash
php artisan test --compact
```

Expected outcomes:

- `composer analyse` passes at max PHPStan level
- `composer lint` passes (PHPStan + Blade formatter + Pint)
- Test suite passes

## Troubleshooting

### Vite manifest not found

If you see `Vite manifest not found`:

```bash
npm run build
```

Or use dev watcher:

```bash
npm run dev
```

### Composer platform mismatch (PHP version)

If `composer install` fails with PHP platform errors, ensure CLI PHP matches project requirement (`php ^8.4`). Check:

```bash
php -v
```

### Missing `ext-sockets`

If Pest browser plugin complains about sockets extension, enable it in your active `php.ini` for CLI.

### AI recommendation errors

- Verify API key for selected provider
- Verify provider/model values in `.env`
- If provider fails, fallback recommendations are expected behavior
