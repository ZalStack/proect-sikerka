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
                // SEMUA font di aplikasi ini memakai Poppins
                'sans': ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
                'poppins': ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                'montserrat': ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                'inter': ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                'display': ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                'mono': ['Poppins', 'ui-monospace', 'monospace'],
                'serif': ['Poppins', 'ui-serif', 'Georgia', 'serif'],
            },
            animation: {
                'float': 'float 8s ease-in-out infinite',
                'float-delayed': 'float-delayed 10s ease-in-out infinite',
                'float-slow': 'float-slow 14s ease-in-out infinite',
                'pulse-ring': 'pulse-ring 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'gradient-shift': 'gradient-shift 6s ease infinite',
                'slide-up': 'slide-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'slide-right': 'slide-right 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'fade-in': 'fade-in 0.9s ease-out forwards',
                'scale-in': 'scale-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'blob': 'blob 20s linear infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                    '25%': { transform: 'translateY(-25px) rotate(2deg)' },
                    '50%': { transform: 'translateY(-15px) rotate(-1deg)' },
                    '75%': { transform: 'translateY(-30px) rotate(1deg)' },
                },
                'float-delayed': {
                    '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                    '25%': { transform: 'translateY(-20px) rotate(-2deg)' },
                    '50%': { transform: 'translateY(-30px) rotate(1deg)' },
                    '75%': { transform: 'translateY(-10px) rotate(-1deg)' },
                },
                'float-slow': {
                    '0%, 100%': { transform: 'translateY(0px) scale(1)' },
                    '50%': { transform: 'translateY(-20px) scale(1.03)' },
                },
                'pulse-ring': {
                    '0%': { transform: 'scale(0.8)', opacity: '0.7' },
                    '100%': { transform: 'scale(1.5)', opacity: '0' },
                },
                'gradient-shift': {
                    '0%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                    '100%': { backgroundPosition: '0% 50%' },
                },
                'slide-up': {
                    'from': { opacity: '0', transform: 'translateY(40px)' },
                    'to': { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-right': {
                    'from': { opacity: '0', transform: 'translateX(-40px)' },
                    'to': { opacity: '1', transform: 'translateX(0)' },
                },
                'fade-in': {
                    'from': { opacity: '0' },
                    'to': { opacity: '1' },
                },
                'scale-in': {
                    'from': { opacity: '0', transform: 'scale(0.85)' },
                    'to': { opacity: '1', transform: 'scale(1)' },
                },
                blob: {
                    '0%, 100%': { borderRadius: '60% 40% 70% 30% / 60% 40% 70% 30%' },
                    '25%': { borderRadius: '40% 60% 30% 70% / 40% 60% 30% 70%' },
                    '50%': { borderRadius: '50% 50% 40% 60% / 50% 50% 40% 60%' },
                    '75%': { borderRadius: '45% 55% 60% 40% / 45% 55% 60% 40%' },
                },
            },
            backgroundSize: {
                '200%': '200% 200%',
            },
        },
    },
    plugins: [],
}

