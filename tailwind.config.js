/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './vendor/livewire/flux/src/**/*.php',
    ],
    safelist: [
        { pattern: /^(bg|text|border|ring)-(rose|emerald|amber|sky|violet|pink|zinc)-(50|100|200|300|400|500|600|700|800|900)$/ },
        { pattern: /^(bg|border)-(rose|emerald|amber|sky|violet|pink|zinc)-(50|100|200|400|500|900)\/(\d{1,2})$/ },
    ],
    darkMode: 'class',
    theme: { extend: {} },
    plugins: [],
};
