#!/usr/bin/env node
// Laravel Vitals — Playwright + Lighthouse runner.
// Invoked by PlaywrightDriver as: node vitals-playwright.mjs --url=... --device=mobile --headers='{"X-Vitals-Audit-Id":"..."}'

let chromium, playAudit;
try {
    ({ chromium } = await import('playwright'));
    ({ playAudit } = await import('playwright-lighthouse'));
} catch {
    console.error(
        'Laravel Vitals: the Playwright driver requires the "playwright" and ' +
        '"playwright-lighthouse" npm packages, which are not installed.\n\n' +
        'Install them in your project root:\n\n' +
        '  npm install --save-dev playwright playwright-lighthouse\n' +
        '  npx playwright install chromium\n\n' +
        'Or switch drivers via VITALS_DRIVER (local | pagespeed). See ' +
        'https://github.com/corentinbtmps/laravel-vitals#driver-installation'
    );
    process.exit(3);
}

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

    // Agent-readiness probe (WebMCP). Runs AFTER the Lighthouse audit, in an
    // isolated context without the vitals telemetry headers, so it can never
    // alter the Lighthouse result nor pollute backend telemetry. Any failure
    // here leaves `agentic` empty and the audit emits exactly as before.
    const agentic = await probeWebMcp(browser, url).catch(() => ({}));

    process.stdout.write(JSON.stringify({ lhr: result.lhr, agentic }));
} catch (e) {
    console.error(e.message || String(e));
    process.exit(1);
} finally {
    await browser.close();
}

/**
 * Detect WebMCP tool exposure in a live page session.
 *
 * NOTE: the WebMCP spec is still emerging, so both the imperative hook
 * (`navigator.modelContext.registerTool`) and the declarative selectors below
 * are best-effort and intentionally defensive — update them as the standard
 * settles. The presence of a `webmcp` key in the returned payload is what the
 * PHP side uses to distinguish "measured" from "not measured".
 *
 * @returns {Promise<{ webmcp: object }>}
 */
async function probeWebMcp(browser, targetUrl) {
    const probeContext = await browser.newContext();
    const probePage = await probeContext.newPage();

    // Installed before any page script runs and re-installed on every
    // navigation, so it captures imperative tool registrations as they happen.
    await probePage.addInitScript(() => {
        window.__vitalsWebMCP = { imperativeTools: [] };
        const install = () => {
            try {
                const mc = navigator.modelContext;
                if (mc && typeof mc.registerTool === 'function' && !mc.__vitalsWrapped) {
                    const orig = mc.registerTool.bind(mc);
                    mc.registerTool = (def, ...rest) => {
                        try {
                            window.__vitalsWebMCP.imperativeTools.push({
                                name: String((def && def.name) || ''),
                                hasSchema: !!(def && (def.inputSchema || def.parameters || def.schema)),
                            });
                        } catch (_) { /* ignore */ }
                        return orig(def, ...rest);
                    };
                    mc.__vitalsWrapped = true;
                }
            } catch (_) { /* ignore */ }
        };
        install();
        document.addEventListener('DOMContentLoaded', install);
    });

    try {
        await probePage.goto(targetUrl, { waitUntil: 'load', timeout: 30000 });
        // Give imperative tools that register post-load a brief window.
        await probePage.waitForTimeout(750);

        const webmcp = await probePage.evaluate(() => {
            const data = window.__vitalsWebMCP || { imperativeTools: [] };
            const imperative = data.imperativeTools || [];

            const declEls = Array.from(
                document.querySelectorAll('[data-webmcp], [webmcp], [data-mcp-tool], tool[name]'),
            );
            const forms = Array.from(document.querySelectorAll('form'));
            const annotatedForms = forms.filter((f) =>
                f.hasAttribute('webmcp') ||
                f.hasAttribute('data-webmcp') ||
                f.hasAttribute('data-mcp-tool') ||
                !!f.querySelector('[webmcp], [data-webmcp]'),
            );

            return {
                imperativeTools: imperative.length,
                declarativeTools: declEls.length,
                toolNames: imperative.map((t) => t.name).filter(Boolean).slice(0, 20),
                formsTotal: forms.length,
                formsAnnotated: annotatedForms.length,
                schemaValid: imperative.every((t) => t.hasSchema),
            };
        });

        return { webmcp };
    } finally {
        await probeContext.close();
    }
}
