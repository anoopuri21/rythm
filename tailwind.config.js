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
                    light: '#555555',
                    DEFAULT: '#111111',
                    dark: '#000000',
                },
                rythme: {
                    black: '#111111',
                    'black-soft': '#1A1A1A',
                    'black-muted': '#2D2D2D',
                    red: '#111111',
                    'red-dark': '#000000',
                    'red-light': '#555555',
                    cream: '#ffffff',
                    'cream-dark': '#f6f6f6',
                    'warm-white': '#ffffff',
                    'warm-gray': '#6B6B6B',
                },
            },
            fontFamily: {
                playfair: ['Inter', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', ...defaultTheme.fontFamily.sans],
                bebas: ['Inter', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
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
