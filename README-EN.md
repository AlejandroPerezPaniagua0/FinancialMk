<div align="center">

# FinancialMk

**The open-source alternative to Bloomberg Terminal, Morningstar and Simply Wall St.**
Multi-market analysis, self-hosted, no subscriptions, no telemetry, no paywalls.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![React 19](https://img.shields.io/badge/React-19-blue.svg)](https://react.dev)
[![Self-hosted](https://img.shields.io/badge/self--hosted-%E2%9C%94-success)](#quickstart)
[![No tracking](https://img.shields.io/badge/no%20tracking-%E2%9C%94-success)](./MANIFESTO.md)

[Manifesto](./MANIFESTO.md) · [Español](./README.md) · [Contribute](./CONTRIBUTING.md) · [Security](./SECURITY.md)

</div>

---

## Why this repo exists

Bloomberg Terminal costs **$24,000/year**. Simply Wall St charges **$300/year**.
Morningstar Premium charges **$250/year**. All of them do the same math on
the same public data.

FinancialMk does 90% of what they do. It installs on your server with one
command. It costs zero. It's yours.

> "David didn't win by luck. He won by picking up the tool that was already
> lying on the ground." — Read the [Manifesto](./MANIFESTO.md).

---

## Quickstart (3 commands)

```bash
git clone https://github.com/AlejandroPerezPaniagua0/FinancialMk
cd FinancialMk
docker compose up
```

Open `http://localhost:8080`. That's it. The stack ships seeded with ~150
popular instruments and two years of history, so there's data to look at
even without configuring any API key.

For bare-metal installs or alternative databases, see
[`back/README.md`](./back/README.md) and [`front/README.md`](./front/README.md).

---

## What it does

- **Multi-market analysis.** Stocks, ETFs, cryptocurrencies, forex, funds.
- **OHLCV history.** Open, high, low, adjusted close, volume — two years
  deep by default (more if you wire up a real provider).
- **Normalized comparison.** Rebase 2-4 assets to base 100 to compare
  percentage evolution regardless of absolute price. Correlation matrix
  included.
- **Automatic insight card.** When you open any instrument, FinancialMk
  computes 30d annualized volatility, 1y max drawdown and correlation with
  a configurable benchmark (SPY by default). All computed locally from the
  history — no external calls.
- **Watchlists.** Follow 20 assets without having to throw them in the
  comparator.
- **Throttled real-time polling.** Quote refresh every 30s with server-side
  cache so we don't hammer your data provider.
- **Multi-provider.** Ships with clients for [TwelveData](https://twelvedata.com)
  (freemium) and [Stooq](https://stooq.com) (unlimited free CSV, legal, EoD).
  Switch provider via an env var.
- **Universal export.** Any view with data exports to CSV or JSON with one
  click. Your data is yours.
- **Price alerts.** When a ticker crosses a threshold, you get an email.
- **Demo mode.** `DEMO_MODE=true` opens the app with a pre-built watchlist
  without asking for registration. Great for showing the project around.
- **Public API documented with OpenAPI.** Build on top of FinancialMk with
  Personal Access Tokens, in any language.
- **Dark mode**, **i18n ES/EN**, **basic a11y**.

---

## Honest comparison

| | Bloomberg Terminal | Simply Wall St | Morningstar Premium | Yahoo Finance | **FinancialMk** |
|---|:---:|:---:|:---:|:---:|:---:|
| **Price/year** | $24,000 | $300 | $250 | $0 (with ads) | **$0** |
| **Open source** | ❌ | ❌ | ❌ | ❌ | ✅ MIT |
| **Self-hosted** | ❌ | ❌ | ❌ | ❌ | ✅ |
| **No telemetry** | ❌ | ❌ | ❌ | ❌ | ✅ |
| **No ads** | ✅ | ✅ | ✅ | ❌ | ✅ |
| **Free export** | ❌ | Limited | Limited | Limited | ✅ CSV/JSON |
| **Open API** | ❌ | ❌ | ❌ | Deprecated | ✅ OpenAPI |
| **Your data is yours** | ❌ | ❌ | ❌ | ❌ | ✅ |
| **OHLCV history** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Real-time quotes** | ✅ tick | ✅ delayed | ✅ delayed | ✅ delayed | ✅ delayed |
| **Comparator** | ✅ | ✅ | Limited | Limited | ✅ |
| **Watchlists** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Alerts** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Multi-provider** | ❌ | ❌ | ❌ | ❌ | ✅ |

*Prices publicly verifiable; they may change. FinancialMk will never charge
— that's the whole point.*

---

## How we get the data

FinancialMk **does not scrape** private sources and does not violate ToS.
We talk to two legal providers:

- **[TwelveData](https://twelvedata.com)** — Freemium. Free tier is 8 req/min.
  Needs `TWELVE_DATA_API_KEY` in `.env`. Covers stocks, ETFs, crypto, forex,
  funds.
- **[Stooq](https://stooq.com)** — Free, no API key, public CSV. EoD
  (end-of-day) data, global coverage. Enough for 95% of historical analysis.

Switch provider with one variable:

```env
MARKET_PROVIDER=stooq   # or twelve_data
```

The instrument list and two years of history ship **seeded** in the repo
as versioned JSON fixtures. No API call is needed on first boot — you open
the app and there's already something to look at.

---

## Architecture (for devs)

Laravel monolith + React SPA served separately. No microservices, no
Kubernetes, no magic.

```
FinancialMk/
├── back/              # Laravel 12 API (PHP 8.2+)
│   ├── app/
│   │   ├── ApiClient/       # TwelveDataApiClient, StooqApiClient
│   │   ├── DTOs/            # Request/response DTOs
│   │   ├── Http/Controllers # Thin controllers
│   │   ├── Models/          # Eloquent models
│   │   ├── Repositories/    # Data access abstraction
│   │   └── Services/        # Business logic
│   ├── config/market.php    # Provider + polling config
│   └── routes/api.php       # Sanctum-authed REST API
├── front/             # React 19 + TS + Vite + Tailwind 4
│   └── src/
│       ├── api/            # HTTP clients
│       ├── components/     # Reusable UI
│       ├── contexts/       # Auth, Theme, Comparison, Locale
│       ├── hooks/          # useQuotesPolling, useWatchlists, ...
│       └── pages/          # Routed pages
└── docker-compose.yml # back + front + postgres + redis
```

Patterns (all standard Laravel): **Service Layer**, **Repository Pattern**,
**DTO Pattern**, **API Resources**, interfaces bound in `AppServiceProvider`
for tests and provider swap.

Stack:
- **Backend:** Laravel 12, PHP 8.2+, Sanctum, PHPUnit 11, Pint.
- **Frontend:** React 19, TypeScript 5.9, Vite 7, Tailwind 4, React Router 7,
  TanStack Query 5, Recharts 3, `react-i18next`.
- **Infra:** PostgreSQL 16 + Redis 7 via Docker Compose. SQLite supported
  as a bare-metal alternative.

---

## Roadmap

- **v0.2** — Full OSS positioning: docs, Docker, landing. (in progress)
- **v0.5** — Watchlists, Stooq multi-provider, insight cards, demo mode,
  export, seeds. (in progress)
- **v1.0** — Price alerts, public OpenAPI, i18n ES/EN, guided onboarding,
  `/why` page.
- **v1.x** — Whatever contributors propose in
  [Issues](https://github.com/AlejandroPerezPaniagua0/FinancialMk/issues).

---

## Contributing

All help welcome. Read [CONTRIBUTING.md](./CONTRIBUTING.md) for the flow;
[CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md) for community rules.

- **Found a bug?** Open an issue using the template.
- **Feature idea?** Open an issue using the feature template.
- **Quick fix?** Send a PR directly.
- **Vulnerability?** See [SECURITY.md](./SECURITY.md). Do not open publicly.

---

## Sustainability

FinancialMk has no business model — it's a civil infrastructure project.
If you use it and it saves you a subscription, consider supporting with a
donation via [GitHub Sponsors](https://github.com/sponsors/AlejandroPerezPaniagua0).
If you can't, use the software anyway — that's what we want.

---

## License

[MIT](./LICENSE). Do whatever you want with the code, including
commercializing your own version. Just keep attribution.

---

<div align="center">

**Open source · Self-hosted · No tracking · Yours forever.**

Built by [Alejandro Pérez Paniagua](https://github.com/AlejandroPerezPaniagua0) and FinancialMk contributors.

</div>
