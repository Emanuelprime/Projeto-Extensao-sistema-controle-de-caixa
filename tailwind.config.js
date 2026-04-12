import forms from '@tailwindcss/forms';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './routes/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                ink: '#181C20',
                muted: '#64748B',
                app: '#F7F9FF',
                panel: '#FFFFFF',
                line: '#EBEEF3',
                navy: {
                    950: '#00164E',
                    900: '#002068',
                    800: '#003399',
                },
                action: '#115CB9',
                danger: '#BA1A1A',
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['Work Sans', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                soft: '0 18px 60px rgba(15, 23, 42, 0.08)',
            },
        },
    },
    plugins: [forms],
};
