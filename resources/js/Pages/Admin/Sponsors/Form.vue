<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sponsor: Object,
    conferences: Array,
});

const isEdit = !!props.sponsor;
const logoPreview = ref(props.sponsor?.logo ? '/storage/' + props.sponsor.logo : null);

const form = useForm({
    conference_id: props.sponsor?.conference_id ?? '',
    name:          props.sponsor?.name          ?? '',
    website:       props.sponsor?.website       ?? '',
    tier:          props.sponsor?.tier          ?? 'bronze',
    description:   props.sponsor?.description   ?? '',
    is_active:     props.sponsor?.is_active     ?? true,
    order:         props.sponsor?.order         ?? 0,
    logo:          null,
});

function onLogoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    if (isEdit) {
        form.post(route('admin.sponsors.update', props.sponsor.id), {
            method: 'put',
            forceFormData: true,
        });
    } else {
        form.post(route('admin.sponsors.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Sponsor' : 'Add Sponsor'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.sponsors.index')" class="text-sm text-slate-400 hover:text-primary">Sponsors</Link>
                <span class="text-slate-300">/</span>
                <h1 class="text-lg font-bold text-slate-800">{{ isEdit ? 'Edit' : 'Add' }} Sponsor</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">

                        <!-- Logo -->
                        <div class="sm:col-span-2 flex items-center gap-5">
                            <div class="h-16 w-24 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center p-2">
                                <img v-if="logoPreview" :src="logoPreview" class="max-h-full max-w-full object-contain" />
                                <span v-else class="text-xs text-slate-300">No logo</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Logo</label>
                                <input type="file" accept="image/*" @change="onLogoChange" class="text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/20" />
                                <p class="mt-1 text-xs text-slate-400">Max 2MB (PNG, SVG, WebP preferred)</p>
                            </div>
                        </div>

                        <!-- Conference -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Conference <span class="text-red-400">*</span></label>
                            <select v-model="form.conference_id" class="admin-input" required>
                                <option value="">Select conference</option>
                                <option v-for="c in conferences" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </select>
                            <p v-if="form.errors.conference_id" class="mt-1 text-xs text-red-500">{{ form.errors.conference_id }}</p>
                        </div>

                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Sponsor Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" class="admin-input" placeholder="Company / Organization name" required />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Tier -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tier <span class="text-red-400">*</span></label>
                            <select v-model="form.tier" class="admin-input" required>
                                <option value="title">Title Sponsor</option>
                                <option value="platinum">Platinum</option>
                                <option value="gold">Gold</option>
                                <option value="silver">Silver</option>
                                <option value="bronze">Bronze</option>
                                <option value="exhibitor">Exhibitor</option>
                            </select>
                        </div>

                        <!-- Order -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Display Order</label>
                            <input v-model.number="form.order" type="number" min="0" class="admin-input" />
                        </div>

                        <!-- Website -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Website URL</label>
                            <input v-model="form.website" type="url" class="admin-input" placeholder="https://example.com" />
                        </div>

                        <!-- Description -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                            <textarea v-model="form.description" rows="3" class="admin-input" placeholder="Short description..."></textarea>
                        </div>

                        <!-- Active -->
                        <div class="flex items-center gap-3">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
                            <label for="is_active" class="text-sm font-medium text-slate-700">Show on website</label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link :href="route('admin.sponsors.index')" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Sponsor' : 'Add Sponsor') }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.admin-input {
    @apply w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary;
}
</style>
