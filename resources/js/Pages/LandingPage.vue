<script setup>
import { Head } from "@inertiajs/vue3";
import { onMounted, ref, computed } from "vue";
import NavbarSection from "@/Components/LandingPage/NavbarSection.vue";
import HeroSection from "@/Components/LandingPage/HeroSection.vue";
import AboutSection from "@/Components/LandingPage/AboutSection.vue";
import TracksSection from "@/Components/LandingPage/TracksSection.vue";
import TimelineSection from "@/Components/LandingPage/TimelineSection.vue";
import SpeakersSection from "@/Components/LandingPage/SpeakersSection.vue";
import AbstractSection from "@/Components/LandingPage/AbstractSection.vue";
import VenueSection from "@/Components/LandingPage/VenueSection.vue";
import SponsorsSection from "@/Components/LandingPage/SponsorsSection.vue";
import ContactSection from "@/Components/LandingPage/ContactSection.vue";
import FooterSection from "@/Components/LandingPage/FooterSection.vue";
import { useCountdown } from "@/Composables/useCountdown";

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    activeConference: {
        type: Object,
    },
});

const lang = ref("en");
const isMenuOpen = ref(false);
const { countdown } = useCountdown("2026-11-10T08:00:00+07:00");

const navLinks = [
    { href: "/#about", label: "About" },
    { href: "/#conference", label: "Conference" },
    { href: "/#timeline", label: "Timeline" },
    { href: "/#speakers", label: "Speakers" },
    { href: "/#abstract", label: "Abstract" },
    { href: "/registration", label: "Registration" },
];

const aboutStats = [
    { value: "4", label: "Scientific Tracks" },
    { value: "2", label: "Conference Days" },
    { value: "3", label: "Host Universities" },
];

const countdownUnits = [
    { label: "Days", key: "days" },
    { label: "Hours", key: "hours" },
    { label: "Minutes", key: "minutes" },
    { label: "Seconds", key: "seconds" },
];

const defaultTracks = [
    {
        badge: "Track 01",
        icon: "🎓",
        title: "Healthcare Administration Education",
        description:
            "Curriculum, learning innovation, and academic development in healthcare administration.",
        cardClass:
            "group overflow-hidden bg-gradient-to-br from-sidebar to-primary text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
    {
        badge: "Track 02",
        icon: "🏥",
        title: "Hospital Leadership & Management",
        description:
            "Leadership, governance, strategy, and operational excellence in healthcare organizations.",
        cardClass:
            "group overflow-hidden bg-gradient-to-br from-primary-dark to-primary text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
    {
        badge: "Track 03",
        icon: "🤖",
        title: "Quality, Innovation & Digital Health",
        description:
            "Quality improvement, patient safety, technology, and digital transformation in health services.",
        cardClass:
            "group overflow-hidden bg-gradient-to-br from-sidebar to-primary-dark text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
    {
        badge: "Track 04",
        icon: "🌍",
        title: "Health Policy, Research & Sustainability",
        description:
            "Health policy, health economics, research methods, and sustainable health systems development.",
        cardClass:
            "group overflow-hidden bg-gradient-to-br from-primary to-primary-dark text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
];

const tracks = computed(() => {
    if (props.activeConference?.categories?.length) {
        const bgClasses = [
            "group overflow-hidden bg-gradient-to-br from-sidebar to-primary text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
            "group overflow-hidden bg-gradient-to-br from-primary-dark to-primary text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
            "group overflow-hidden bg-gradient-to-br from-sidebar to-primary-dark text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
            "group overflow-hidden bg-gradient-to-br from-primary to-primary-dark text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
        ];
        return props.activeConference.categories.map((c, i) => ({
            badge: c.badge || `Track 0${i + 1}`,
            icon: c.icon || "🎓",
            title: c.name,
            description: c.description,
            cardClass: bgClasses[i % bgClasses.length],
        }));
    }
    return defaultTracks;
});

const defaultTimelineItems = [
    {
        period: "July - August 2026",
        title: "Preparation & Launch",
        points: [
            "24–25 Jul – PIPMARSI Meeting",
            "5 Aug – TOR & Branding Finalization",
            "8 Aug – Committee Formation",
            "11 Aug – 1st Announcement & Call for Abstract",
        ],
        borderClass: "border-primary-dark",
    },
    {
        period: "September 2026",
        title: "Speaker Confirmation",
        points: [
            "7 Sep – Keynote invitations sent",
            "21 Sep – 70% keynote confirmed",
            "22 Sep – 2nd Announcement & Registration Opens",
        ],
        borderClass: "border-primary",
    },
    {
        period: "October 2026",
        title: "Abstract Selection",
        points: [
            "3 Oct – Abstract submission closed",
            "4–10 Oct – Abstract review",
            "12 Oct – Acceptance notification",
        ],
        borderClass: "border-primary",
    },
];

const timelineItems = computed(() => {
    if (props.activeConference?.timelines?.length) {
        return props.activeConference.timelines.map(t => ({
            period: t.period || 'Schedule',
            title: t.title,
            points: t.description ? t.description.split('\n') : [],
            borderClass: 'border-primary',
        }));
    }
    return defaultTimelineItems;
});

function setLang(value) {
    lang.value = value;
}

function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value;
}

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                }
            });
        },
        { threshold: 0.1 },
    );

    document.querySelectorAll(".fade-in").forEach((el) => observer.observe(el));

    return () => {
        observer.disconnect();
    };
});
</script>

<template>
    <Head title="ICHA 2026" />

    <div class="min-h-screen bg-white text-slate-800">
        <NavbarSection
            :links="navLinks"
            :can-login="props.canLogin"
            :lang="lang"
            :is-menu-open="isMenuOpen"
            @set-lang="setLang"
            @toggle-menu="toggleMenu"
        />

        <HeroSection :countdown="countdown" :countdown-units="countdownUnits" />

        <AboutSection
            eyebrow="About the Conference"
            :title="props.activeConference?.theme || 'Healthcare Administration for a Sustainable Future'"
            :description="props.activeConference?.description || 'ICHA 2026 brings together researchers, academics, practitioners, students, and policymakers to share ideas, innovations, and best practices for the future of health systems.'"
            :stats="aboutStats"
        />

        <TracksSection
            eyebrow="Scientific Programme"
            title="Scientific Tracks"
            description="Four focused tracks covering the full spectrum of healthcare administration research and practice."
            :items="tracks"
        />

        <TimelineSection
            eyebrow="Preparation Schedule"
            title="Conference Timeline"
            description="Key milestones from preparation through to the conference event."
            :items="timelineItems"
        />

        <SpeakersSection
            eyebrow="Distinguished Guests"
            title="Keynote & Invited Speakers"
            description="Keynote and plenary speakers representing leading institutions."
            :speakers="props.activeConference?.speakers"
        />

        <AbstractSection />
        <VenueSection
            eyebrow="Location"
            title="Conference Venue"
            description="See you in Surabaya!"
        />
        
        <SponsorsSection
            eyebrow="Partnership Opportunities"
            title="Sponsorship & Exhibition"
            description="Join us as a sponsor and connect with the healthcare administration community."
            :sponsors="props.activeConference?.sponsors"
        />
        <ContactSection />
        <FooterSection />
    </div>
</template>

<style scoped>
.fade-in {
    opacity: 0;
    transform: translateY(24px);
    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

.fade-in.visible {
    opacity: 1;
    transform: translateY(0);
}
</style>
