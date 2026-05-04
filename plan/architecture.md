# SmartShop Mini Architecture

## Overview
SmartShop Mini will use a repository-service-controller structure to keep business logic testable and framework wiring simple.

- Controllers: receive requests, call form requests for validation, delegate to services, return views/responses.
- Services: contain use-case orchestration and domain rules.
- Repositories: encapsulate Eloquent queries and persistence details.
- DTO-like arrays/value objects: normalize data exchanged with AI adapters and view models.

## Recommendation Service

### Goals
- Support multiple AI providers (OpenAI, Gemini, Claude) behind one contract.
- Return a consistent recommendation payload regardless of provider.
- Fail safely with deterministic fallback recommendations.

### Suggested Components
- `App\Services\Recommendation\RecommendationServiceInterface`
- `App\Services\Recommendation\RecommendationService`
- `App\Services\Recommendation\PromptBuilder`
- `App\Services\Recommendation\ResponseParser`
- `App\Services\Recommendation\Providers\OpenAiRecommendationProvider`
- `App\Services\Recommendation\Providers\GeminiRecommendationProvider`
- `App\Services\Recommendation\Providers\ClaudeRecommendationProvider`

### Flow
1. Product detail controller calls recommendation service with product + optional cart context.
2. Service checks cache key for existing recommendation payload.
3. Service builds prompt via `PromptBuilder`.
4. Selected provider adapter is resolved using config (`services.ai.provider`).
5. Raw response is parsed/validated by `ResponseParser`.
6. On parse/provider failure, fallback recommendations are generated from repository query rules.
7. Normalized payload is returned and optionally cached.

### Payload Contract
- `items`: array of recommended products with `slug`, `name`, `price`, `reason`
- `source`: `ai` or `fallback`
- `provider`: provider name when AI is used
- `generated_at`: ISO timestamp

## Cart Service

### Goals
- Keep cart session-based for MVP.
- Enforce stock and active product rules on every mutation.
- Provide stable totals and line-item summaries for cart and checkout.

### Suggested Components
- `App\Services\Cart\CartServiceInterface`
- `App\Services\Cart\CartService`
- `App\Repositories\Product\ProductRepository`
- `App\Data\Cart\CartLineData` (or equivalent typed array shape in PHPDoc)

### Session Structure
- `cart.lines`: keyed by product id
- each line: `product_id`, `name`, `slug`, `unit_price`, `quantity`, `subtotal`
- `cart.meta`: `items_count`, `subtotal`, `updated_at`

### Rules
- Add/update requires product exists and `is_active = true`.
- Quantity must be integer and between `1` and available stock.
- Totals are recalculated server-side on each mutation.
- Stale lines are removed or flagged when product becomes unavailable.

## AI Prompt Design

### Objectives
- Keep prompts short, deterministic, and parse-friendly.
- Minimize hallucinated fields by constraining allowed output schema.
- Include only necessary product context to control token and latency costs.

### Prompt Inputs
- Current product: name, category, price, short description, stock status.
- Candidate pool summary (optional): top related products from repository.
- Business constraints: no inactive products, no out-of-stock products unless explicitly allowed.

### Output Requirements
- Require JSON-only output with fixed keys.
- Limit recommendation count (for example, 4 items).
- Each item must include `slug` and concise `reason`.
- Reject/normalize any extra or malformed fields.

### Safety and Fallback
- Timeout + retry policy with bounded attempts.
- Parser validation before use in UI.
- Fallback strategy: same category, similar price band, active/in-stock first.
- Log metadata only (provider, latency, fallback used), never API keys or raw sensitive headers.

## Controller-Service-Repository Boundaries

### Controllers
- Handle request/response only.
- Use form request classes for validation.
- Call one service method per action whenever possible.

### Services
- Coordinate repositories and domain rules.
- Throw domain exceptions for invalid states (stock conflict, empty checkout, stale cart).
- Return response-ready data structures.

### Repositories
- Own query concerns (filters, eager loading, sorting, pagination).
- Keep Eloquent details out of controllers/services where possible.
- Return models/collections needed by services.
