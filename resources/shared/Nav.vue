<template>
    <nav>
          <ul class="flex items-center gap-6 list-none">
            <li>
                <NavLink href="/" :active="$page.component === 'Home'">
                    Inicio
                </NavLink>
            </li>

            <li>
                <NavLink href="/cuadros" :active="$page.component.startsWith('Paintings')">
                    Cuadros
                </NavLink>
            </li>

            <li>
                <NavLink href="/publicaciones" :active="$page.component.startsWith('Publicaciones')">
                    Publicaciones
                </NavLink>
            </li>

            <li>
                <NavLink href="/users/list" :active="$page.component.startsWith('Users')">
                    Usuarios
                </NavLink>
            </li>

            <li>
                <NavLink href="/inquiries" :active="$page.component === 'Inquiries/Index'">
                    Solicitudes
                </NavLink>
            </li>

            <li>
                <NavLink href="/settings" :active="$page.component === 'Settings'">
                    Ajustes
                </NavLink>
            </li>
              <li>
        <NavLink href="/logout" method="post" as="button">
          Cerrar sesión
        </NavLink>
      </li>
        </ul>
    </nav>
</template>

<script>
import NavLink from './NavLink.vue';

export default {
    components: {
        NavLink,
    },
};
</script>