/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    prefix: 'tw-',
    corePlugins: {
        preflight: true,
    },
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f5f7fa',
                    100: '#e8edf3',
                    200: '#cfdbe8',
                    300: '#a9c0d8',
                    400: '#7f9fbe',
                    500: '#5f84a8',
                    600: '#4b6c90',
                    700: '#3f5a78',
                    800: '#354a62',
                    900: '#2d3f53',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(15, 23, 42, 0.08), 0 10px 24px rgba(15, 23, 42, 0.08)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
        require('@tailwindcss/forms'),
    ],
};
