import { ref } from "vue";

/**
 * Caché local del tema por usuario. La preferencia real se guarda en la
 * base de datos; localStorage evita el parpadeo inicial y respalda a invitados.
 */
export function themeKey(userId) {
    return userId ? `theme:user:${userId}` : "theme:guest";
}

export const isDark = ref(
    typeof document !== "undefined" &&
        document.documentElement.classList.contains("dark"),
);

export function getTheme(userId) {
    try {
        const key = themeKey(userId);
        let theme = localStorage.getItem(key);

        // Compatibilidad con la clave global de versiones anteriores.
        const legacyTheme = localStorage.getItem("theme");

        if (!theme && legacyTheme) {
            theme = legacyTheme;
            localStorage.setItem(key, legacyTheme);
            localStorage.removeItem("theme");
        }

        return theme === "dark" ? "dark" : "light";
    } catch {
        return "light";
    }
}

/**
 * La base de datos es la fuente principal; localStorage solo evita el
 * parpadeo inicial y sirve de respaldo si el usuario aún no tiene tema.
 */
export function resolveTheme(serverTheme, userId) {
    if (serverTheme === "dark" || serverTheme === "light") {
        saveTheme(userId, serverTheme);
        return serverTheme;
    }

    return getTheme(userId);
}

export function saveTheme(userId, theme) {
    try {
        localStorage.setItem(themeKey(userId), theme);
    } catch {
        // Si el navegador bloquea localStorage, el tema se mantiene en la sesión actual.
    }
}

export function applyTheme(theme) {
    const dark = theme === "dark";

    isDark.value = dark;

    if (typeof document !== "undefined") {
        document.documentElement.classList.toggle("dark", dark);
    }
}
