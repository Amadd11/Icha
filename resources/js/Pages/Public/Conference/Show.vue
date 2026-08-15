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
    conference: Object,
    availableConferences: Array,
});

const { countdown } = useCountdown(props.conference?.event_date || "2026-11-10T08:00:00+07:00");

const countdownUnits = [
    { label: "Days", key: "days" },
    { label: "Hours", key: "hours" },
    { label: "Minutes", key: "minutes" },
    { label: "Seconds", key: "seconds" },
];

const tracks = computed(() => {
    if (props.conference?.categories?.length) {
        return props.conference.categories.map((c, idx) => ({
            badge: c.badge || `Track 0${idx + 1}`,
            title: c.name,
            description: c.description,
        }));
    }
    return [];
});

const timelineItems = computed(() => {
    if (props.conference?.timelines?.length) {
        return props.conference.timelines.map(t => ({
            period: t.period || 'Schedule',
            title: t.title,
            points: t.description ? t.description.split('\n') : [],
            borderClass: 'border-primary',
        }));
    }
    return [];
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
    <Head title="ICHA" />

    <PublicLayout
        :conference="props.conference"
        :available-conferences="props.availableConferences"
        :can-login="props.canLogin"
        :can-register="props.canRegister"
    >
        <Hero :conference="props.conference" :countdown="countdown" :countdown-units="countdownUnits" />

        <About
            :conference="props.conference"
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
            v-if="props.conference?.speakers?.length"
            eyebrow="Distinguished Guests"
            title="Keynote & Invited Speakers"
            description="Keynote and plenary speakers representing leading institutions."
            :speakers="props.conference?.speakers"
        />

        <Abstract />

        <Venue />

        <Sponsors
            v-if="props.conference?.sponsors?.length"
            eyebrow="Partnership Opportunities"
            title="Sponsorship & Exhibition"
            description="Join us as a sponsor and connect with the healthcare administration community."
            :sponsors="props.conference?.sponsors"
        />

        <Contact />
    </PublicLayout>
</template>
