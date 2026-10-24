@php
    /*
     * Kamp Love email theme for the shared Markdown layout, in the operating-product
     * style (September 9/10 university-theme contract): white card on a light gray
     * page, #222222 text, DM Sans with an Arial fallback, and school colours where
     * the event maps to a school. It matches the standalone university emails
     * (emails/orders/university-*), so every family email has the same shape.
     *
     * Laravel renders this view with the mail's data and inlines the result, so the
     * colours can follow the event: the school theme from
     * frontend/src/styles/universityThemes.json, otherwise the shared Kamp fallback
     * (_kampFallback). If the manifest is unusable it renders in ink.
     *
     * Fixed literals are the operating palette, enforced by
     * backend/tests/Unit/WhiteLabel/BrandResidueTest.php.
     */
    $kampEvent = (isset($event) && $event instanceof \HiEvents\DomainObjects\EventDomainObject) ? $event : null;
    try {
        // Every Markdown email renders through this theme, so a theme lookup failure
        // must degrade to the ink default below rather than stop the email.
        $kampTheme = (isset($universityTheme) && $universityTheme instanceof \HiEvents\Services\Domain\Email\DTO\UniversityEmailThemeDTO)
            ? $universityTheme
            : app(\HiEvents\Services\Domain\Email\UniversityThemeResolver::class)->resolveForMail($kampEvent);
    } catch (\Throwable $kampThemeError) {
        report($kampThemeError);
        $kampTheme = null;
    }
    $kampTheme ??= new \HiEvents\Services\Domain\Email\DTO\UniversityEmailThemeDTO('#222222', '#222222', '#ffffff', '#ffffff', '#f4f5f7');
@endphp
/* Base */

body,
body *:not(html):not(style):not(br):not(tr):not(code) {
    box-sizing: border-box;
    font-family: 'DM Sans', Arial, Helvetica, sans-serif,
    'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    position: relative;
}

body {
    -webkit-text-size-adjust: 100%;
    background-color: #f4f5f7;
    color: #222222;
    height: 100%;
    line-height: 1.5;
    margin: 0;
    padding: 0;
    width: 100% !important;
}

p,
ul,
ol,
blockquote {
    color: #222222;
    font-size: 16px;
    line-height: 25px;
    margin-top: 0;
    text-align: left;
}

a {
    color: {{ $kampTheme->secondary }};
    text-decoration: underline;
}

a img {
    border: none;
}

/* Typography */

h1 {
    color: #222222;
    font-size: 28px;
    font-weight: 700;
    line-height: 34px;
    margin: 0 0 18px;
    text-align: left;
}

h2 {
    color: #222222;
    font-size: 20px;
    font-weight: 700;
    line-height: 26px;
    margin: 0 0 12px;
    text-align: left;
}

h3 {
    color: #222222;
    font-size: 16px;
    font-weight: 700;
    line-height: 24px;
    margin: 0 0 8px;
    text-align: left;
}

p {
    margin: 0 0 16px;
}

p.sub {
    color: #555b66;
    font-size: 14px;
    line-height: 21px;
}

img {
    max-width: 100%;
}

/* Layout: page, then one fluid card no wider than 600px */

.wrapper {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: #f4f5f7;
    border-collapse: collapse;
    margin: 0;
    padding: 0;
    table-layout: fixed;
    width: 100%;
}

.wrapper-cell {
    padding: 12px;
}

.card {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: #ffffff;
    border: 1px solid #d8dbe2;
    border-collapse: collapse;
    border-radius: 10px;
    margin: 0 auto;
    max-width: 600px;
    table-layout: fixed;
    width: 100%;
}

.card-bar {
    background-color: {{ $kampTheme->primary }};
    border-radius: 10px 10px 0 0;
    color: {{ $kampTheme->onPrimary }};
    font-size: 0;
    height: 10px;
    line-height: 10px;
}

/* Header: the "Kamp Love" eyebrow in the theme colour */

.header {
    padding: 28px 20px 0;
    text-align: left;
}

.header a {
    color: {{ $kampTheme->primary }};
    text-decoration: none;
}

.logo {
    max-width: 300px;
    width: 150px;
}

.brand-wordmark {
    color: {{ $kampTheme->primary }};
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .08em;
    line-height: 20px;
    text-transform: uppercase;
}

/* Body */

.content-cell {
    overflow-wrap: anywhere;
    padding: 24px 20px 32px;
    word-break: break-word;
}

/* Subcopy */

.subcopy {
    border-top: 1px solid #d8dbe2;
    margin-top: 24px;
    padding-top: 20px;
}

.subcopy p {
    color: #555b66;
    font-size: 14px;
    line-height: 21px;
}

.subcopy a {
    word-break: break-all;
}

/* Footer: below the card */

.footer {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    border-collapse: collapse;
    margin: 0 auto;
    max-width: 600px;
    table-layout: fixed;
    width: 100%;
}

.footer-cell {
    overflow-wrap: anywhere;
    padding: 20px 20px 8px;
    text-align: center;
    word-break: break-word;
}

.footer p {
    color: #555b66;
    font-size: 13px;
    line-height: 20px;
    margin: 0 0 4px;
    text-align: center;
}

.footer a {
    color: #555b66;
    text-decoration: underline;
}

/* Tables */

.table table {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 24px auto;
    width: 100%;
}

.table th {
    border-bottom: 1px solid #d8dbe2;
    color: #222222;
    margin: 0;
    padding-bottom: 8px;
}

.table td {
    color: #222222;
    font-size: 15px;
    line-height: 21px;
    margin: 0;
    padding: 10px 0;
}

/* Buttons: one full-width pill in the theme colour */

.action {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    border-collapse: separate;
    margin: 28px 0;
    padding: 0;
    width: 100%;
}

.button-cell {
    background-color: {{ $kampTheme->secondary }};
    border-radius: 999px;
}

.button {
    -webkit-text-size-adjust: none;
    border-radius: 999px;
    box-sizing: border-box;
    color: {{ $kampTheme->onSecondary }};
    display: block;
    font-size: 16px;
    font-weight: 700;
    line-height: 20px;
    padding: 15px 12px;
    text-align: center;
    text-decoration: none;
    width: 100%;
}

.button-cell-red,
.button-cell-error {
    background-color: #9f3620;
}

.button-red,
.button-error {
    color: #ffffff;
}

/* Panels: the soft theme box used for event details */

.panel {
    background-color: {{ $kampTheme->secondarySoft }};
    border-collapse: collapse;
    border-radius: 8px;
    margin: 0 0 24px;
}

.panel-content {
    color: #222222;
    padding: 18px 20px;
}

.panel-content p {
    color: #222222;
}

.panel-item {
    padding: 0;
}

.panel-item p:last-of-type {
    margin-bottom: 0;
    padding-bottom: 0;
}

/* Utilities */

.break-all {
    word-break: break-all;
}

/*
 * Licence attribution. Subordinate by order and scale only.
 * 13px #555b66 on #f4f5f7 computes to ~6.3:1, so it stays legible.
 * Do not shrink below 13px, hide, or remove without a licensing decision.
 */
.attribution {
    color: #555b66;
    font-size: 13px;
}

.attribution a {
    color: #555b66;
    text-decoration: underline;
}
