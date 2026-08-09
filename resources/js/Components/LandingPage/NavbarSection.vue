<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    canLogin: {
        type: Boolean,
        default: false,
    },
    lang: {
        type: String,
        default: "en",
    },
    isMenuOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["set-lang", "toggle-menu"]);
</script>

<template>
    <nav
        class="sticky top-0 z-999 flex h-16 items-center justify-between bg-sidebar px-5 shadow-lg md:px-10"
    >
        <div class="flex items-center gap-3 text-lg font-bold text-white">
            <div class="flex items-center gap-1.5 p-1 rounded-md">
                <img
                    src="/assets/logo/logo-pipmarsi.png"
                    alt="PIP MARSI"
                    class="h-6 w-auto object-contain"
                />
                <img
                    src="/assets/logo/logo-umsura.png"
                    alt="UMSURA"
                    class="h-6 w-auto object-contain"
                />
            </div>
            <span>ICHA 2026</span>
        </div>

        <ul class="hidden list-none gap-7 lg:flex">
            <li v-for="link in props.links" :key="link.href">
                <a
                    :href="link.href"
                    class="text-sm font-medium text-white/80 transition-colors hover:text-gold"
                >
                    {{ link.label }}
                </a>
            </li>
        </ul>

        <div class="flex items-center gap-3">
            <template v-if="props.canLogin">
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('dashboard')"
                    class="hidden text-sm font-medium text-white/80 transition-colors hover:text-gold lg:inline-block"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="hidden text-sm font-medium text-white/80 transition-colors hover:text-gold lg:inline-block"
                >
                    Login
                </Link>
            </template>

            <div class="ml-5 flex">
                <button
                    class="rounded-l border border-gold px-3 py-1 text-xs font-semibold transition-all"
                    :class="
                        props.lang === 'en'
                            ? 'bg-gold text-sidebar'
                            : 'bg-white/10 text-white/70'
                    "
                    @click="emit('set-lang', 'en')"
                >
                    EN
                </button>
                <button
                    class="rounded-r border border-white/25 px-3 py-1 text-xs font-semibold transition-all"
                    :class="
                        props.lang === 'id'
                            ? 'bg-gold text-sidebar'
                            : 'bg-white/10 text-white/70'
                    "
                    @click="emit('set-lang', 'id')"
                >
                    ID
                </button>
            </div>

            <a
                href="#abstract"
                class="hidden rounded-md bg-gold px-5 py-2 text-sm font-bold text-sidebar transition-colors hover:bg-gold-dark lg:inline-block"
            >
                Submit Abstract
            </a>

            <button
                class="flex flex-col gap-1.25 lg:hidden"
                @click="emit('toggle-menu')"
            >
                <span class="block h-0.5 w-6 bg-white"></span>
                <span class="block h-0.5 w-6 bg-white"></span>
                <span class="block h-0.5 w-6 bg-white"></span>
            </button>
        </div>
    </nav>

    <div
        v-if="props.isMenuOpen"
        class="bg-sidebar px-5 py-4 shadow-md lg:hidden"
    >
        <ul class="flex flex-col gap-4">
            <li v-for="link in props.links" :key="link.href">
                <a
                    :href="link.href"
                    class="block text-sm font-medium text-white/80 transition-colors hover:text-gold"
                >
                    {{ link.label }}
                </a>
            </li>
        </ul>
    </div>
</template>
