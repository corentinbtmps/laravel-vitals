/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './vendor/livewire/flux/src/**/*.php',
    ],
    safelist: [
        // Status colors — keep emerald/amber for semantic meaning only
        { pattern: /^(bg|text|border|ring)-(emerald|amber)-(50|100|200|300|400|500|600|700|800|900|950)$/ },
        { pattern: /^(bg|border)-(emerald|amber)-(50|100|200|400|500|900|950)\/(\d{1,2})$/ },
        // ink tinted-neutral scale — used for text/bg/border throughout
        { pattern: /^(bg|text|border|ring)-ink-(50|100|200|300|400|500|600|700|800|900|950)$/ },
        { pattern: /^(bg|text|border)-ink-(100|200|800|900)\/\d{1,2}$/ },
        // accent (rose-tinted) — primary interactive color
        { pattern: /^(bg|text|border|ring)-accent-(50|100|500|600|700)$/ },
        { pattern: /^(bg|text|border)-accent-(50|100|500|600)\/\d{1,2}$/ },
        // paper / canvas surface tokens
        { pattern: /^bg-(paper|canvas)$/ },
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // Barely-rose-tinted off-white — primary page background
                paper: 'oklch(99.2% 0.003 17)',
                // Slightly cooler surface — secondary surface (card backgrounds)
                canvas: 'oklch(97.8% 0.005 17)',
                // Tinted-neutral scale — replaces zinc entirely
                // All tinted toward hue 17 (rose direction) at minimal chroma
                ink: {
                    50:  'oklch(98% 0.003 17)',
                    100: 'oklch(95% 0.005 17)',
                    200: 'oklch(90% 0.007 17)',
                    300: 'oklch(80% 0.008 17)',
                    400: 'oklch(65% 0.010 17)',
                    500: 'oklch(52% 0.012 17)',
                    600: 'oklch(42% 0.012 17)',
                    700: 'oklch(32% 0.012 17)',
                    800: 'oklch(22% 0.010 17)',
                    900: 'oklch(15% 0.008 17)',
                    950: 'oklch(10% 0.006 17)',
                },
                // Rose accent scale — primary brand color
                accent: {
                    50:  'oklch(97% 0.018 12)',
                    100: 'oklch(94% 0.040 12)',
                    500: 'oklch(64% 0.220 12)',
                    600: 'oklch(57% 0.240 12)',
                    700: 'oklch(50% 0.220 12)',
                },
            },
            fontFamily: {
                sans: ['Geist Variable', 'system-ui', '-apple-system', 'sans-serif'],
                mono: ['Geist Mono Variable', 'Geist Variable', 'ui-monospace', 'monospace'],
            },
            fontSize: {
                'xs':      ['0.75rem',  { lineHeight: '1rem' }],
                'sm':      ['0.875rem', { lineHeight: '1.25rem' }],
                'base':    ['1rem',     { lineHeight: '1.5rem' }],
                'lg':      ['1.125rem', { lineHeight: '1.75rem' }],
                'xl':      ['1.25rem',  { lineHeight: '1.75rem' }],
                '2xl':     ['1.5rem',   { lineHeight: '2rem' }],
                '3xl':     ['2rem',     { lineHeight: '2.25rem' }],
                'display': ['3rem',     { lineHeight: '1', letterSpacing: '-0.02em' }],
            },
            letterSpacing: {
                'label': '0.08em',
            },
        },
    },
    plugins: [],
};
