<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adlatere SaaS — Software jurídico para despachos</title>
    <meta name="description" content="Adlatere SaaS: gestión de expedientes, actuaciones, plazos y equipos para despachos jurídicos de Ecuador.">
    <link rel="icon" href="{{ asset('assets2/images/favicon.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets2/css/vendors/bootstrap.css') }}">
    @livewireStyles
    <style>
        :root {
            --ink: #0e1726;
            --muted: #5d677c;
            --line: #e3e8f1;
            --soft: #f6f8fc;
            --white: #ffffff;
            --brand: #173b99;
            --brand-2: #0a1d42;
            --accent: #22c55e;
            --shadow: 0 18px 54px rgba(13, 27, 56, .11);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--soft);
            color: var(--ink);
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button, input, select { font: inherit; }
        .container { width: min(1180px, 92%); margin-inline: auto; }
        .icon { width: 20px; height: 20px; display: inline-block; vertical-align: middle; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 9px;
            color: #d5e3ff; font-size: 12px; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
        }
        .eyebrow::before { content: ""; width: 24px; height: 1px; background: currentColor; opacity: .7; }
        .eyebrow.dark { color: #415b9a; }

        .site-header {
            position: sticky; top: 0; z-index: 30;
            background: rgba(6, 15, 36, .9);
            border-bottom: 1px solid rgba(255,255,255,.09);
            backdrop-filter: blur(14px);
        }
        .nav-wrap { display: flex; align-items: center; gap: 24px; height: 74px; }
        .brand { display: flex; align-items: center; gap: 10px; color: #fff; font-weight: 800; letter-spacing: -.03em; }
        .brand-mark {
            width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center;
            background: linear-gradient(135deg, #2b63e8, #173b99);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.3), 0 8px 18px rgba(23,59,153,.35);
            color: #fff; font-size: 20px; font-weight: 800; letter-spacing: -.04em;
        }
        .brand small { display: block; font-size: 10px; color: #a9b9dd; font-weight: 500; letter-spacing: .16em; text-transform: uppercase; }
        .nav { display: flex; align-items: center; gap: 5px; margin-left: auto; }
        .nav a {
            color: #cdd9f4; font-size: 13px; font-weight: 500;
            padding: 8px 11px; border-radius: 8px; transition: .2s;
        }
        .nav a:hover { color: #fff; background: rgba(255,255,255,.08); }
        .nav-actions { display: flex; align-items: center; gap: 8px; margin-left: 8px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            border: 0; border-radius: 10px; cursor: pointer;
            padding: 11px 17px; font-size: 14px; font-weight: 700;
            transition: .2s ease; white-space: nowrap;
        }
        .btn-primary { background: linear-gradient(135deg, #27cc67, #17a84d); color: #06130a; box-shadow: 0 10px 24px rgba(34,197,94,.25); }
        .btn-primary:hover { transform: translateY(-1px); filter: brightness(1.04); }
        .btn-outline { color: #e6efff; border: 1px solid rgba(255,255,255,.25); background: transparent; }
        .btn-outline:hover { background: rgba(255,255,255,.09); }
        .btn-ghost-light { color: #254889; background: #edf3ff; }
        .btn-ghost-light:hover { background: #e2ebff; }
        .btn-sm { padding: 8px 12px; font-size: 12px; border-radius: 8px; }

        .hero {
            position: relative; overflow: hidden;
            color: #f5f8ff; padding: 77px 0 117px;
            background:
                radial-gradient(ellipse at 76% 40%, rgba(75,126,255,.33), transparent 54%),
                radial-gradient(ellipse at 12% 86%, rgba(34,197,94,.16), transparent 47%),
                linear-gradient(146deg, #06142d 0%, #0b2352 54%, #153e8e 100%);
        }
        .hero::after {
            content: ""; position: absolute; inset: auto 0 0; height: 130px;
            background: linear-gradient(180deg, transparent, rgba(3,12,30,.28));
            pointer-events: none;
        }
        .hero-grid {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: 1.02fr .98fr; gap: 52px; align-items: center;
        }
        .hero-copy h1 {
            margin: 19px 0 18px; max-width: 650px;
            font-size: clamp(2.45rem, 5vw, 4.35rem); line-height: 1.04;
            letter-spacing: -.055em; font-weight: 800;
        }
        .hero-copy h1 em { font-style: normal; color: #c8ffd8; }
        .hero-copy p {
            margin: 0; max-width: 575px; color: #bfd0ee;
            font-size: 18px; line-height: 1.68;
        }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 11px; margin-top: 28px; }
        .hero-actions .btn-primary { padding-inline: 22px; }
        .micro {
            display: flex; flex-wrap: wrap; align-items: center; gap: 17px;
            margin-top: 23px; color: #b5c8e8; font-size: 12px; font-weight: 500;
        }
        .micro span { display: inline-flex; align-items: center; gap: 6px; }
        .micro .icon { width: 14px; height: 14px; color: #89f5a8; }
        .hero-card {
            position: relative; padding: 18px; border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,.17), rgba(255,255,255,.07));
            border: 1px solid rgba(255,255,255,.24);
            box-shadow: 0 30px 80px rgba(0, 0, 0, .3);
            backdrop-filter: blur(16px);
        }
        .app-window {
            background: #f8faff; color: #13213c; border-radius: 12px;
            overflow: hidden; border: 1px solid rgba(255,255,255,.55);
            box-shadow: 0 18px 45px rgba(4, 14, 34, .22);
        }
        .app-bar {
            display: flex; align-items: center; gap: 7px; height: 35px;
            padding: 0 13px; background: #11254c; color: #d9e6ff; font-size: 10px;
        }
        .app-bar .dot { width: 7px; height: 7px; border-radius: 50%; background: #f0a552; }
        .app-bar .dot.g { background: #5dd17d; }
        .app-bar .dot.b { background: #6d9eff; }
        .app-bar .path { margin-left: 10px; opacity: .7; }
        .app-body { display: grid; grid-template-columns: 120px 1fr; min-height: 370px; }
        .app-side { background: #0d2149; color: #93a9ce; padding: 16px 12px; font-size: 10px; }
        .side-brand { display: flex; align-items: center; gap: 7px; color: #fff; font-weight: 700; font-size: 11px; margin-bottom: 22px; }
        .side-brand .mini-mark { width: 20px; height: 20px; border-radius: 6px; background: #2d66e7; display: grid; place-items: center; font-weight: 800; }
        .side-item { display: flex; align-items: center; gap: 8px; padding: 7px 0; }
        .side-item.active { color: #fff; }
        .side-item::before { content: ""; width: 4px; height: 4px; border-radius: 50%; background: currentColor; opacity: .6; }
        .app-main { padding: 17px; background: #f7f9ff; }
        .app-head { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
        .app-head small { display: block; color: #7d8aa7; font-size: 9px; margin-bottom: 3px; }
        .app-head strong { font-size: 15px; letter-spacing: -.02em; }
        .mini-new { background: #173b99; color: #fff; border: 0; border-radius: 5px; font-size: 8px; padding: 6px 8px; }
        .app-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin: 14px 0; }
        .stat { background: #fff; border: 1px solid #e4eaf5; border-radius: 8px; padding: 8px; }
        .stat b { font-size: 18px; display: block; line-height: 1.1; }
        .stat span { font-size: 8px; color: #7b88a2; }
        .app-list-title { display: flex; justify-content: space-between; align-items: center; margin: 12px 0 7px; font-size: 9px; color: #7b88a2; }
        .app-list-title b { color: #173b99; }
        .case-row { display: flex; gap: 8px; align-items: center; background: #fff; border: 1px solid #e4eaf5; border-radius: 8px; padding: 8px; margin-bottom: 6px; }
        .case-mark { width: 22px; height: 22px; border-radius: 6px; display: grid; place-items: center; background: #e7efff; color: #315fc6; font-size: 10px; }
        .case-row p { margin: 0; font-size: 8px; color: #73809a; line-height: 1.3; }
        .case-row p strong { color: #223557; font-size: 9px; }
        .case-row .due { margin-left: auto; font-size: 7px; color: #9b6818; background: #fff7e8; padding: 3px 5px; border-radius: 4px; white-space: nowrap; }
        .preview-float {
            position: absolute; left: -22px; bottom: -23px;
            background: #fff; color: #17243d; border-radius: 12px; padding: 11px 14px;
            box-shadow: 0 14px 32px rgba(3, 14, 34, .22); font-size: 11px; font-weight: 700;
            display: flex; align-items: center; gap: 9px;
        }
        .preview-float .icon { color: #18a34e; width: 17px; height: 17px; }

        .section { padding: 84px 0; }
        .section-head { max-width: 710px; }
        .section-head h2 {
            margin: 9px 0 12px; font-size: clamp(1.8rem, 3.4vw, 2.65rem);
            line-height: 1.16; letter-spacing: -.04em; font-weight: 800;
        }
        .section-head p { margin: 0; color: var(--muted); font-size: 16px; }

        .registration-section {
            position: relative; margin-top: -55px; padding: 0 0 76px;
        }
        .registration-shell {
            background: #fff; border: 1px solid var(--line); border-radius: 20px;
            box-shadow: var(--shadow); padding: 37px 38px 39px;
        }
        .registration-top {
            display: flex; justify-content: space-between; align-items: flex-end; gap: 24px;
            padding-bottom: 26px; border-bottom: 1px solid #edf0f6;
        }
        .registration-top h2 { margin: 5px 0 8px; font-size: 30px; letter-spacing: -.04em; line-height: 1.15; }
        .registration-top p { margin: 0; color: #6b7890; font-size: 14px; }
        .registration-note {
            max-width: 260px; padding: 12px 14px; border-radius: 10px;
            background: #f4f8ff; color: #4c628b; font-size: 12px;
            border: 1px solid #e2ebfa;
        }
        .registration-note strong { color: #173b99; }
        .plans-registration { margin-top: 27px; }
        .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .plan-card {
            display: flex; flex-direction: column; min-height: 335px;
            background: #fbfcff; border: 1px solid #e0e7f2; border-radius: 15px; padding: 22px;
        }
        .plan-card:hover { border-color: #c7d5eb; box-shadow: 0 12px 28px rgba(21,50,107,.07); }
        .plan-label { color: #173b99; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .plan-price { display: flex; align-items: baseline; gap: 5px; margin: 11px 0 4px; }
        .plan-price strong { font-size: 36px; line-height: 1; letter-spacing: -.06em; }
        .plan-price span { color: #7b869b; font-size: 12px; }
        .plan-copy { min-height: 42px; margin: 0 0 10px; color: #69758b; font-size: 12px; }
        .plan-features { list-style: none; margin: 11px 0 21px; padding: 0; }
        .plan-features li { display: flex; gap: 8px; padding: 7px 0; border-top: 1px solid #edf0f6; color: #344057; font-size: 12px; }
        .plan-features .icon { width: 14px; height: 14px; color: #1aa553; flex: 0 0 auto; margin-top: 2px; }
        .plan-card .btn { margin-top: auto; width: 100%; }
        .plans-note { margin: 18px 0 0; color: #8a94a6; font-size: 12px; text-align: center; }
        .plans-empty { padding: 30px; border: 1px dashed #cfdae9; border-radius: 12px; color: #7b869b; text-align: center; }

        .features-section { background: #fff; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 17px; margin-top: 33px; }
        .feature-card {
            position: relative; overflow: hidden; min-height: 185px;
            padding: 22px; background: #f9fbff; border: 1px solid #e5ebf5; border-radius: 14px;
            transition: .22s;
        }
        .feature-card:hover { transform: translateY(-3px); border-color: #cbd8ef; box-shadow: 0 14px 30px rgba(21,50,107,.08); }
        .feature-icon {
            width: 41px; height: 41px; display: grid; place-items: center; border-radius: 10px;
            background: #e7efff; color: #2256be; margin-bottom: 17px;
        }
        .feature-icon .icon { width: 21px; height: 21px; }
        .feature-card h3 { margin: 0 0 9px; font-size: 15px; letter-spacing: -.015em; }
        .feature-card p { margin: 0; color: #68748a; font-size: 13px; line-height: 1.6; }

        .process-section { background: linear-gradient(180deg, #f5f8fe, #eef3fb); }
        .process-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-top: 33px; }
        .process-card { background: #fff; border: 1px solid #e1e8f4; border-radius: 13px; padding: 19px; }
        .process-card .num { color: #b1c1de; font-size: 12px; font-weight: 800; letter-spacing: .08em; }
        .process-card h3 { margin: 14px 0 7px; font-size: 15px; }
        .process-card p { margin: 0; color: #6c788e; font-size: 12px; line-height: 1.6; }

        .security-section { background: #07162e; color: #ecf3ff; }
        .security-section .section-head p { color: #a9bddc; }
        .security-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 34px; margin-top: 33px; align-items: start; }
        .security-copy { color: #aebfdc; font-size: 14px; }
        .security-copy strong { color: #ecf3ff; }
        .security-items { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .security-item {
            display: flex; gap: 10px; align-items: flex-start;
            padding: 13px; background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.1); border-radius: 10px;
            color: #d8e4f8; font-size: 12px;
        }
        .security-item .icon { width: 17px; height: 17px; color: #8df2a2; flex: 0 0 auto; margin-top: 1px; }

        .faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; margin-top: 25px; }
        .faq-item { border-bottom: 1px solid #e1e7f1; padding: 14px 0; }
        .faq-item summary { cursor: pointer; font-size: 14px; font-weight: 700; color: #253550; list-style: none; }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary::after { content: "+"; float: right; color: #6d84af; font-size: 18px; line-height: 1; }
        .faq-item[open] summary::after { content: "−"; }
        .faq-item p { margin: 9px 0 0; color: #6c788d; font-size: 13px; }

        .final-cta {
            padding: 59px 0;
            background:
                radial-gradient(ellipse at 80% 10%, rgba(34,197,94,.28), transparent 45%),
                linear-gradient(120deg, #0a1d42, #173b99);
            color: #fff;
        }
        .final-cta-row { display: flex; align-items: center; justify-content: space-between; gap: 25px; }
        .final-cta h2 { margin: 8px 0 8px; font-size: clamp(1.7rem, 3vw, 2.4rem); letter-spacing: -.045em; line-height: 1.15; }
        .final-cta p { margin: 0; color: #bfd1ee; font-size: 14px; }

        .footer { background: #061126; color: #bed0eb; padding: 39px 0 27px; }
        .footer-grid { display: grid; grid-template-columns: 1.4fr repeat(3, 1fr); gap: 26px; }
        .footer .brand { margin-bottom: 11px; }
        .footer p, .footer a { color: #a9bdd9; font-size: 12px; line-height: 1.8; }
        .footer a:hover { color: #fff; text-decoration: underline; }
        .footer h4 { margin: 3px 0 10px; color: #e7effc; font-size: 12px; letter-spacing: .03em; }
        .footer-bottom { margin-top: 28px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,.1); color: #8299bc; font-size: 11px; display: flex; justify-content: space-between; gap: 12px; }
        .mock-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 9px; border-radius: 999px; background: #fff3d9; color: #805900; font-size: 10px; font-weight: 700; letter-spacing: .04em; }

        /* Modal de registro (Payator) */
        .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 60px rgba(5,18,45,.3); }
        .modal-header { background: linear-gradient(135deg, #0d2450, #173b99); color: #fff; border: 0; padding: 20px 24px; }
        .modal-title { font-size: 20px; font-weight: 800; letter-spacing: -.02em; }
        .modal-header .btn-close { filter: invert(1); opacity: .9; }
        .modal-body { padding: 24px; background: #fff; }
        .modal .form-label { color: #344057; font-size: 12px; font-weight: 700; margin-bottom: 5px; }
        .modal .form-control, .modal .form-select {
            border: 1px solid #d9e0eb; border-radius: 8px; padding: 10px 12px;
            font-size: 13px; color: #17243d; box-shadow: none;
        }
        .modal .form-control:focus, .modal .form-select:focus {
            border-color: #7397e7; box-shadow: 0 0 0 3px rgba(37,88,190,.12);
        }
        .modal .input-group-text { background: #f5f7fb; border-color: #d9e0eb; color: #7a8498; font-size: 11px; }
        .modal .btn { font-size: 13px; }
        .reg-summary { background: #f4f8ff; border: 1px solid #e1ebfa; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; color: #4d6286; font-size: 12px; }
        .reg-summary strong { color: #173b99; }
        .reg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px; }
        .reg-grid .full { grid-column: 1 / -1; }
        .payment-created { text-align: center; padding: 7px 0 3px; }
        .payment-created h4 { color: #15803d; font-weight: 800; font-size: 20px; }
        .payment-created .link-box { margin-top: 13px; }
        .payment-created .link-box input { font-size: 11px; }

        @media (max-width: 980px) {
            .hero-grid { grid-template-columns: 1fr; gap: 34px; }
            .hero-card { max-width: 660px; }
            .feature-grid, .process-grid { grid-template-columns: repeat(2, 1fr); }
            .security-grid { grid-template-columns: 1fr; }
            .nav { display: none; }
            .final-cta-row { align-items: flex-start; flex-direction: column; }
            .registration-top { align-items: flex-start; flex-direction: column; }
            .registration-note { max-width: none; }
        }
        @media (max-width: 620px) {
            .container { width: min(100% - 30px, 1180px); }
            .site-header .nav-actions .btn-outline { display: none; }
            .hero { padding-top: 51px; padding-bottom: 97px; }
            .hero-copy h1 { font-size: 2.55rem; }
            .hero-copy p { font-size: 16px; }
            .hero-card { padding: 10px; }
            .preview-float { left: 9px; bottom: -17px; }
            .app-body { grid-template-columns: 1fr; }
            .app-side { display: none; }
            .section { padding: 61px 0; }
            .registration-shell { padding: 25px 20px; }
            .registration-top h2 { font-size: 25px; }
            .feature-grid, .process-grid, .security-items, .faq-grid, .footer-grid, .reg-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; }
        }
    </style>
</head>
<body>

<svg aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden" focusable="false">
    <symbol id="i-folder" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3v3M17 3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></symbol>
    <symbol id="i-coins" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
    <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5M4 19h16M8 16v-4M12 16V8M16 16v-6"/></symbol>
    <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M10 7V4h4v3M3 12h18M10 12v2h4v-2"/></symbol>
</svg>

<header class="site-header">
    <div class="container nav-wrap">
        <a href="#inicio" class="brand" aria-label="Adlatere SaaS">
            <span class="brand-mark">A</span>
            <span>Adlatere <small>Software jurídico</small></span>
        </a>
        <nav class="nav" aria-label="Navegación principal">
            <a href="#funcionalidades">Funcionalidades</a>
            <a href="#como-funciona">Cómo funciona</a>
            <a href="#registro">Planes</a>
            <a href="#seguridad">Seguridad</a>
            <a href="#preguntas">FAQ</a>
            <a href="{{ url('/dash') }}">Admin</a>
        </nav>
        <div class="nav-actions">
            <a href="javascript:void(0)" class="btn btn-outline btn-sm" onclick="tenantLogin()">Iniciar sesión</a>
            <a href="#registro" class="btn btn-primary btn-sm">Crear cuenta <svg class="icon" style="width:14px;height:14px"><use href="#i-arrow"/></svg></a>
        </div>
    </div>
</header>

<main>
    <section class="hero" id="inicio">
        <div class="container hero-grid">
            <div class="hero-copy">
                <div class="eyebrow">SaaS jurídico para Ecuador</div>
                <h1>El expediente completo de tu despacho, <em>sin perder un plazo.</em></h1>
                <p>
                    Organiza casos, clientes, actuaciones y documentos desde una sola plataforma.
                    Adlatere está diseñado para despachos que necesitan orden, trazabilidad y control real
                    sobre su operación diaria.
                </p>
                <div class="hero-actions">
                    <a href="#registro" class="btn btn-primary">Empezar ahora <svg class="icon" style="width:15px;height:15px"><use href="#i-arrow"/></svg></a>
                    <a href="#funcionalidades" class="btn btn-outline">Ver funcionalidades</a>
                </div>
                <div class="micro">
                    <span><svg class="icon"><use href="#i-check"/></svg> Sin tarjeta para empezar</span>
                    <span><svg class="icon"><use href="#i-check"/></svg> Base aislada por despacho</span>
                    <span><svg class="icon"><use href="#i-check"/></svg> Importación inicial</span>
                </div>
            </div>

            <div class="hero-card">
                <div class="app-window" aria-label="Vista previa del sistema">
                    <div class="app-bar">
                        <span class="dot"></span><span class="dot g"></span><span class="dot b"></span>
                        <span class="path">panel.adlatere.ec / expedientes</span>
                    </div>
                    <div class="app-body">
                        <aside class="app-side">
                            <div class="side-brand"><span class="mini-mark">A</span> Adlatere SaaS</div>
                            <div class="side-item active">Panel</div>
                            <div class="side-item">Expedientes</div>
                            <div class="side-item">Actuaciones</div>
                            <div class="side-item">Agenda</div>
                            <div class="side-item">Clientes</div>
                            <div class="side-item">Equipo</div>
                        </aside>
                        <div class="app-main">
                            <div class="app-head">
                                <div>
                                    <small>Jueves, 30 de octubre</small>
                                    <strong>Buenos días, Dra. Rosales</strong>
                                </div>
                                <button class="mini-new" type="button">+ Nuevo caso</button>
                            </div>
                            <div class="app-stats">
                                <div class="stat"><b>12</b><span>Casos activos</span></div>
                                <div class="stat"><b>4</b><span>Vencen esta semana</span></div>
                                <div class="stat"><b>28</b><span>Documentos</span></div>
                            </div>
                            <div class="app-list-title"><span>EXPEDIENTES RECIENTES</span><b>Ver todos</b></div>
                            <div class="case-row">
                                <span class="case-mark">C</span>
                                <p><strong>Paredes Villacís vs. Constructora Andina</strong><br>17230-2026-04187 · Laboral</p>
                                <span class="due">Vence 2 días</span>
                            </div>
                            <div class="case-row">
                                <span class="case-mark" style="background:#e9f8ee;color:#18884b">J</span>
                                <p><strong>Jarrín Ocaña vs. Textiles del Litoral</strong><br>09332-2026-01596 · Civil</p>
                                <span class="due" style="color:#226a43;background:#e9f8ee">Audiencia 5 días</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="preview-float"><svg class="icon"><use href="#i-shield"/></svg> Información aislada y trazable</div>
            </div>
        </div>
    </section>

    <section class="registration-section" id="registro">
        <div class="container">
            <div class="registration-shell">
                <div class="registration-top">
                    <div>
                        <div class="eyebrow dark">Tu espacio de trabajo</div>
                        <h2>Crea el espacio de tu despacho.</h2>
                        <p>Selecciona un plan para iniciar el registro de tu estudio. Los importes y características se administran desde el panel central.</p>
                    </div>
                    <div class="registration-note">
                        <strong>Registro de tenant</strong><br>
                        Cada despacho obtiene un subdominio y una base de datos independiente.
                    </div>
                </div>

                <div class="plans-registration">
                    <livewire:payator />
                </div>
            </div>
        </div>
    </section>

    <section class="section features-section" id="funcionalidades">
        <div class="container">
            <div class="section-head">
                <div class="eyebrow dark">Todo en una sola plataforma</div>
                <h2>Menos hojas sueltas. Más control sobre tu despacho.</h2>
                <p>Las piezas que necesitas para gestionar la operación jurídica sin saltar entre Excel, correo y mensajes.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-folder"/></svg></div><h3>Expedientes ordenados</h3><p>Carátula, sujetos procesales, patrocinador y estado del juicio en una ficha única y consultable.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-file"/></svg></div><h3>Actuaciones con historial</h3><p>Cada escrito, audiencia o actuación con responsable, fecha y trazabilidad de cambios.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-briefcase"/></svg></div><h3>Tareas que se convierten en actividad</h3><p>El trabajo interno no se pierde: una tarea completada puede pasar a la actividad del juicio.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-file"/></svg></div><h3>Plantillas con variables</h3><p>Demandas y escritos reutilizables con datos del expediente que se completan automáticamente.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-calendar"/></svg></div><h3>Agenda y plazos</h3><p>Audiencias, vencimientos y próximos pasos visibles para todo el equipo.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-users"/></svg></div><h3>Roles y permisos</h3><p>Abogado patrocinador, asistente, pasante o administrador: cada persona ve lo que le corresponde.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-bell"/></svg></div><h3>Notificaciones útiles</h3><p>Avisos de tareas, cambios y vencimientos sin llenar la bandeja del equipo de ruido.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-coins"/></svg></div><h3>Consultas y economía</h3><p>Honorarios, cobros y gastos vinculados a cada caso, en un circuito separado de la agenda procesal.</p></article>
                <article class="feature-card"><div class="feature-icon"><svg class="icon"><use href="#i-shield"/></svg></div><h3>Privacidad por diseño</h3><p>Información aislada por despacho, accesos controlados y registro de actividad del sistema.</p></article>
            </div>
        </div>
    </section>

    <section class="section process-section" id="como-funciona">
        <div class="container">
            <div class="section-head">
                <div class="eyebrow dark">Del registro a la operación</div>
                <h2>Empezar no debería ser un proyecto.</h2>
                <p>Un onboarding claro para que tu despacho esté trabajando dentro de la plataforma lo antes posible.</p>
            </div>
            <div class="process-grid">
                <article class="process-card"><div class="num">01</div><h3>Registra tu estudio</h3><p>Nombre, subdominio y responsable de la cuenta.</p></article>
                <article class="process-card"><div class="num">02</div><h3>Elige tu plan</h3><p>Un plan que acompañe el tamaño actual de tu equipo.</p></article>
                <article class="process-card"><div class="num">03</div><h3>Configura tu espacio</h3><p>Catálogos, usuarios y roles principales de tu despacho.</p></article>
                <article class="process-card"><div class="num">04</div><h3>Importa y trabaja</h3><p>Sube tu base inicial y empieza a gestionar expedientes.</p></article>
            </div>
        </div>
    </section>

    <section class="section security-section" id="seguridad">
        <div class="container">
            <div class="section-head">
                <div class="eyebrow">Seguridad y confianza</div>
                <h2>La información de tus casos no se mezcla con la de otro despacho.</h2>
                <p>Una base de datos independiente por cada estudio, con accesos y trazabilidad desde el primer día.</p>
            </div>
            <div class="security-grid">
                <div class="security-copy">
                    <p><strong>Adlatere SaaS está pensado para operar con información sensible.</strong></p>
                    <p>Cada despacho tiene su propio espacio de datos. El acceso está segmentado por rol, las acciones quedan registradas y los documentos se administran dentro del expediente correcto.</p>
                </div>
                <div class="security-items">
                    <div class="security-item"><svg class="icon"><use href="#i-lock"/></svg> Base aislada por despacho</div>
                    <div class="security-item"><svg class="icon"><use href="#i-shield"/></svg> Control por roles</div>
                    <div class="security-item"><svg class="icon"><use href="#i-chart"/></svg> Historial de movimientos</div>
                    <div class="security-item"><svg class="icon"><use href="#i-briefcase"/></svg> Documentos vinculados</div>
                    <div class="security-item"><svg class="icon"><use href="#i-check"/></svg> Respaldos del sistema</div>
                    <div class="security-item"><svg class="icon"><use href="#i-file"/></svg> Exportación de información</div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="preguntas">
        <div class="container">
            <div class="section-head">
                <div class="eyebrow dark">Antes de empezar</div>
                <h2>Preguntas frecuentes</h2>
            </div>
            <div class="faq-grid">
                <details class="faq-item" open><summary>¿Puedo importar mis expedientes actuales?</summary><p>Sí. La plataforma contempla una carga inicial desde Excel para clientes, usuarios y expedientes, con validaciones antes de incorporar la información.</p></details>
                <details class="faq-item"><summary>¿Cada despacho tiene su propia base de datos?</summary><p>Sí. Adlatere SaaS trabaja con un esquema multi-tenant donde cada estudio tiene una base de datos separada.</p></details>
                <details class="faq-item"><summary>¿Puedo definir diferentes roles dentro del equipo?</summary><p>Sí. Puedes asignar acceso de administrador, abogado patrocinador, asistente o pasante según las responsabilidades de cada persona.</p></details>
                <details class="faq-item"><summary>¿Qué ocurre con mis documentos y plantillas?</summary><p>Los documentos se vinculan a la actividad o audiencia correspondiente, y las plantillas pueden contener variables que se completan con los datos del expediente.</p></details>
                <details class="faq-item"><summary>¿Puedo cambiar de plan más adelante?</summary><p>Sí. Los planes están pensados para acompañar el crecimiento del despacho, desde un abogado independiente hasta un equipo consolidado.</p></details>
                <details class="faq-item"><summary>¿Cómo accedo si ya tengo una cuenta?</summary><p>Usa el botón Iniciar sesión e indica el subdominio de tu espacio. Serás redirigido al login de tu despacho.</p></details>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="container final-cta-row">
            <div>
                <div class="eyebrow">Tu despacho, en orden</div>
                <h2>Empieza con una base sólida.</h2>
                <p>Crea tu espacio, ordena tus expedientes y dale a tu equipo una sola forma de trabajar.</p>
            </div>
            <a href="#registro" class="btn btn-primary">Crear mi cuenta <svg class="icon" style="width:15px;height:15px"><use href="#i-arrow"/></svg></a>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="brand"><span class="brand-mark">A</span><span>Adlatere <small>Software jurídico</small></span></div>
                <p>Una plataforma para despachos que quieren operar con orden, trazabilidad e información aislada por estudio.</p>
                <span class="mock-badge">Adlatere SaaS · Software jurídico</span>
            </div>
            <div><h4>Producto</h4><p><a href="#funcionalidades">Funcionalidades</a><br><a href="#como-funciona">Cómo funciona</a><br><a href="#registro">Planes</a><br><a href="#seguridad">Seguridad</a></p></div>
            <div><h4>Recursos</h4><p><a href="#preguntas">Preguntas frecuentes</a><br><a href="#registro">Empezar prueba</a><br><a href="{{ url('/dash') }}">Administrar tenants</a><br><a href="{{ url('/planes') }}">Administrar planes</a><br><a href="#inicio">Inicio</a></p></div>
            <div><h4>Legal</h4><p><a href="#">Términos del servicio</a><br><a href="#">Política de privacidad</a><br><a href="#">Protección de datos</a></p></div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 Adlatere SaaS · Todos los derechos reservados.</span>
            <span>Hecho para la realidad jurídica de Ecuador.</span>
        </div>
    </div>
</footer>

<script src="{{ asset('assets2/js/vendors/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets2/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
@livewireScripts
<script>
    window.addEventListener('open-modal', function () {
        const modalId = '#paymentModal';
        const modalEl = document.querySelector(modalId);

        if (!modalEl) return;

        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
            jQuery(modalId).modal('show');
        } else if (typeof window.bootstrap !== 'undefined') {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    function tenantLogin() {
        const subdominio = window.prompt('Ingresa el nombre de tu espacio (subdominio):');
        if (!subdominio) return;

        const host = @json(request()->getHost());
        const limpio = subdominio.trim().toLowerCase().replace(/[^a-z0-9-]/g, '');
        if (!limpio) return;

        window.location.href = window.location.protocol + '//' + limpio + '.' + host + '/login';
    }
</script>
</body>
</html>
