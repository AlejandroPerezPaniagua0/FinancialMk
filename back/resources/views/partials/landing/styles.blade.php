{{-- Self-contained CSS for the landing page — no build step required. --}}
<style>
    :root {
        --bg:           #0b0f17;
        --bg-elev:      #111827;
        --bg-card:      #131a26;
        --border:       #1f2937;
        --border-strong:#334155;
        --text:         #e5e7eb;
        --text-muted:   #94a3b8;
        --text-dim:     #64748b;
        --accent:       #22d3ee;
        --accent-soft:  rgba(34, 211, 238, 0.1);
        --success:      #34d399;
        --danger:       #f87171;
        --warning:      #fbbf24;
        --shadow:       0 1px 3px rgba(0,0,0,0.3), 0 8px 24px rgba(0,0,0,0.2);
        --radius:       12px;
        --radius-sm:    8px;
        --max-width:    1140px;
        --gutter:       clamp(1rem, 4vw, 2rem);
    }

    [data-theme="light"] {
        --bg:           #ffffff;
        --bg-elev:      #f8fafc;
        --bg-card:      #ffffff;
        --border:       #e2e8f0;
        --border-strong:#cbd5e1;
        --text:         #0f172a;
        --text-muted:   #475569;
        --text-dim:     #64748b;
        --accent:       #0891b2;
        --accent-soft:  rgba(8, 145, 178, 0.08);
        --shadow:       0 1px 2px rgba(15,23,42,0.06), 0 8px 24px rgba(15,23,42,0.06);
    }

    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        font-size: 16px;
        line-height: 1.6;
        color: var(--text);
        background: var(--bg);
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    a { color: var(--accent); text-decoration: none; }
    a:hover { text-decoration: underline; }

    .container { max-width: var(--max-width); margin: 0 auto; padding: 0 var(--gutter); }

    /* ── Nav ────────────────────────────────────────────────────────── */
    .nav {
        position: sticky; top: 0; z-index: 50;
        backdrop-filter: blur(10px);
        background: color-mix(in srgb, var(--bg) 85%, transparent);
        border-bottom: 1px solid var(--border);
    }
    .nav-inner {
        display: flex; align-items: center; justify-content: space-between;
        height: 64px;
    }
    .nav-brand {
        display: flex; align-items: center; gap: 0.6rem;
        font-weight: 700; font-size: 1.1rem; color: var(--text);
        text-decoration: none;
    }
    .nav-brand:hover { text-decoration: none; }
    .nav-logo {
        width: 28px; height: 28px; border-radius: 8px;
        background: linear-gradient(135deg, var(--accent), #6366f1);
        display: grid; place-items: center;
        color: #0b0f17; font-weight: 800; font-size: 0.85rem;
    }
    .nav-links { display: flex; align-items: center; gap: 1.5rem; }
    .nav-links a {
        color: var(--text-muted); font-size: 0.92rem; font-weight: 500;
    }
    .nav-links a:hover { color: var(--text); text-decoration: none; }
    .nav-cta {
        background: var(--accent); color: #0b0f17;
        padding: 0.5rem 1rem; border-radius: var(--radius-sm);
        font-weight: 600; font-size: 0.9rem;
    }
    .nav-cta:hover { text-decoration: none; opacity: 0.9; }
    .icon-btn {
        background: transparent; border: 1px solid var(--border);
        color: var(--text-muted); cursor: pointer;
        width: 36px; height: 36px; border-radius: var(--radius-sm);
        display: grid; place-items: center;
        transition: border-color 0.15s, color 0.15s;
    }
    .icon-btn:hover { border-color: var(--border-strong); color: var(--text); }
    @media (max-width: 720px) {
        .nav-links a:not(.nav-cta) { display: none; }
    }

    /* ── Hero ───────────────────────────────────────────────────────── */
    .hero { padding: clamp(3rem, 8vw, 6rem) 0 clamp(2rem, 6vw, 4rem); }
    .hero-grid {
        display: grid; gap: 3rem;
        grid-template-columns: 1.1fr 0.9fr;
        align-items: center;
    }
    @media (max-width: 900px) { .hero-grid { grid-template-columns: 1fr; } }

    .badge {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--accent-soft);
        color: var(--accent);
        padding: 0.35rem 0.8rem; border-radius: 999px;
        font-size: 0.78rem; font-weight: 600;
        border: 1px solid color-mix(in srgb, var(--accent) 30%, transparent);
        margin-bottom: 1.25rem;
    }
    .badge-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 25%, transparent);
    }

    h1.hero-title {
        font-size: clamp(2rem, 5vw, 3.4rem);
        line-height: 1.08; font-weight: 800;
        letter-spacing: -0.02em;
        margin: 0 0 1.25rem;
        color: var(--text);
    }
    .hero-title em {
        font-style: normal;
        background: linear-gradient(135deg, var(--accent), #818cf8);
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
    }
    .hero-sub {
        font-size: clamp(1rem, 1.6vw, 1.15rem);
        color: var(--text-muted);
        max-width: 540px;
        margin: 0 0 2rem;
    }

    .btn-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 2rem; }
    .btn {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.75rem 1.25rem; border-radius: var(--radius-sm);
        font-weight: 600; font-size: 0.95rem;
        text-decoration: none; cursor: pointer;
        border: 1px solid transparent;
        transition: transform 0.05s, opacity 0.15s, border-color 0.15s;
    }
    .btn:hover { text-decoration: none; }
    .btn:active { transform: translateY(1px); }
    .btn-primary  { background: var(--accent); color: #0b0f17; }
    .btn-primary:hover  { opacity: 0.92; }
    .btn-ghost    { background: transparent; color: var(--text); border-color: var(--border-strong); }
    .btn-ghost:hover    { border-color: var(--accent); color: var(--accent); }

    .hero-meta { display: flex; flex-wrap: wrap; gap: 1.5rem; color: var(--text-dim); font-size: 0.88rem; }
    .hero-meta-item { display: flex; align-items: center; gap: 0.5rem; }
    .hero-meta-item svg { width: 16px; height: 16px; color: var(--success); }

    /* Hero card mock */
    .hero-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .hero-card::before {
        content: '';
        position: absolute; inset: -1px;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 40%, transparent), transparent 50%);
        -webkit-mask: linear-gradient(#000, #000) content-box, linear-gradient(#000, #000);
        -webkit-mask-composite: xor; mask-composite: exclude;
        pointer-events: none;
    }
    .mock-head {
        display: flex; justify-content: space-between; align-items: center;
        padding-bottom: 0.75rem; margin-bottom: 0.75rem;
        border-bottom: 1px solid var(--border);
    }
    .mock-ticker { font-weight: 700; color: var(--text); font-size: 1rem; }
    .mock-name   { font-size: 0.78rem; color: var(--text-dim); }
    .mock-price  { font-variant-numeric: tabular-nums; font-size: 1.4rem; font-weight: 700; }
    .mock-change { color: var(--success); font-size: 0.85rem; font-weight: 600; }
    .mock-chart  { height: 120px; display: block; width: 100%; }
    .mock-stats  { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-top: 1rem; }
    .mock-stat   {
        background: var(--bg-elev);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 0.6rem 0.75rem;
    }
    .mock-stat-label { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em; }
    .mock-stat-value { font-size: 0.95rem; font-weight: 700; color: var(--text); margin-top: 0.15rem; font-variant-numeric: tabular-nums; }

    /* ── Section base ──────────────────────────────────────────────── */
    section.s { padding: clamp(3rem, 7vw, 5rem) 0; }
    .s-head   { text-align: center; max-width: 720px; margin: 0 auto clamp(2rem, 5vw, 3rem); }
    .s-eyebrow {
        text-transform: uppercase; letter-spacing: 0.12em;
        font-size: 0.78rem; font-weight: 700;
        color: var(--accent); margin-bottom: 0.6rem;
    }
    h2.s-title {
        font-size: clamp(1.6rem, 3.6vw, 2.4rem);
        line-height: 1.15; font-weight: 800;
        letter-spacing: -0.015em; margin: 0 0 1rem;
        color: var(--text);
    }
    .s-sub { color: var(--text-muted); font-size: 1.05rem; margin: 0; }

    /* ── Comparison table ──────────────────────────────────────────── */
    .compare-wrap {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow-x: auto;
    }
    table.compare {
        width: 100%; min-width: 720px;
        border-collapse: collapse;
        font-size: 0.92rem;
    }
    .compare th, .compare td {
        padding: 0.95rem 1rem; text-align: left;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .compare thead th {
        font-weight: 600; color: var(--text-muted);
        background: var(--bg-elev);
        font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .compare th.us, .compare td.us {
        background: color-mix(in srgb, var(--accent) 8%, var(--bg-card));
        color: var(--text);
    }
    .compare th.us { color: var(--accent); }
    .compare tr:last-child td { border-bottom: none; }
    .compare .feat { font-weight: 600; color: var(--text); }
    .compare .yes  { color: var(--success); font-weight: 600; }
    .compare .no   { color: var(--danger);  font-weight: 600; }
    .compare .meh  { color: var(--warning); font-weight: 600; }

    /* ── Features grid ─────────────────────────────────────────────── */
    .features {
        display: grid; gap: 1.25rem;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }
    .feature {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        transition: border-color 0.15s, transform 0.15s;
    }
    .feature:hover { border-color: var(--border-strong); }
    .feature-ico {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--accent-soft); color: var(--accent);
        display: grid; place-items: center; margin-bottom: 1rem;
    }
    .feature-ico svg { width: 20px; height: 20px; }
    .feature h3 { margin: 0 0 0.5rem; font-size: 1.05rem; color: var(--text); }
    .feature p  { margin: 0; color: var(--text-muted); font-size: 0.92rem; line-height: 1.55; }

    /* ── Data sources ──────────────────────────────────────────────── */
    .sources {
        display: grid; gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
    .source {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
    }
    .source h4 { margin: 0 0 0.4rem; font-size: 1rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem; }
    .source p  { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
    .pill {
        display: inline-block; padding: 0.15rem 0.55rem;
        border-radius: 999px; font-size: 0.7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .pill-free  { background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success); }
    .pill-key   { background: color-mix(in srgb, var(--warning) 15%, transparent); color: var(--warning); }

    /* ── Never section ─────────────────────────────────────────────── */
    .never {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: clamp(1.5rem, 3vw, 2.5rem);
    }
    .never ul {
        list-style: none; padding: 0; margin: 0;
        display: grid; gap: 0.75rem;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }
    .never li {
        display: flex; align-items: flex-start; gap: 0.65rem;
        color: var(--text-muted); font-size: 0.95rem;
    }
    .never li svg { width: 18px; height: 18px; color: var(--danger); flex-shrink: 0; margin-top: 2px; }
    .never strong { color: var(--text); font-weight: 600; }

    /* ── Final CTA ─────────────────────────────────────────────────── */
    .cta-box {
        background: linear-gradient(135deg, var(--accent-soft), transparent 60%), var(--bg-card);
        border: 1px solid var(--border-strong);
        border-radius: var(--radius);
        padding: clamp(2rem, 5vw, 3.5rem);
        text-align: center;
    }
    .cta-box h2 {
        font-size: clamp(1.5rem, 3.2vw, 2rem);
        margin: 0 0 0.75rem; color: var(--text);
    }
    .cta-box p { color: var(--text-muted); margin: 0 0 1.75rem; }

    /* ── Footer ────────────────────────────────────────────────────── */
    footer {
        border-top: 1px solid var(--border);
        padding: 2.5rem 0 2rem; margin-top: 3rem;
        font-size: 0.88rem; color: var(--text-dim);
    }
    .footer-grid {
        display: grid; gap: 2rem;
        grid-template-columns: 1.4fr repeat(3, 1fr);
        margin-bottom: 2rem;
    }
    @media (max-width: 760px) { .footer-grid { grid-template-columns: 1fr 1fr; } }
    .footer-col h5 { margin: 0 0 0.75rem; font-size: 0.82rem; color: var(--text); text-transform: uppercase; letter-spacing: 0.06em; }
    .footer-col a {
        display: block; color: var(--text-muted);
        padding: 0.2rem 0; font-size: 0.9rem;
    }
    .footer-col a:hover { color: var(--accent); text-decoration: none; }
    .footer-tagline { color: var(--text-muted); margin: 0.5rem 0 0; max-width: 320px; font-size: 0.9rem; }
    .footer-bottom {
        display: flex; flex-wrap: wrap; gap: 1rem;
        align-items: center; justify-content: space-between;
        border-top: 1px solid var(--border);
        padding-top: 1.5rem;
    }
    .footer-bottom .slogan {
        color: var(--text-muted); font-weight: 500;
    }
    .footer-bottom .slogan span { color: var(--text); }
</style>
