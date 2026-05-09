import { onLCP, onINP, onCLS, onTTFB, onFCP } from 'web-vitals/attribution';

const config = window.__VITALS_RUM__ || {};

if (!config.endpoint) {
    console.warn('[Vitals RUM] no endpoint configured, skipping');
} else {
    // Apply sample rate — if sample_rate = 0.1 only 10% of sessions send beacons
    const sampleRate = typeof config.sample_rate === 'number' ? config.sample_rate : 1.0;
    if (Math.random() > sampleRate) {
        // Silently skip this session
    } else {
        const send = (metric) => {
            const body = JSON.stringify({
                url: location.pathname,
                metric: metric.name,
                value: metric.value,
                rating: metric.rating,
                navigation_type: metric.navigationType,
                attribution: metric.attribution || null,
                device: matchMedia('(max-width: 768px)').matches ? 'mobile' : 'desktop',
                user_agent: navigator.userAgent,
                connection: navigator.connection?.effectiveType || null,
                timestamp: Date.now(),
            });

            if (navigator.sendBeacon) {
                navigator.sendBeacon(config.endpoint, new Blob([body], { type: 'application/json' }));
            } else {
                fetch(config.endpoint, {
                    method: 'POST',
                    body,
                    keepalive: true,
                    headers: { 'Content-Type': 'application/json' },
                });
            }
        };

        onLCP(send);
        onINP(send);
        onCLS(send);
        onTTFB(send);
        onFCP(send);
    }
}
