/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // All Blade views in this package
        './resources/**/*.blade.php',
        // PHP files that may contain class strings (Livewire components, view providers, etc.)
        './resources/**/*.php',
        './src/**/*.php',
        // JS / Alpine bindings
        './resources/js/**/*.js',
        './resources/css/**/*.css',
        // Flux Free stubs and source — needed for Flux components to be styled
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './vendor/livewire/flux/src/**/*.php',
        // Translation files may carry class names too in rare cases
        './lang/**/*.php',
    ],
    // NOTE: Tailwind 3's `safelist` with `pattern + variants` was not generating
    // `dark:border-ink-800/60` etc. reliably for some color/opacity combinations.
    // We've moved to explicit class strings for the variants we depend on.
    safelist: [
        // Base utilities (no variant) — generated unconditionally
        {
            pattern: /^(bg|text|border|ring|divide|from|to|via)-(emerald|amber|accent|ink|sky|violet)-(50|100|200|300|400|500|600|700|800|900|950)$/,
        },
        {
            pattern: /^(bg|border|text|ring)-(emerald|amber|accent|ink|sky|violet)-(50|100|200|300|400|500|600|700|800|900|950)\/(10|20|30|40|50|60|70|80|90)$/,
        },
        { pattern: /^(bg|text|border)-(paper|canvas)$/ },
        // Explicit dark variants for the borders / backgrounds the views actually use
        // (these are what Tailwind's content scanner sometimes misses for dynamic class strings)
        ...[
            'dark:border-ink-200', 'dark:border-ink-700', 'dark:border-ink-800', 'dark:border-ink-900',
            'dark:bg-ink-50', 'dark:bg-ink-100', 'dark:bg-ink-200', 'dark:bg-ink-800', 'dark:bg-ink-900', 'dark:bg-ink-950',
            'dark:text-ink-100', 'dark:text-ink-200', 'dark:text-ink-300', 'dark:text-ink-400', 'dark:text-ink-500',
            'dark:bg-paper', 'dark:bg-canvas',
        ],
        // Explicit dark + opacity variants used in views (fragile combo with content scanner)
        ...[10, 20, 30, 40, 50, 60, 70, 80, 90].flatMap(op =>
            [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].flatMap(shade =>
                ['ink', 'accent', 'emerald', 'amber', 'sky', 'violet'].flatMap(color => [
                    `dark:bg-${color}-${shade}/${op}`,
                    `dark:border-${color}-${shade}/${op}`,
                    `dark:text-${color}-${shade}/${op}`,
                    `hover:bg-${color}-${shade}/${op}`,
                    `dark:hover:bg-${color}-${shade}/${op}`,
                ])
            )
        ),
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // OKLCH colors with <alpha-value> placeholder — required for Tailwind's
                // opacity modifier (/60 etc.) to work on custom-defined colors.
                paper: 'oklch(99.2% 0.003 17 / <alpha-value>)',
                canvas: 'oklch(97.8% 0.005 17 / <alpha-value>)',
                // Tinted-neutral scale — replaces zinc entirely (rose tint at low chroma)
                ink: {
                    50:  'oklch(98% 0.003 17 / <alpha-value>)',
                    100: 'oklch(95% 0.005 17 / <alpha-value>)',
                    200: 'oklch(90% 0.007 17 / <alpha-value>)',
                    300: 'oklch(80% 0.008 17 / <alpha-value>)',
                    400: 'oklch(65% 0.010 17 / <alpha-value>)',
                    500: 'oklch(52% 0.012 17 / <alpha-value>)',
                    600: 'oklch(42% 0.012 17 / <alpha-value>)',
                    700: 'oklch(35% 0.014 17 / <alpha-value>)',
                    800: 'oklch(26% 0.012 17 / <alpha-value>)',
                    900: 'oklch(18% 0.010 17 / <alpha-value>)',
                    950: 'oklch(13% 0.008 17 / <alpha-value>)',
                },
                // Rose accent scale — primary brand color
                accent: {
                    50:  'oklch(97% 0.018 12 / <alpha-value>)',
                    100: 'oklch(94% 0.040 12 / <alpha-value>)',
                    200: 'oklch(88% 0.075 12 / <alpha-value>)',
                    300: 'oklch(80% 0.120 12 / <alpha-value>)',
                    400: 'oklch(72% 0.180 12 / <alpha-value>)',
                    500: 'oklch(64% 0.220 12 / <alpha-value>)',
                    600: 'oklch(57% 0.240 12 / <alpha-value>)',
                    700: 'oklch(50% 0.220 12 / <alpha-value>)',
                    800: 'oklch(40% 0.180 12 / <alpha-value>)',
                    900: 'oklch(30% 0.150 12 / <alpha-value>)',
                    950: 'oklch(20% 0.100 12 / <alpha-value>)',
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
