<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";

const props = defineProps({
    conference: {
        type: Object,
        default: null,
    },
    availableConferences: {
        type: Array,
        default: () => [],
    },
    links: {
        type: Array,
        default: () => [],
    },
    canLogin: {
        type: Boolean,
        default: false,
    },
    isMenuOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["toggle-menu", "close-menu"]);

const activeSection = ref("");
let isTicking = false;

const handleScroll = () => {
    if (!isTicking) {
        window.requestAnimationFrame(() => {
            const scrollPos = window.scrollY + 120;
            const linkIds = props.links
                .map((link) => link.href ? link.href.split('#')[1] : null)
                .filter(Boolean);

            let current = "";
            for (const id of linkIds) {
                const el = document.getElementById(id);
                if (el) {
                    const top = el.offsetTop;
                    const height = el.offsetHeight;
                    if (scrollPos >= top && scrollPos < top + height) {
                        current = id;
                        break;
                    } else if (scrollPos >= top) {
                        current = id;
                    }
                }
            }
            if (current) {
                activeSection.value = current;
            }
            isTicking = false;
        });
        isTicking = true;
    }
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll, { passive: true });
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});

const scrollToSection = (e, href) => {
    if (href && (href.startsWith('#') || href.startsWith('/#'))) {
        const targetId = href.split('#')[1];
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            e.preventDefault();
            targetElement.scrollIntoView({ behavior: 'smooth' });
            emit('close-menu');
        }
    }
};

const isHashLink = (href) => {
    return href && (href.startsWith('#') || href.startsWith('/#'));
};

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <nav class="fixed top-0 left-0 right-0 z-50 flex h-16 items-center justify-between bg-sidebar/95 backdrop-blur-md px-5 shadow-lg md:px-10 border-b border-purple-900/40">
        <!-- Logo + Title as a unified click-to-home Link -->
        <Link href="/" class="flex items-center gap-2.5 sm:gap-3 text-white transition hover:opacity-95 group">
            <div class="flex items-center gap-2 sm:gap-2.5 p-1 rounded-lg">
                <!-- Main ICHA Official Logo -->
                <img
                    src="/assets/logo/logo-icha.png"
                    alt="ICHA 10th Logo"
                    class="h-9 sm:h-10 md:h-11 w-auto object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-300"
                />
            </div>
            <span class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-white group-hover:text-gold transition-colors">
                {{ props.conference?.title || 'ICHA 2026' }}
            </span>
        </Link>

        <!-- Navigation Links (Desktop) -->
        <ul class="hidden list-none items-center gap-7 lg:flex h-full">
            <li v-for="link in props.links" :key="link.label" class="h-full flex items-center">
                <!-- Dropdown Menu Item -->
                <div v-if="link.isDropdown" class="group relative flex h-full items-center cursor-pointer">
                    <span class="text-sm font-medium transition-colors hover:text-gold flex items-center gap-1 text-white/80 group-hover:text-gold py-5">
                        {{ link.label }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <div class="absolute left-0 top-[100%] mt-0 hidden min-w-[220px] flex-col rounded-lg bg-white py-2 shadow-xl group-hover:flex border border-slate-100 z-50">
                        <template v-if="props.availableConferences && props.availableConferences.length > 0">
                            <Link
                                v-for="conf in props.availableConferences"
                                :key="conf.id"
                                :href="conf.slug ? route('conferences.show', conf.slug) : route('conferences.index')"
                                class="px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-primary"
                                :class="{'bg-primary/5 text-primary font-bold': props.conference?.id === conf.id}"
                            >
                                {{ conf.title }}
                            </Link>
                        </template>
                        <Link
                            v-else-if="props.conference"
                            :href="props.conference.slug ? route('conferences.show', props.conference.slug) : route('home')"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-primary bg-primary/5 text-primary font-bold"
                        >
                            {{ props.conference.title || 'ICHA 2026' }}
                        </Link>
                        <Link
                            :href="route('conferences.index')"
                            class="border-t border-slate-100 px-4 py-2.5 mt-1 text-xs font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-primary"
                        >
                            View All Editions &rarr;
                        </Link>
                    </div>
                </div>

                <!-- Regular Hash Link -->
                <a
                    v-else-if="isHashLink(link.href)"
                    :href="link.href"
                    @click="scrollToSection($event, link.href)"
                    class="text-sm font-medium transition-colors hover:text-gold py-5 flex items-center cursor-pointer"
                    :class="activeSection === link.href.split('#')[1] ? 'text-gold' : 'text-white/80'"
                >
                    {{ link.label }}
                </a>
                <Link
                    v-else
                    :href="link.href"
                    class="text-sm font-medium text-white/80 transition-colors hover:text-gold py-5 flex items-center"
                >
                    {{ link.label }}
                </Link>
            </li>
        </ul>

        <!-- Right Side: Auth buttons & Language -->
        <div class="flex items-center gap-3">
            <template v-if="props.canLogin">
                <!-- Logged-in user: show avatar + name + dropdown -->
                <div v-if="$page.props.auth?.user" class="group relative hidden lg:flex items-center gap-2">
                    <button class="flex items-center gap-2 cursor-pointer py-5">
                        <div class="w-8 h-8 rounded-full bg-white/20 border border-white/30 flex items-center justify-center text-white text-xs font-black">
                            {{ $page.props.auth.user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-sm font-semibold text-white/90 group-hover:text-gold transition-colors">
                            {{ $page.props.auth.user.name }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white/60 transition-transform group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- Dropdown: hidden by default, shown on group-hover -->
                    <div class="absolute right-0 top-[100%] hidden min-w-[180px] flex-col rounded-xl bg-white shadow-xl border border-slate-100 py-1.5 group-hover:flex z-50">
                        <Link :href="route('dashboard')" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1">dashboard</span>
                            Dashboard
                        </Link>
                        <div class="border-t border-slate-100 my-1"></div>
                        <button @click="logout" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1">logout</span>
                            Logout
                        </button>
                    </div>
                </div>
                <Link
                    v-else
                    :href="route('login')"
                    class="hidden text-sm font-medium text-white/80 transition-colors hover:text-gold lg:inline-block"
                >
                    Login
                </Link>
            </template>

            <!-- Mobile Hamburger Toggle -->
            <button
                class="flex flex-col justify-center items-center w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 transition lg:hidden cursor-pointer gap-1.25"
                @click="emit('toggle-menu')"
                :aria-label="props.isMenuOpen ? 'Close navigation menu' : 'Open navigation menu'"
            >
                <span :class="['block h-0.5 w-5 bg-white transition-all duration-300', props.isMenuOpen ? 'rotate-45 translate-y-1.75' : '']"></span>
                <span :class="['block h-0.5 w-5 bg-white transition-all duration-300', props.isMenuOpen ? 'opacity-0' : '']"></span>
                <span :class="['block h-0.5 w-5 bg-white transition-all duration-300', props.isMenuOpen ? '-rotate-45 -translate-y-1.75' : '']"></span>
            </button>
        </div>
    </nav>

    <!-- Mobile Navigation Drawer Overlay (Fixed right below Navbar) -->
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div v-if="props.isMenuOpen" class="fixed top-16 inset-x-0 bottom-0 z-40 lg:hidden flex flex-col">
            <!-- Backdrop: Click to close -->
            <div class="fixed inset-0 top-16 bg-slate-950/70 backdrop-blur-xs" @click="emit('close-menu')"></div>

            <!-- Drawer Content Panel -->
            <div class="relative bg-sidebar border-t border-purple-800/60 px-6 py-6 shadow-2xl max-h-[calc(100vh-4rem)] overflow-y-auto z-10">
                <ul class="flex flex-col gap-4">
                    <li v-for="link in props.links" :key="link.label">
                        <div v-if="link.isDropdown" class="flex flex-col gap-2">
                            <span class="block text-sm font-medium text-white/50 uppercase tracking-wider text-xs">
                                {{ link.label }}
                            </span>
                            <template v-if="props.availableConferences && props.availableConferences.length > 0">
                                <Link
                                    v-for="conf in props.availableConferences"
                                    :key="conf.id"
                                    :href="conf.slug ? route('conferences.show', conf.slug) : route('conferences.index')"
                                    @click="emit('close-menu')"
                                    class="block text-sm font-medium pl-3 border-l border-white/20 transition-colors hover:text-gold"
                                    :class="props.conference?.id === conf.id ? 'text-gold border-gold' : 'text-white/80'"
                                >
                                    {{ conf.title }}
                                </Link>
                            </template>
                            <Link
                                v-else-if="props.conference"
                                :href="props.conference.slug ? route('conferences.show', props.conference.slug) : route('home')"
                                @click="emit('close-menu')"
                                class="block text-sm font-medium pl-3 border-l border-gold text-gold"
                            >
                                {{ props.conference.title || 'ICHA 2026' }}
                            </Link>
                            <Link
                                :href="route('conferences.index')"
                                @click="emit('close-menu')"
                                class="block text-xs font-semibold pl-3 border-l border-white/10 text-white/50 hover:text-gold"
                            >
                                View All Editions &rarr;
                            </Link>
                        </div>
                        <a
                            v-else-if="isHashLink(link.href)"
                            :href="link.href"
                            @click="scrollToSection($event, link.href)"
                            class="block text-sm font-medium transition-colors hover:text-gold"
                            :class="activeSection === link.href.split('#')[1] ? 'text-gold' : 'text-white/80'"
                        >
                            {{ link.label }}
                        </a>
                        <Link
                            v-else
                            :href="link.href"
                            @click="emit('close-menu')"
                            class="block text-sm font-medium text-white/80 transition-colors hover:text-gold"
                        >
                            {{ link.label }}
                        </Link>
                    </li>
                    <li class="pt-4 border-t border-white/10 flex flex-col gap-2.5">
                        <template v-if="$page.props.auth?.user">
                            <div class="px-2 py-1 text-xs text-gold font-bold flex items-center gap-2">
                                <span>👤 {{ $page.props.auth.user.name }}</span>
                            </div>
                            <Link
                                :href="route('dashboard')"
                                class="text-sm font-semibold text-white hover:text-gold flex items-center gap-2 px-2 py-1.5 rounded-xl bg-white/10"
                                @click="emit('close-menu')"
                            >
                                <span class="material-symbols-outlined text-[18px]">dashboard</span>
                                Dashboard
                            </Link>
                            <button
                                @click="logout"
                                class="text-sm font-semibold text-rose-300 hover:text-rose-400 flex items-center gap-2 px-2 py-1.5 cursor-pointer text-left"
                            >
                                <span class="material-symbols-outlined text-[18px]">logout</span>
                                Logout
                            </button>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-sm font-medium text-white/80 hover:text-gold px-2 py-1.5"
                                @click="emit('close-menu')"
                            >
                                Login
                            </Link>
                        </template>
                    </li>
                </ul>
            </div>
        </div>
    </transition>
</template>
