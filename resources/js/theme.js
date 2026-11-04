import { ref } from "vue";

/**
 * El tema vive en localStorage, pero con una clave distinta por usuario.
 * Así cada cuenta conserva su preferencia en el mismo equipo y un usuario
 * nuevo no hereda el tema del anterior.
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
