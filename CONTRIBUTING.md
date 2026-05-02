# Contributing to FinancialMk

Thanks for thinking about contributing. This project exists because software
should be a commons, not a paywalled luxury — your time matters more than
you think.

## Ground rules

Before you send a PR, skim these:

- **Read the [Manifesto](./MANIFESTO.md).** It tells you what this project
  will and won't ever do. PRs that push towards paid plans, telemetry,
  ads, vendor lock-in, or closed-source modules will be closed — that's
  not negotiable.
- **Be kind.** We follow the [Contributor Covenant](./CODE_OF_CONDUCT.md).
- **One change per PR.** Easier to review, easier to revert.
- **Respect the architecture.** Services, Repositories, DTOs,
  Interfaces — the patterns are there for a reason. See "Architecture"
  below.

## What we want help with

High-signal contributions, roughly in priority order:

1. **New market providers.** Implement `MarketProviderInterface` for your
   favourite legal data source (Alpha Vantage, IEX Cloud, FRED, etc.).
2. **Bug reports and fixes.** Especially around the polling throttle,
   insight calculations, and export formats.
3. **i18n translations.** We ship ES/EN — any other language is welcome.
   Just drop a `lang/<locale>/*.php` on the back and a
   `front/src/i18n/locales/<locale>/*.json` on the front.
4. **Accessibility improvements.** Keyboard nav, ARIA, color contrast.
5. **Documentation.** Deployment guides for Render, Railway, Hetzner,
   Coolify, etc.
6. **Performance.** The app should stay light enough to self-host on a $5
   VPS. PRs that reduce memory or query count are gold.

## What we don't want

- **Paid tiers, Stripe, Cashier, subscriptions.** Closed on sight.
- **Third-party analytics/telemetry.** Closed on sight.
- **AI "investment recommendation" features.** We ship deterministic math,
  not LLM guesses.
- **Microservices, Kubernetes, serverless refactors.** We are a self-hosted
  monolith and we like it that way.
- **Sweeping style refactors.** If you want to change the whole codebase's
  conventions, open an issue first.

## Local setup

```bash
git clone https://github.com/<your-fork>/FinancialMk
cd FinancialMk

# Option A: full stack in Docker (recommended)
cp .env.docker.example .env.docker
docker compose up

# Option B: bare metal
cd back
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve &

cd ../front
npm install
npm run dev
```

Back on `http://localhost:8000`, front on `http://localhost:5173`.

## Development flow

1. Fork the repo.
2. Create a branch from `main`: `git checkout -b feat/my-feature` or
   `fix/my-bug`.
3. Make your change. Add tests (see "Testing" below).
4. Run the linters and tests locally:
   ```bash
   # back
   cd back
   ./vendor/bin/pint --test   # style check
   php artisan test            # phpunit

   # front
   cd ../front
   npm run lint
   npm run build               # also runs tsc
   ```
5. Open a PR against `main`. Use the PR template. Reference any issue it
   closes with `Closes #123`.
6. A maintainer reviews. Respond to comments with pushes to the same
   branch — no force-push if avoidable.

## Commit style

We like [Conventional Commits](https://www.conventionalcommits.org):

- `feat: add Stooq api client`
- `fix: correct volatility computation for sparse history`
- `docs: update README comparison table`
- `refactor: extract InsightService correlation helper`
- `test: cover watchlist deletion edge cases`
- `chore: bump Laravel to 12.x`

One commit per logical change. Squash noisy fix-up commits before merging.

## Architecture (read before writing code)

Back-end (`/back`):

- **Controllers** are thin. They validate, call a service, return a
  Resource. No business logic.
- **Services** (`app/Services`) hold business logic. They depend on
  interfaces (`app/Services/Interfaces`), not concrete classes. Bind in
  `AppServiceProvider`.
- **Repositories** (`app/Repositories`) are the only place allowed to talk
  to Eloquent. Services never call `Model::where(...)` directly.
- **API clients** (`app/ApiClient`) wrap external HTTP providers. They
  implement `MarketProviderInterface` when they provide market data so the
  factory can swap them.
- **DTOs** (`app/DTOs`) carry typed data between layers. Prefer them over
  arrays for anything non-trivial.

Front-end (`/front`):

- **Pages** (`src/pages`) are routed top-level components.
- **Components** (`src/components`) are reusable, preferably stateless.
- **Contexts** (`src/contexts`) hold cross-cutting concerns: auth, theme,
  comparison selection, locale.
- **Hooks** (`src/hooks`) wrap TanStack Query calls and polling loops.
- **API calls** live in `src/api/*.ts` — never inline inside components.

## Testing

- Back: PHPUnit 11. New services should have a `tests/Unit` test. New
  controllers a `tests/Feature`. Mock providers with the interface — never
  hit the real upstream in tests.
- Front: we're adding Vitest. For now, `npm run build` catching TS errors
  is the baseline.

## Asking questions

Open a Discussion, or an Issue with the "question" template. There are no
bad questions — if something is unclear, the docs are probably wrong.

---

Thanks for reading all the way down. You're the reason this works.
