import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/robsontenorio/mary/src/View/Components/**/*.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                gold: {
                    light: '#FF5252',
                    DEFAULT: '#d50808',
                    dark: '#a30404',
                },
                rythme: {
                    black: '#000000',
                    'black-soft': '#111111',
                    'black-muted': '#2D2D2D',
                    red: '#d50808',
                    'red-dark': '#a30404',
                    'red-light': '#FF5252',
                    cream: '#ffffff',
                    'cream-dark': '#f5f5f5',
                    'warm-white': '#ffffff',
                    'warm-gray': '#6B6B6B',
                },
            },
            fontFamily: {
                playfair: ['Poppins', ...defaultTheme.fontFamily.sans],
                inter: ['Poppins', ...defaultTheme.fontFamily.sans],
                bebas: ['Poppins', ...defaultTheme.fontFamily.sans],
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'bounce-slow': 'bounce 2s infinite',
                marquee: 'marquee 30s linear infinite',
                'fade-in': 'fadeIn 0.6s ease-out forwards',
                'slide-up': 'slideUp 0.6s ease-out forwards',
                float: 'float 6s ease-in-out infinite',
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0%)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-20px)' },
                },
            },
        },
    },
    plugins: [forms],
};
