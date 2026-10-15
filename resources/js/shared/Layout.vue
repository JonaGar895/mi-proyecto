<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div
                class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center"
            >
                <h1 class="text-xl font-bold text-gray-800">
                    <input type="text" value="🎨 My App">
                </h1>

                <p class="text-sm ml-4 text-gray-600">
                    Welcome Back,
                    {{ username }}!
                </p>

                <Nav />
            </div>
        </header>
         <iframe
        class="mb-6"
        width="100%"
        frameborder="no"
        scrolling="no"
        seamless
        src="https://player.simplecast.com/fd0bd2ba-c553-466c-a060-b144797ce369?dark=false"
      />

        <main class="max-w-7xl mx-auto p-6">
            <slot />
        </main>
    </div>
</template>

<script>
import Nav from './Nav.vue';

export default {
    components: {
        Nav,
    },
    computed: {
        username() {
            return this.$page.props.auth.user.username;
        }
    }
};
</script>