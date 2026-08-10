<script setup>
import { ref } from "vue";
import Navbar from "@/Components/Navigation/Navbar.vue";
import Footer from "@/Components/Navigation/Footer.vue";

const props = defineProps({
    conference: {
        type: Object,
        default: null,
    },
    availableConferences: {
        type: Array,
        default: () => [],
    },
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
});

const lang = ref("en");
const isMenuOpen = ref(false);

const navLinks = [
    { href: "/#about", label: "About" },
    { isDropdown: true, label: "Conferences" },
    { href: "/#tracks", label: "Tracks" },
    { href: "/#timeline", label: "Timeline" },
    { href: "/#speakers", label: "Speakers" },
    { href: "/registration", label: "Registration" },
];

function setLang(value) {
    lang.value = value;
}

function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value;
}
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800">
        <Navbar
            :conference="props.conference"
            :available-conferences="props.availableConferences"
            :links="navLinks"
            :can-login="props.canLogin"
            :lang="lang"
            :is-menu-open="isMenuOpen"
            @set-lang="setLang"
            @toggle-menu="toggleMenu"
            @close-menu="isMenuOpen = false"
        />

        <main>
            <slot />
        </main>

        <Footer />
    </div>
</template>
