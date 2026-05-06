/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './vendor/livewire/flux/src/**/*.php',
    ],
    darkMode: 'class',
    theme: { extend: {} },
    plugins: [],
};
