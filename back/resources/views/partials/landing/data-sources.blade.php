{{-- Transparency: where the numbers come from. --}}
<section class="s" id="data">
    <div class="container">
        <div class="s-head">
            <div class="s-eyebrow">Where the numbers come from</div>
            <h2 class="s-title">Honest about data, always.</h2>
            <p class="s-sub">
                We don't pretend to have institutional feeds. We use public providers
                you can audit. You pick which one — and you can always swap.
            </p>
        </div>

        <div class="sources">
            <div class="source">
                <h4>Stooq <span class="pill pill-free">Free</span></h4>
                <p>End-of-day OHLCV via public CSV endpoints. Legal, generous, no API key required. The default for self-hosted installs.</p>
            </div>
            <div class="source">
                <h4>TwelveData <span class="pill pill-key">Free key</span></h4>
                <p>Real-time and intraday quotes for stocks, ETFs, forex and crypto. Free tier covers most retail use; paid tiers exist if you need more.</p>
            </div>
            <div class="source">
                <h4>Bring your own provider <span class="pill pill-free">Pluggable</span></h4>
                <p>Implement <code>MarketProviderInterface</code>, set <code>MARKET_PROVIDER=your_provider</code>, restart. We document the contract and we'll review your PR.</p>
            </div>
        </div>

        <p style="text-align: center; color: var(--text-dim); font-size: 0.85rem; margin-top: 1.5rem;">
            FinancialMk caches and rate-limits provider calls so you don't get banned and don't blow your free tier.
        </p>
    </div>
</section>
