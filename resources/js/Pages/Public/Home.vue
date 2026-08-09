<script setup>
import { Head } from "@inertiajs/vue3";
import { onMounted, computed } from "vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import Hero from "@/Components/Conference/Hero.vue";
import About from "@/Components/Conference/About.vue";
import Topics from "@/Components/Conference/Topics.vue";
import Timeline from "@/Components/Conference/Timeline.vue";
import Speakers from "@/Components/Conference/Speakers.vue";
import Abstract from "@/Components/Conference/Abstract.vue";
import Venue from "@/Components/Conference/Venue.vue";
import Sponsors from "@/Components/Conference/Sponsors.vue";
import Contact from "@/Components/Conference/Contact.vue";
import { useCountdown } from "@/Composables/useCountdown";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    activeConference: Object,
    availableConferences: Array,
});

const { countdown } = useCountdown("2026-11-10T08:00:00+07:00");

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
        description: "Curriculum, learning innovation, and academic development in healthcare administration.",
        cardClass: "group overflow-hidden bg-gradient-to-br from-sidebar to-primary text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
    {
        badge: "Track 02",
        icon: "🏥",
        title: "Hospital Leadership & Management",
        description: "Leadership, governance, strategy, and operational excellence in healthcare organizations.",
        cardClass: "group overflow-hidden bg-gradient-to-br from-primary-dark to-primary text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
    {
        badge: "Track 03",
        icon: "🤖",
        title: "Quality, Innovation & Digital Health",
        description: "Quality improvement, patient safety, technology, and digital transformation in health services.",
        cardClass: "group overflow-hidden bg-gradient-to-br from-sidebar to-primary-dark text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
    },
    {
        badge: "Track 04",
        icon: "🌍",
        title: "Health Policy, Research & Sustainability",
        description: "Health policy, health economics, research methods, and sustainable health systems development.",
        cardClass: "group overflow-hidden bg-gradient-to-br from-primary to-primary-dark text-white transition-all hover:-translate-y-2 hover:shadow-2xl",
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

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                }
            });
        },
        { threshold: 0.1 }
    );

    document.querySelectorAll(".fade-in").forEach((el) => observer.observe(el));

    return () => {
        observer.disconnect();
    };
});
</script>

<template>
    <Head :title="props.activeConference?.title || 'ICHA Conference Portal'" />

    <PublicLayout
        :conference="props.activeConference"
        :available-conferences="props.availableConferences"
        :can-login="props.canLogin"
        :can-register="props.canRegister"
    >
        <Hero :conference="props.activeConference" :countdown="countdown" :countdown-units="countdownUnits" />

        <About
            eyebrow="About the Conference"
            :title="props.activeConference?.theme || 'Healthcare Administration for a Sustainable Future'"
            :description="props.activeConference?.description || 'ICHA 2026 brings together researchers, academics, practitioners, students, and policymakers to share ideas, innovations, and best practices for the future of health systems.'"
            :stats="aboutStats"
        />

        <Topics
            eyebrow="Scientific Programme"
            title="Scientific Tracks"
            description="Four focused tracks covering the full spectrum of healthcare administration research and practice."
            :items="tracks"
        />

        <Timeline
            eyebrow="Preparation Schedule"
            title="Conference Timeline"
            description="Key milestones from preparation through to the conference event."
            :items="timelineItems"
        />

        <Speakers
            eyebrow="Distinguished Guests"
            title="Keynote & Invited Speakers"
            description="Keynote and plenary speakers representing leading institutions."
            :speakers="props.activeConference?.speakers"
        />

        <Abstract />

        <Venue />

        <Sponsors
            eyebrow="Partnership Opportunities"
            title="Sponsorship & Exhibition"
            description="Join us as a sponsor and connect with the healthcare administration community."
            :sponsors="props.activeConference?.sponsors"
        />

        <Contact />
    </PublicLayout>
</template>
