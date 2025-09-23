import "../css/app.css";
import "./bootstrap";
import "./plugins/echo";
import axios from "axios";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

// === Tambahkan ini untuk ikon ===
import * as lucide from "lucide-vue-next";

// ======== DARK MODE PERSISTENCE ========
const savedTheme = localStorage.theme;
if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
} else {
    document.documentElement.classList.remove("dark");
}
// =======================================

// Optional: set header untuk AJAX Inertia requests
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";
// Ambil token dari <meta>
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error(
        'CSRF token not found: please add <meta name="csrf-token" content="{{ csrf_token() }}"> in your layout.'
    );
}

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 419) {
            // Session habis → reload page untuk dapat CSRF token baru
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // // Set CSRF header lagi untuk setiap load page SPA
        // axios.defaults.headers.common["X-CSRF-TOKEN"] = document
        //     .querySelector('meta[name="csrf-token"]')
        //     .getAttribute("content");

        // === Register semua ikon Lucide sebagai global component ===
        Object.entries(lucide).forEach(([name, component]) => {
            vueApp.component(name, component);
        });

        vueApp.mount(el);
    },
    progress: {
        color: "#d4f502",
    },
});
