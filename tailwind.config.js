/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                'deep-twilight': '#161758',
                'french-blue': '#27438D',
                'blue-bell': '#00a2e9',
                'school-bus-yellow': '#FCC626',
                'racing-red': '#ec1d1d',
                'laurel-green': '#2E7D3E',
                'fresh-green': '#009a4b',
                'off-white': '#F5F5F5',
            },
            fontFamily: {
                'poppins': ['Poppins', 'sans-serif'],
                'inter': ['Inter', 'sans-serif'],
                'montserrat': ['Montserrat', 'sans-serif'],
            },
        },
    },
    plugins: [],
}

