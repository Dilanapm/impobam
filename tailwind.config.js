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
                canvas: 'rgb(var(--impobam-canvas) / <alpha-value>)',
                surface: 'rgb(var(--impobam-surface) / <alpha-value>)',
                muted: 'rgb(var(--impobam-muted) / <alpha-value>)',
                border: 'rgb(var(--impobam-border) / <alpha-value>)',
                'border-strong': 'rgb(var(--impobam-border-strong) / <alpha-value>)',
                foreground: 'rgb(var(--impobam-foreground) / <alpha-value>)',
                'foreground-muted': 'rgb(var(--impobam-foreground-muted) / <alpha-value>)',

                primary: {
                    DEFAULT: 'rgb(var(--impobam-primary) / <alpha-value>)',
                    hover: 'rgb(var(--impobam-primary-hover) / <alpha-value>)',
                    soft: 'rgb(var(--impobam-primary-soft) / <alpha-value>)',
                    border: 'rgb(var(--impobam-primary-border) / <alpha-value>)',
                    foreground: 'rgb(var(--impobam-primary-foreground) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--impobam-success) / <alpha-value>)',
                    hover: 'rgb(var(--impobam-success-hover) / <alpha-value>)',
                    soft: 'rgb(var(--impobam-success-soft) / <alpha-value>)',
                    border: 'rgb(var(--impobam-success-border) / <alpha-value>)',
                    foreground: 'rgb(var(--impobam-success-foreground) / <alpha-value>)',
                },
                danger: {
                    DEFAULT: 'rgb(var(--impobam-danger) / <alpha-value>)',
                    hover: 'rgb(var(--impobam-danger-hover) / <alpha-value>)',
                    soft: 'rgb(var(--impobam-danger-soft) / <alpha-value>)',
                    border: 'rgb(var(--impobam-danger-border) / <alpha-value>)',
                    foreground: 'rgb(var(--impobam-danger-foreground) / <alpha-value>)',
                },
                accent: {
                    DEFAULT: 'rgb(var(--impobam-accent) / <alpha-value>)',
                    hover: 'rgb(var(--impobam-accent-hover) / <alpha-value>)',
                    soft: 'rgb(var(--impobam-accent-soft) / <alpha-value>)',
                    border: 'rgb(var(--impobam-accent-border) / <alpha-value>)',
                    foreground: 'rgb(var(--impobam-accent-foreground) / <alpha-value>)',
                },
                warning: {
                    DEFAULT: 'rgb(var(--impobam-warning) / <alpha-value>)',
                    hover: 'rgb(var(--impobam-warning-hover) / <alpha-value>)',
                    soft: 'rgb(var(--impobam-warning-soft) / <alpha-value>)',
                    border: 'rgb(var(--impobam-warning-border) / <alpha-value>)',
                    foreground: 'rgb(var(--impobam-warning-foreground) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
