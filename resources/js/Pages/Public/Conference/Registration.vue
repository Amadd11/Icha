<script setup>
import { Head } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import RegistrationSection from "@/Components/Conference/Registration.vue";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    activeConference: Object,
    availableConferences: Array,
    registrationFees: Array,
});
</script>

<template>
    <Head :title="'Registration Fees - ' + (props.activeConference?.title || 'ICHA')" />

    <PublicLayout
        :conference="props.activeConference"
        :available-conferences="props.availableConferences"
        :can-login="props.canLogin"
        :can-register="props.canRegister"
    >
        <div class="py-12">
            <RegistrationSection :registration-fees="props.registrationFees" />
        </div>
    </PublicLayout>
</template>
