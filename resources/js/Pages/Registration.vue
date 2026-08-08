<script setup>
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import NavbarSection from "@/Components/LandingPage/NavbarSection.vue";
import FooterSection from "@/Components/LandingPage/FooterSection.vue";
import RegistrationSection from "@/Components/LandingPage/RegistrationSection.vue";

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const lang = ref("en");
const isMenuOpen = ref(false);

const navLinks = [
    { href: "/#about", label: "About" },
    { href: "/#conference", label: "Conference" },
    { href: "/#timeline", label: "Timeline" },
    { href: "/#speakers", label: "Speakers" },
    { href: "/#abstract", label: "Abstract" },
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
    <Head title="Registration - ICHA 2026" />

    <div class="min-h-screen bg-white text-slate-800">
        <NavbarSection
            :links="navLinks"
            :can-login="props.canLogin"
            :lang="lang"
            :is-menu-open="isMenuOpen"
            @set-lang="setLang"
            @toggle-menu="toggleMenu"
        />

        <!-- Main Content -->
        <main class="min-h-screen">
            <RegistrationSection />
        </main>

        <FooterSection />
    </div>
</template>
