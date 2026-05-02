# The FinancialMk Manifesto

> Bloomberg Terminal costs **$24,000 per year**.
> Financial analysis should not be a privilege.
> So we built this. And we gave it away.

---

## What we believe

**1. Market data is a civil infrastructure, not a luxury good.**
Charts, historical prices, correlations, volatility, drawdowns — this is
math on public data. It should cost zero dollars to look at the market you
already live in.

**2. Your tools should belong to you.**
A tool you self-host cannot be shut down, rug-pulled, or rebranded into
something worse. Your portfolio of curiosity deserves the same permanence
as your portfolio of assets.

**3. Transparency beats features.**
We will always tell you where the data comes from, how we compute insights,
and what we do with your traffic (nothing — we don't see it). An app that
hides its math is an app that can lie to you.

**4. Small software can out-punch expensive software.**
One well-written Laravel monolith + one well-written React SPA can cover
the 90% of use cases that the Bloomberg Terminal uses to justify its
$24k/year price tag. The other 10% is theatre.

---

## What we will never do

- **No paid plans.** No "Premium", no "Pro", no "Enterprise". Every feature
  ships to every user. If a company wants more, they can fork the repo.
- **No ads.** Not banner ads, not sponsored tickers, not "recommended by"
  tiles. Not now, not later.
- **No third-party telemetry.** No Google Analytics. No Mixpanel. No
  Segment. No Posthog Cloud. No Sentry SaaS. Nothing phoning home.
- **No data resale.** We do not have your data — you self-host. There is
  nothing to sell, and we pledge to never build a hosted version whose
  business model requires selling it.
- **No vendor lock-in.** Every piece of data you put in comes out again,
  in CSV or JSON, with one click. It was always yours.
- **No AI-generated investment advice.** We will not wrap an LLM around
  charts and pretend it's analysis. The math we ship is deterministic,
  auditable, and printed in the source code.
- **No closed-source "Enterprise Edition".** The whole repo is MIT. What
  you see is what there is.

---

## Who this is for

- **Independent investors** who refuse to pay $24k to see a candle chart.
- **Students** learning quantitative finance who need real data to practice.
- **Developers** who want to build on top of an open financial API without
  asking permission from a gatekeeper.
- **Anyone** who has ever been told "that feature is in the Enterprise plan".

---

## How we sustain this

Donations via [GitHub Sponsors](https://github.com/sponsors/AlejandroPerezPaniagua0).
That's it. If donations dry up, the code still works — the repo is MIT,
the Docker stack runs offline, the seed data ships with the install.

We are not a startup. We are not venture-funded. We are not looking for
a buyer. We are a tool.

---

## The ask

If you use this and it saves you a subscription, consider:

1. Starring the repo.
2. Telling one other independent investor about it.
3. Filing an issue when you find a bug.
4. Sending a PR when you fix one.
5. Sponsoring, if you can afford it.

That's the whole contract.

---

*David vs. Goliath is not a story about luck. It's a story about picking
up the tool that was already lying on the ground.*

— The FinancialMk project
