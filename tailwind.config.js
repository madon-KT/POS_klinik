import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    safelist: [
        // background & gradient
        'bg-white',
        'bg-gray-50',
        'bg-blue-400',
        'bg-indigo-600',
        'bg-gradient-to-br',
        'from-blue-500',
        'to-indigo-600',

        // text
        'text-gray-900',
        'text-gray-700',
        'text-gray-600',
        'text-gray-500',
        'text-indigo-600',
        'text-white',

        // border & shadow
        'border-gray-300',
        'rounded-lg',
        'rounded-xl',
        'shadow',
        'shadow-md',
        'shadow-2xl',

        // focus & ring (INI PALING SERING HILANG)
        'focus:ring-indigo-500',
        'focus:border-indigo-500',

        // layout
        'min-h-screen',
        'flex',
        'items-center',
        'justify-center',
        'w-full',
        'max-w-md',
        'p-10',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
