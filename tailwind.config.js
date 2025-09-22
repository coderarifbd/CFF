import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php', 
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, daisyui],

    // DaisyUI configuration
    daisyui: {
        themes: [
            'corporate', // clean, business-like
            'business',
            'light',
            'dark',
        ],
        darkTheme: 'business',
        base: true,
        styled: true,
        utils: true,
        logs: false,
        themeRoot: ':root',
    },
};
