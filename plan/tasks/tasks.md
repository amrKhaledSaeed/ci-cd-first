# SmartShop Mini Implementation Tasks

## 1. Project Setup
- [x] Confirm project baseline (Laravel 12, PHP 8.4, Livewire stack) and lock assumptions in planning notes
- [x] Configure `.env` for local database, cache, queue, session, mail, and app URL
- [x] Add AI provider configuration keys in `config/services.php` and reference only via config
- [x] Create route groups and naming strategy for `products.*`, `cart.*`, and `checkout.*`
- [x] Add base layout updates for store navigation, auth actions, and cart badge placeholder
- [x] Define repository-service-controller folder conventions under existing `app/` structure

## 2. Authentication
- [x] Reuse existing auth flow (register, login, logout, reset password, verification) as SmartShop auth baseline
- [x] Add route middleware rules for checkout pages (auth required) while keeping product browsing public
- [x] Add guest redirect handling when trying to access checkout routes
- [x] Add feature tests for auth-protected checkout access
- [x] Keep single role type as `customer` in registration/seeding flow
- [x] Seed one fixed test user: `user@example.com / password`

## 3. Database & Models
- [x] Create `products` migration with `slug`, `name`, `description`, `price`, `stock`, `image`, `category`, `is_active`
- [x] Add indexes for `slug`, `category`, and active listing queries
- [x] Create `Product` model with explicit casts and route model binding by slug
- [x] Create concrete repository classes for product reads and cart-related product checks
- [x] Create service classes for product listing/detail, cart orchestration, checkout simulation, and recommendations
- [x] Create controllers for product pages, cart actions, and checkout flow using form requests for validation
- [x] Define DTO-like payload structures for recommendation input/output data

## 4. Product Seeding
- [x] Create `ProductFactory` with realistic categories, varied prices, and stock levels
- [x] Create `ProductSeeder` to generate 20-30 active products
- [x] Add deterministic placeholder image paths for seeded products
- [x] Register `ProductSeeder` in `DatabaseSeeder`
- [x] Verify `php artisan migrate:fresh --seed --no-interaction` produces a usable catalog

## 5. Home Page
- [x] Build product listing page with pagination and responsive card layout
- [x] Implement search and category filters with query string persistence
- [x] Show price, stock status, and detail link for each product card
- [x] Add empty-state UI for no matching products
- [x] Implement sorting options (default relevance, price asc/desc, newest)
- [x] Ensure listing query only returns active products
- [x] Replace default landing page with SmartShop home page (hero + product grid)
- [x] Add hero/tagline section as requested in assignment brief
- [x] Add home "Recommended for you" block powered by AI or fallback

## 6. Product Detail Page
- [x] Add product detail route using slug binding
- [x] Build detail UI with image, name, category, price, stock, and description
- [x] Implement quantity selector with stock-aware limits
- [x] Add add-to-cart form request validation and feedback messages
- [x] Render recommendation section with fallback content when AI is unavailable
- [x] Keep cart as session-first source of truth (no checkout line DB dependency)

## 7. Cart System
- [x] Implement session-based `CartService` with add, update, remove, clear, and summary methods
- [x] Define cart line schema (product id, name snapshot, unit price, qty, subtotal)
- [x] Revalidate product existence, active state, and stock before cart mutations
- [x] Build cart controller actions and Blade page for line-item updates
- [x] Keep navbar cart count synced after all cart operations
- [x] Handle stale cart items when products become inactive or out of stock
- [x] Add feature/unit tests for happy path and edge cases
- [x] Align quantity controls with Alpine.js requirement (or document intentional non-Alpine fallback)

## 8. Checkout Simulation
- [x] Add checkout routes and controller actions
- [x] Create checkout form request for customer/shipping/payment-simulation fields
- [x] Validate cart integrity before processing checkout
- [x] Implement simulation result payload (confirmation code, timestamp, totals, line snapshot)
- [x] Clear cart on successful checkout simulation
- [x] Build confirmation page and failure handling for empty/invalid carts
- [x] Add tests for checkout success and failure paths

## 9. AI Recommendation System
- [x] Define `RecommendationService` contract and provider-agnostic implementation
- [x] Build provider adapters for OpenAI, Gemini, and Claude selected by config
- [x] Implement prompt builder with product context, constraints, and strict output instructions
- [x] Add robust response parser/normalizer and safe fallback strategy
- [x] Add timeout/retry handling and provider error logging without sensitive data
- [x] Add short-term caching for recommendations by product/context key
- [x] Add tests with mocked provider clients for success, malformed output, and fallback scenarios
- [x] Document recommendation architecture and prompt strategy in `plan/architecture.md`
- [x] Track last 3 viewed products in session for personalization context
- [x] Build recommendation prompt from last viewed products + candidate pool on home page
- [x] Show exactly 3 recommended products from AI response
- [x] Fallback to 3 random active products when AI fails

## 10. Frontend (Tailwind + Alpine)
- [x] Build reusable Blade components (product card, cart row, status badge, alert, recommendation list)
- [x] Add Alpine interactions for quantity controls and temporary notification banners
- [x] Ensure responsive UI behavior across listing, detail, cart, and checkout pages
- [x] Add loading and disabled states for async/submission actions
- [x] Keep styling consistent with existing app patterns and dark mode support where present
- [x] Implement Alpine-based search interaction on home page

## 11. Error Handling & Fallback Logic
- [x] Add centralized domain exceptions for cart and checkout validation issues
- [x] Convert domain exceptions to user-friendly flash/callout messages
- [x] Add not-found and inactive-product handling for product detail route
- [x] Add deterministic recommendation fallback when AI provider fails or returns invalid payload
- [x] Log operational context (provider, latency, fallback used) while excluding secrets

## 12. Code Quality Tools
- [x] Ensure Pint formatting command and standards are applied to changed files
- [x] Configure and run PHPStan at max level with Laravel extension
- [x] Configure and run Blade Formatter checks for Blade templates
- [x] Add or align composer scripts for `format`, `analyse`, and `lint` workflows
- [x] Verify local quality command sequence is CI-friendly
- [x] Verify `composer test` passes end-to-end in target environment
- [x] Confirm no N+1 queries on listing/detail/cart/checkout paths
- [x] Confirm no raw inline DB queries in app flow (prefer repositories/Eloquent relations)

## 13. Testing
- [x] Add feature tests for product listing, filtering, sorting, and product detail rendering
- [x] Add cart tests for add/update/remove/clear and stock validation failures
- [x] Add checkout tests for unauthorized access, empty cart rejection, and success flow
- [x] Add recommendation tests with provider mocks and fallback assertions
- [x] Add integration tests for repository-service-controller interaction on key flows
- [x] Run minimal targeted test files during development and full suite before final delivery

## 14. README & Documentation
- [x] Update README with setup, migration/seed, asset build, and run instructions
- [x] Document env variables for AI providers and provider selection behavior
- [x] Document recommendation flow, fallback behavior, and known limitations
- [x] Document repository-service-controller architecture overview
- [x] Document quality commands and expected outputs
- [x] Add troubleshooting notes for common setup and AI integration issues
- [x] Include AI provider choice and prompt example in README

## 15. Bonus Features
- [x] Persist cart for authenticated users with DB-backed merge behavior on login
- [x] Add wishlist/favorites feature
- [x] Add recommendation rationale text ("Why this is suggested")
- [x] Add lightweight admin seed refresh command for fast demo resets
- [x] Add basic analytics events for view, add-to-cart, and checkout simulation
