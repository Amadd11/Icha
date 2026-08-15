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

const { countdown } = useCountdown(props.activeConference?.event_date || "2026-11-10T08:00:00+07:00");

const countdownUnits = [
    { label: "Days", key: "days" },
    { label: "Hours", key: "hours" },
    { label: "Minutes", key: "minutes" },
    { label: "Seconds", key: "seconds" },
];

const tracks = computed(() => {
    if (props.activeConference?.categories?.length) {
        return props.activeConference.categories.map((c, idx) => ({
            badge: c.badge || `Track 0${idx + 1}`,
            title: c.name,
            description: c.description,
        }));
    }
    return [];
});

const timelineItems = computed(() => {
    if (props.activeConference?.timelines?.length) {
        return props.activeConference.timelines.map(t => ({
            period: t.period || 'Schedule',
            title: t.title,
            points: t.description ? t.description.split('\n') : [],
            borderClass: 'border-primary',
        }));
    }
    return [];
});

const jsonLd = computed(() => {
    const conf = props.activeConference;
    if (!conf) return null;

    return JSON.stringify({
        "@context": "https://schema.org",
        "@type": "Event",
        "name": `${conf.title || 'ICHA 2026'} - ${conf.tagline || 'International Conference on Healthcare Administration'}`,
        "description": conf.description || '11th International Conference on Healthcare Administration',
        "startDate": conf.start_date || "2026-11-10",
        "endDate": conf.end_date || "2026-11-11",
        "eventAttendanceMode": "https://schema.org/MixedEventAttendanceMode",
        "eventStatus": "https://schema.org/EventScheduled",
        "location": {
            "@type": "Place",
            "name": conf.venue || "Surabaya International Convention Center",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": conf.city || "Surabaya",
                "addressCountry": conf.country || "Indonesia"
            }
        },
        "organizer": {
            "@type": "Organization",
            "name": "Perhimpunan Pengelola Program Magister Administrasi Rumah Sakit Indonesia (PERSI/MARSI) & Universitas Muhammadiyah Surabaya",
            "url": "https://icha2026.id"
        },
        "offers": {
            "@type": "AggregateOffer",
            "priceCurrency": "IDR",
            "availability": "https://schema.org/InStock",
            "url": "https://icha2026.id/register"
        }
    });
});

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("visible");
                    observer.unobserve(entry.target);
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
    <Head>
        <title>{{ props.activeConference?.title ? `${props.activeConference.title} - ${props.activeConference.tagline || 'International Conference on Healthcare Administration'}` : 'ICHA 2026 - International Conference on Healthcare Administration' }}</title>
        
        <!-- Standard Meta -->
        <meta name="description" :content="props.activeConference?.description || 'The 11th International Conference on Healthcare Administration (ICHA 2026). Join global healthcare leaders, researchers, academics, and policymakers.'" />
        <meta name="keywords" content="ICHA 2026, International Conference on Healthcare Administration, PIP MARSI, UMSURA, Call for Papers, Healthcare Management, Hospital Administration, Surabaya Conference" />
        <meta name="author" content="PIP MARSI & Universitas Muhammadiyah Surabaya" />

        <!-- Open Graph / WhatsApp / Facebook / LinkedIn -->
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="props.activeConference?.title ? `${props.activeConference.title} - ${props.activeConference.tagline || 'International Conference on Healthcare Administration'}` : 'ICHA 2026 - International Conference on Healthcare Administration'" />
        <meta property="og:description" :content="props.activeConference?.description || 'The 10th International Conference on Healthcare Administration (ICHA 2026). Join global healthcare leaders, researchers, academics, and policymakers.'" />
        <meta property="og:image" content="/assets/logo/logo-umsura.png" />
        <meta property="og:site_name" content="ICHA Conference" />

        <!-- Google Structured Data (JSON-LD) -->
        <component :is="'script'" type="application/ld+json" v-if="jsonLd" v-html="jsonLd" />
    </Head>

    <PublicLayout
        :conference="props.activeConference"
        :available-conferences="props.availableConferences"
        :can-login="props.canLogin"
        :can-register="props.canRegister"
    >
        <Hero :conference="props.activeConference" :countdown="countdown" :countdown-units="countdownUnits" />

        <About
            :conference="props.activeConference"
            eyebrow="About the Conference"
        />

        <Topics
            eyebrow="Scientific Programme"
            title="Scientific Tracks"
            description="Focused tracks covering the full spectrum of healthcare administration research and practice."
            :items="tracks"
        />

        <Timeline
            v-if="timelineItems.length"
            eyebrow="Preparation Schedule"
            title="Conference Timeline"
            description="Key milestones from preparation through to the conference event."
            :items="timelineItems"
        />

        <Speakers
            eyebrow="Distinguished Guests"
            title="Keynote & Invited Speakers"
            description="Keynote and plenary speakers representing leading institutions."
            :speakers="props.activeConference?.speakers || []"
        />

        <Abstract />

        <Venue />

        <Sponsors
            v-if="props.activeConference?.sponsors?.length"
            eyebrow="Partnership Opportunities"
            title="Sponsorship & Exhibition"
            description="Join us as a sponsor and connect with the healthcare administration community."
            :sponsors="props.activeConference?.sponsors"
        />

        <Contact />
    </PublicLayout>
</template>
