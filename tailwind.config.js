/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./vendor/jaocero/activity-timeline/resources/views/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./resources/views/vendor/invoices/templates/*.blade.php",
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
