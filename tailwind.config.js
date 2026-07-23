import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'animate-fall-down',
        'bg-purple-500/10', 'border-purple-500/20', 'text-purple-400',
        'bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-400',
        'bg-orange-500/10', 'border-orange-500/20', 'text-orange-400',
        'group-hover:bg-cyan-500', 'group-hover:border-cyan-500',
        'group-hover:bg-purple-500', 'group-hover:border-purple-500',
        'group-hover:bg-emerald-500', 'group-hover:border-emerald-500',
        'group-hover:bg-orange-500', 'group-hover:border-orange-500',
        'group-hover:text-white',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'fall-down': 'fall-down 2s ease-out forwards',
            },
            keyframes: {
                'fall-down': {
                    '0%': {
                        transform: 'translateY(-100vh)',
                        opacity: '0'
                    },
                    '100%': {
                        transform: 'translateY(0)',
                        opacity: '1'
                    },
                }
            }
        },
    },

    plugins: [forms],
};
