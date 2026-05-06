#!/usr/bin/env node
// Laravel Vitals — Playwright + Lighthouse runner.
// Invoked by PlaywrightDriver as: node vitals-playwright.mjs --url=... --device=mobile --headers='{"X-Vitals-Audit-Id":"..."}'

import { chromium } from 'playwright';
import { playAudit } from 'playwright-lighthouse';

const args = Object.fromEntries(
    process.argv.slice(2).map(a => {
        const [k, ...v] = a.replace(/^--/, '').split('=');
        return [k, v.join('=')];
    })
);

const url = args.url;
const device = args.device || 'mobile';
const headers = args.headers ? JSON.parse(args.headers) : {};

if (!url) {
    console.error('Missing --url');
    process.exit(2);
}

const browser = await chromium.launch({ args: ['--remote-debugging-port=9222', '--no-sandbox'] });
const context = await browser.newContext({ extraHTTPHeaders: headers });
const page = await context.newPage();

try {
    const result = await playAudit({
        page,
        port: 9222,
        config: {
            extends: 'lighthouse:default',
            settings: {
                formFactor: device,
                throttlingMethod: 'simulate',
                onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'],
                screenEmulation: device === 'mobile'
                    ? { mobile: true, width: 360, height: 640, deviceScaleFactor: 2 }
                    : { mobile: false, width: 1366, height: 768, deviceScaleFactor: 1 },
            },
        },
        reports: { formats: { json: true }, name: 'vitals' },
        thresholds: {},
    });

    process.stdout.write(JSON.stringify(result.lhr));
} catch (e) {
    console.error(e.message || String(e));
    process.exit(1);
} finally {
    await browser.close();
}
