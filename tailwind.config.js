/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './vendor/livewire/flux/src/**/*.php',
    ],
    safelist: [
        { pattern: /^(bg|text|border|ring)-(rose|emerald|amber|sky|zinc)-(100|200|300|400|500|600|700|800|900)$/ },
        { pattern: /^bg-(rose|emerald|amber|sky)-(100|900)\/(30|10|20)$/ },
    ],
    darkMode: 'class',
    theme: { extend: {} },
    plugins: [],
};
