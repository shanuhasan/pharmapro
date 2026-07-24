import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                medical: {
                    primary: '#007bff', // Blue
                    success: '#28a745', // Green
                    dark: '#343a40',    // AdminLTE Dark Sidebar
                    darker: '#23272b',  // Sidebar Header
                }
            }
        },
    },

    plugins: [forms],
};
