import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Menambahkan warna primer untuk tombol dan highlight
                primary: {
                    50: "#eff6ff",
                    100: "#dbeafe",
                    200: "#bfdbfe",
                    300: "#93c5fd",
                    400: "#60a5fa",
                    500: "#3b82f6",
                    600: "#2563eb",
                    700: "#0f11a0",
                    800: "#1e40af",
                    900: "#1e3a8a",
                    950: "#172554",
                },
                backdropBlur: {
                    xl: "20px",
                },
                // Menambahkan warna semantik untuk status
                status: {
                    success: {
                        bg: "#dcfce7", // green-100
                        text: "#166534", // green-800
                    },
                    warning: {
                        bg: "#fef9c3", // yellow-100
                        text: "#854d0e", // yellow-800
                    },
                    danger: {
                        bg: "#fee2e2", // red-100
                        text: "#991b1b", // red-800
                    },
                },
            },
        },
    },

    plugins: [forms],
};
