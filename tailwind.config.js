import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import preset from "./vendor/filament/support/tailwind.config.preset";
import fs from "fs";
import path from "path";

const themeFilePath = path.resolve(__dirname, "theme.json");
const activeTheme = fs.existsSync(themeFilePath)
  ? JSON.parse(fs.readFileSync(themeFilePath, "utf8")).name
  : "anchor";

/** @type {import('tailwindcss').Config} */
export default {
  presets: [preset],
  content: [
    "./app/Filament/**/*.php",
    "./resources/views/filament/**/*.blade.php",
    "./vendor/filament/**/*.blade.php",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/views/components/**/*.blade.php",
    "./resources/views/components/blade.php",
    "./wave/resources/views/**/*.blade.php",
    "./resources/themes/" + activeTheme + "/**/*.blade.php",
    "./resources/plugins/**/*.php",
    "./config/*.php",
  ],

  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#df1c24',
          container: '#fee2e2',
          fixed: '#fff5f5',
          'fixed-dim': '#fecaca',
        },
        secondary: {
          DEFAULT: '#5f6368',
          container: '#e5e7eb',
          fixed: '#edf2f7',
          'fixed-dim': '#cbd5e1',
        },
        tertiary: {
          DEFAULT: '#0f766e',
          container: '#ccfbf1',
          fixed: '#f0fdfa',
          'fixed-dim': '#99f6e4',
        },
        'brand-navy': '#0f172a',
        'brand-navy-deep': '#020617',
        'brand-navy-mid': '#1e293b',
        'link-blue': '#006ADC',
        'card-tint-peach': '#fff7ed',
        'card-tint-rose': '#fdf2f8',
        'card-tint-mint': '#ecfdf5',
        'card-tint-lavender': '#eff6ff',
        'card-tint-sky': '#eef6ff',
        'card-tint-yellow': '#fefce8',
        'card-tint-yellow-bold': '#fde68a',
        'card-tint-cream': '#f8fafc',
        'ink-deep': '#020617',
        charcoal: '#1f2937',
        slate: '#64748b',
        steel: '#6b7280',
        hairline: '#e5e7eb',
        'hairline-strong': '#d1d5db',
      },
      fontFamily: {
        sans: ['Inter', 'Inter Tight', ...defaultTheme.fontFamily.sans],
      },
      animation: {
        marquee: "marquee 25s linear infinite",
      },
      keyframes: {
        marquee: {
          from: { transform: "translateX(0)" },
          to: { transform: "translateX(-100%)" },
        },
      },
      backgroundImage: {
        hero: "url('/images/bg-hero.jpg')",
      },
    },
  },

  plugins: [forms, require("@tailwindcss/typography")],
};
