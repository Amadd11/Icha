<script setup>
import { Link } from "@inertiajs/vue3";
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
    lang: {
        type: String,
        default: "en",
    },
    isMenuOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["set-lang", "toggle-menu", "close-menu"]);

const activeSection = ref("");

const handleScroll = () => {
    const sections = props.links
        .map((link) => link.href ? link.href.split('#')[1] : null)
        .filter(Boolean)
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    let current = "";
    for (const section of sections) {
        const sectionTop = section.offsetTop;
        if (window.scrollY >= sectionTop - 100) {
            current = section.getAttribute("id");
        }
    }
    activeSection.value = current;
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});

const scrollToSection = (e, href) => {
    if (href && href.includes('#')) {
        const id = href.split('#')[1];
        const section = document.getElementById(id);
        if (section) {
            e.preventDefault();
            window.scrollTo({
                top: section.offsetTop - 60,
                behavior: "smooth",
            });
            history.pushState(null, null, '#' + id);
            emit('close-menu');
        }
    } else {
        emit('close-menu');
    }
};

const isHashLink = (href) => {
    return href && (href.startsWith('#') || href.startsWith('/#'));
};
</script>

<template>
    <nav class="sticky top-0 z-50 flex h-16 items-center justify-between bg-sidebar px-5 shadow-lg md:px-10">
        <div class="flex items-center gap-3 text-2xl font-bold text-white">
            <Link href="/" class="flex items-center gap-2 p-1 rounded-md">
                <img
                    v-if="props.conference?.logo"
                    :src="'/storage/' + props.conference.logo"
                    :alt="props.conference?.title || 'Conference Logo'"
                    class="h-9 w-auto object-contain"
                />
                <img
                    v-else
                    src="/assets/logo/logo-pipmarsi.png"
                    alt="PIP MARSI"
                    class="h-9 w-auto object-contain"
                />
                <img
                    v-if="!props.conference?.logo"
                    src="/assets/logo/logo-umsura.png"
                    alt="UMSURA"
                    class="h-9 w-auto object-contain"
                />
            </Link>
            <span>{{ props.conference?.title || 'ICHA 2026' }}</span>
        </div>

        <ul class="hidden list-none gap-7 lg:flex">
            <li v-for="link in props.links" :key="link.label">
                <!-- Dropdown Menu Item -->
                <div v-if="link.isDropdown" class="group relative flex items-center cursor-pointer py-5 -my-5">
                    <span class="text-sm font-medium transition-colors hover:text-gold flex items-center gap-1 text-white/80 group-hover:text-gold">
                        {{ link.label }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <div v-if="props.availableConferences.length > 0" class="absolute left-0 top-[100%] mt-0 hidden min-w-[220px] flex-col rounded-lg bg-white py-2 shadow-xl group-hover:flex border border-slate-100">
                        <Link
                            v-for="conf in props.availableConferences"
                            :key="conf.id"
                            :href="route('conferences.show', conf.slug)"
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-primary"
                            :class="{'bg-primary/5 text-primary font-bold': props.conference?.id === conf.id}"
                        >
                            {{ conf.title }}
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
                    class="text-sm font-medium transition-colors hover:text-gold"
                    :class="activeSection === link.href.split('#')[1] ? 'text-gold' : 'text-white/80'"
                >
                    {{ link.label }}
                </a>
                <Link
                    v-else
                    :href="link.href"
                    class="text-sm font-medium text-white/80 transition-colors hover:text-gold"
                >
                    {{ link.label }}
                </Link>
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
                    :class="props.lang === 'en' ? 'bg-gold text-sidebar' : 'bg-white/10 text-white/70'"
                    @click="emit('set-lang', 'en')"
                >
                    EN
                </button>
                <button
                    class="rounded-r border border-white/25 px-3 py-1 text-xs font-semibold transition-all"
                    :class="props.lang === 'id' ? 'bg-gold text-sidebar' : 'bg-white/10 text-white/70'"
                    @click="emit('set-lang', 'id')"
                >
                    ID
                </button>
            </div>

            <a
                href="#abstract"
                @click="scrollToSection($event, '#abstract')"
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

    <div v-if="props.isMenuOpen" class="bg-sidebar px-5 py-4 shadow-md lg:hidden">
        <ul class="flex flex-col gap-4">
            <li v-for="link in props.links" :key="link.label">
                <div v-if="link.isDropdown" class="flex flex-col gap-2">
                    <span class="block text-sm font-medium text-white/50 uppercase tracking-wider text-xs">
                        {{ link.label }}
                    </span>
                    <Link
                        v-for="conf in props.availableConferences"
                        :key="conf.id"
                        :href="route('conferences.show', conf.slug)"
                        class="block text-sm font-medium pl-3 border-l border-white/20 transition-colors hover:text-gold"
                        :class="props.conference?.id === conf.id ? 'text-gold border-gold' : 'text-white/80'"
                    >
                        {{ conf.title }}
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
        </ul>
    </div>
</template>
