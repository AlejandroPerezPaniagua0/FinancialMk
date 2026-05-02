# Security Policy

## Supported versions

FinancialMk uses semantic versioning. Security fixes are backported to:

| Version | Supported          |
| ------- | ------------------ |
| `main`  | ✅ always          |
| latest tagged release | ✅          |
| older releases | ❌ — please upgrade |

## Reporting a vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Instead, send a private report to the maintainer:

- **Email:** alejandro.perez.paniagua1@gmail.com
- **Subject prefix:** `[SECURITY] FinancialMk`
- **GitHub Security Advisory:** [open a private advisory](https://github.com/AlejandroPerezPaniagua0/FinancialMk/security/advisories/new)
  (preferred — automatically issues a CVE if applicable).

Include:

1. A description of the vulnerability and its impact.
2. Steps to reproduce (a minimal PoC if possible).
3. The version / commit you reproduced on.
4. Any suggested fix or mitigation.

We commit to:

- Acknowledge receipt within **72 hours**.
- Provide an initial assessment within **7 days**.
- Coordinate a fix and disclosure timeline with you.
- Credit you in the security advisory unless you prefer to remain anonymous.

## Scope

In scope:

- The Laravel backend (`/back`) and its REST API.
- The React frontend (`/front`).
- The provided Docker images (`docker-compose.yml`, `back/Dockerfile`,
  `front/Dockerfile`).
- The default seed data and demo mode middleware.
- Authentication and authorization (Sanctum, demo mode, rate limiting).

Out of scope:

- Vulnerabilities in upstream data providers (TwelveData, Stooq) — please
  report those to them directly.
- Self-inflicted misconfiguration (running with `APP_DEBUG=true` exposed
  to the internet, weak `APP_KEY`, default admin credentials, etc.).
- Denial of service via excessive polling against your own deployment —
  configure `MARKET_POLLING_REQUEST_RATE` accordingly.
- Issues in third-party integrations the user adds (custom providers,
  custom mailers, custom OAuth bridges).

## What we consider vulnerabilities

- Authentication or authorization bypass (e.g. accessing another user's
  watchlist, alerts, or settings).
- Stored or reflected XSS in any user-facing component.
- SQL injection (we use Eloquent — but never assume).
- Server-side request forgery via the provider clients.
- Token leakage via logs, error messages, or response bodies.
- Insecure defaults in the shipped Docker images or `.env.example`.
- Cryptographic mistakes in token storage, password hashing, or session
  handling.

## What we consider non-issues

- Missing security headers on `welcome.blade.php` while running locally
  in `APP_ENV=local`.
- Self-XSS that requires the user to paste attacker-supplied JS into
  their own console.
- Lack of brute-force protection on `/api/auth/login` beyond Laravel's
  default throttling — open a feature request, not an advisory.
- Vulnerabilities in `vendor/` or `node_modules/` that have an upstream
  fix already published — please update first.

## Disclosure timeline

Default: **90 days** from initial report to public disclosure, or as soon
as a fix is released — whichever comes first. We can negotiate a shorter
or longer window for critical or unusually complex issues.

---

Thank you for keeping FinancialMk safe for everyone.
