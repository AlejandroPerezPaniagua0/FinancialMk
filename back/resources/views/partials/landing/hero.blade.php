{{-- Hero: positioning + CTAs + product mock. --}}
<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="badge">
                <span class="badge-dot" aria-hidden="true"></span>
                Open source · Self-hosted · MIT licensed
            </span>

            <h1 class="hero-title">
                Market analysis<br>
                <em>without the paywall.</em>
            </h1>

            <p class="hero-sub">
                FinancialMk is a free, self-hosted alternative to Bloomberg,
                Morningstar and Simply Wall St. Compare assets, study volatility
                and drawdowns, own your data. No subscription. No tracking. No ads.
            </p>

            <div class="btn-row">
                <a class="btn btn-primary" href="/demo">
                    Try the demo
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 5l7 7-7 7"/>
                    </svg>
                </a>
                <a class="btn btn-ghost" href="https://github.com/AlejandroPerezPaniagua0/FinancialMk" rel="noopener" target="_blank">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                        <path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.58.1.79-.25.79-.56 0-.28-.01-1-.02-1.97-3.2.7-3.87-1.54-3.87-1.54-.52-1.32-1.27-1.67-1.27-1.67-1.04-.71.08-.7.08-.7 1.15.08 1.76 1.18 1.76 1.18 1.02 1.76 2.69 1.25 3.35.96.1-.74.4-1.25.72-1.54-2.55-.29-5.24-1.28-5.24-5.7 0-1.26.45-2.29 1.18-3.1-.12-.29-.51-1.46.11-3.05 0 0 .96-.31 3.15 1.18a10.95 10.95 0 0 1 5.74 0c2.19-1.49 3.15-1.18 3.15-1.18.62 1.59.23 2.76.11 3.05.74.81 1.18 1.84 1.18 3.1 0 4.43-2.69 5.41-5.26 5.69.41.36.78 1.06.78 2.13 0 1.54-.01 2.78-.01 3.16 0 .31.21.67.79.56C20.21 21.39 23.5 17.08 23.5 12 23.5 5.65 18.35.5 12 .5z"/>
                    </svg>
                    Star on GitHub
                </a>
            </div>

            <div class="hero-meta">
                <span class="hero-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Zero-config quickstart
                </span>
                <span class="hero-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Multi-provider, no lock-in
                </span>
                <span class="hero-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Export anything (CSV/JSON)
                </span>
            </div>
        </div>

        {{-- Inline SVG mock — purely visual, no live data, no JS dependency. --}}
        <div class="hero-card" role="img" aria-label="Sample instrument card showing AAPL price chart and key statistics">
            <div class="mock-head">
                <div>
                    <div class="mock-ticker">AAPL</div>
                    <div class="mock-name">Apple Inc. · NASDAQ</div>
                </div>
                <div style="text-align: right;">
                    <div class="mock-price">$192.45</div>
                    <div class="mock-change">▲ 1.82%</div>
                </div>
            </div>

            <svg class="mock-chart" viewBox="0 0 400 120" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="fmkArea" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%"  stop-color="#22d3ee" stop-opacity="0.35"/>
                        <stop offset="100%" stop-color="#22d3ee" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="M0,90 L20,82 L40,88 L60,70 L80,78 L100,60 L120,68 L140,52 L160,58 L180,42 L200,50 L220,38 L240,46 L260,30 L280,40 L300,28 L320,34 L340,20 L360,28 L380,16 L400,22 L400,120 L0,120 Z"
                      fill="url(#fmkArea)"/>
                <path d="M0,90 L20,82 L40,88 L60,70 L80,78 L100,60 L120,68 L140,52 L160,58 L180,42 L200,50 L220,38 L240,46 L260,30 L280,40 L300,28 L320,34 L340,20 L360,28 L380,16 L400,22"
                      fill="none" stroke="#22d3ee" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
            </svg>

            <div class="mock-stats">
                <div class="mock-stat">
                    <div class="mock-stat-label">Vol 30d</div>
                    <div class="mock-stat-value">18.4%</div>
                </div>
                <div class="mock-stat">
                    <div class="mock-stat-label">Max DD 1y</div>
                    <div class="mock-stat-value">-12.7%</div>
                </div>
                <div class="mock-stat">
                    <div class="mock-stat-label">Corr SPY</div>
                    <div class="mock-stat-value">0.81</div>
                </div>
            </div>
        </div>
    </div>
</section>
