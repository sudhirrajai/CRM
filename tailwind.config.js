import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                landing: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                boron: {
                    dark: '#1c1c2e',
                    darker: '#151525',
                    accent: '#22c55e',
                    'accent-hover': '#16a34a',
                    orange: '#f59e0b',
                    text: '#111827',
                    muted: '#6b7280',
                    border: '#e5e7eb',
                    card: '#ffffff',
                    'card-dark': '#1f2937',
                    bg: '#f9fafb',
                },
            },
            borderRadius: {
                'card': '12px',
            },
            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                'slide-in-left': {
                    '0%': { opacity: '0', transform: 'translateX(-30px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                'slide-in-right': {
                    '0%': { opacity: '0', transform: 'translateX(30px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                'count-up': {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'fade-in-up': 'fade-in-up 0.6s ease-out forwards',
                'fade-in': 'fade-in 0.6s ease-out forwards',
                'slide-in-left': 'slide-in-left 0.6s ease-out forwards',
                'slide-in-right': 'slide-in-right 0.6s ease-out forwards',
            },
        },
    },

    corePlugins: {
        preflight: false,
    },

    plugins: [],
};
