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
                // Display/Heading — karakter kuat, cocok untuk lembaga keuangan
                display: [
                    '"Plus Jakarta Sans"',
                    ...defaultTheme.fontFamily.sans,
                ],
                // Body/UI — optimal untuk data-heavy interface
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
                // Monospace — untuk NIK, angka, kode
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },

            colors: {
                // ── NAVY (Sidebar & brand utama) ────────────────────────────
                navy: {
                    950: "#0B1426", // Sidebar background
                    900: "#0F1D35", // Sidebar header area
                    800: "#162544", // Sidebar item hover
                    700: "#1E3055", // Border aksen
                    600: "#264573", // Focus ring, secondary accent
                    500: "#2E5590",
                    400: "#4A72B0",
                },

                // ── GOLD (Active indicator — signature element) ──────────────
                gold: {
                    600: "#B8860B",
                    500: "#D4A017", // Active menu left-border & icon
                    400: "#E5B729", // Hover gold
                    300: "#F0CE5A",
                    100: "#FEF3C7", // Light gold bg
                },

                // ── PRIMARY BLUE (Action utama — button, link) ───────────────
                // Diperbaiki: primary-700 sebelumnya #0f11a0 (terlalu gelap/inkonsisten)
                primary: {
                    50: "#EFF6FF",
                    100: "#DBEAFE",
                    200: "#BFDBFE",
                    300: "#93C5FD",
                    400: "#60A5FA",
                    500: "#3B82F6",
                    600: "#2563EB", // ← Main primary action
                    700: "#1D4ED8", // ← Hover state
                    800: "#1E40AF",
                    900: "#1E3A8A",
                    950: "#172554",
                },

                // ── DARK MODE NEUTRALS ───────────────────────────────────────
                dark: {
                    900: "#0F172A", // Page background dark
                    800: "#1E293B", // Card background dark
                    700: "#334155", // Border dark
                    600: "#475569", // Muted text dark
                    500: "#64748B",
                },

                // ── STATUS COLORS (semantic) ─────────────────────────────────
                // Tetap menggunakan Tailwind built-in (green, red, yellow, blue, purple)
                // tapi kita definisikan alias untuk konsistensi
                status: {
                    pending: {
                        bg: "#FEF3C7",
                        text: "#92400E",
                        dark_bg: "rgba(251,191,36,0.15)",
                        dark_text: "#FCD34D",
                    },
                    approved: {
                        bg: "#DCFCE7",
                        text: "#14532D",
                        dark_bg: "rgba(34,197,94,0.15)",
                        dark_text: "#86EFAC",
                    },
                    rejected: {
                        bg: "#FEE2E2",
                        text: "#7F1D1D",
                        dark_bg: "rgba(239,68,68,0.15)",
                        dark_text: "#FCA5A5",
                    },
                    revision: {
                        bg: "#FEF9C3",
                        text: "#713F12",
                        dark_bg: "rgba(234,179,8,0.15)",
                        dark_text: "#FDE047",
                    },
                    info: {
                        bg: "#E0F2FE",
                        text: "#0C4A6E",
                        dark_bg: "rgba(14,165,233,0.15)",
                        dark_text: "#7DD3FC",
                    },
                },
            },

            // ── SHADOW SYSTEM ────────────────────────────────────────────────
            boxShadow: {
                // Override default agar konsisten dengan design system
                xs: "0 1px 2px 0 rgba(0, 0, 0, 0.05)",
                sm: "0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.06)",
                DEFAULT:
                    "0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05)",
                md: "0 4px 8px -2px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.05)",
                lg: "0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.05)",
                xl: "0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05)",
                "2xl": "0 25px 50px -12px rgba(0, 0, 0, 0.18)",
                // Shadow khusus untuk sidebar
                sidebar: "4px 0 24px -4px rgba(11, 20, 38, 0.40)",
                // Shadow untuk card navy/dark
                navy: "0 4px 14px -2px rgba(11, 20, 38, 0.30)",
                none: "none",
            },

            // ── BORDER RADIUS ────────────────────────────────────────────────
            borderRadius: {
                none: "0",
                sm: "4px",
                DEFAULT: "6px",
                md: "8px", // Input, button default
                lg: "10px", // Card, panel
                xl: "12px", // Card utama
                "2xl": "16px", // Modal, auth panel
                "3xl": "24px", // Auth card dekoratif
                full: "9999px", // Badge, avatar, pill button
            },

            // ── SPACING TAMBAHAN ─────────────────────────────────────────────
            spacing: {
                18: "4.5rem", // 72px — sidebar nav padding
                68: "17rem", // 272px — sidebar content offset
                72: "18rem", // 288px — sidebar width
                76: "19rem",
                80: "20rem",
            },

            // ── TRANSITION ───────────────────────────────────────────────────
            transitionDuration: {
                150: "150ms",
                200: "200ms",
                300: "300ms",
            },

            // ── Z-INDEX ──────────────────────────────────────────────────────
            zIndex: {
                60: "60",
                70: "70",
            },
        },
    },

    plugins: [forms],
};
