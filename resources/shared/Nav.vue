<template>
    <nav>
          <ul class="flex items-center gap-6 list-none">
            <li>
                <NavLink href="/" :active="$page.component === 'Home'">
                    Home
                </NavLink>
            </li>

            <li>
                <NavLink href="/cuadros" :active="$page.component === 'Paintings'">
                    Cuadros
                </NavLink>
            </li>

            <li>
                <NavLink href="/users" :active="$page.component === 'Users'">
                    User
                </NavLink>
            </li>

            <li>
                <NavLink href="/setting" :active="$page.component === 'Setting'">
                    Setting
                </NavLink>
            </li>
              <li>
        <NavLink href="/logout" method="post" as="button">
          Log Out
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