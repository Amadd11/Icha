<script setup>
import { Head } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import RegistrationSection from "@/Components/Conference/Registration.vue";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    activeConference: Object,
    registrationTypes: Array,
});
</script>

<template>
    <Head :title="'Registration - ' + (props.activeConference?.title || 'ICHA')" />

    <PublicLayout
        :conference="props.activeConference"
        :can-login="props.canLogin"
        :can-register="props.canRegister"
    >
        <div class="py-12">
            <RegistrationSection :registration-types="props.registrationTypes" />
        </div>
    </PublicLayout>
</template>
