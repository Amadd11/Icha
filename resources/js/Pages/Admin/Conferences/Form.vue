<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    conference: Object, // null = create, object = edit
});

const isEdit = !!props.conference;

const form = useForm({
    _method:     isEdit ? 'put' : 'post',
    title:       props.conference?.title       ?? '',
    year:        props.conference?.year        ?? new Date().getFullYear(),
    tagline:     props.conference?.tagline     ?? '',
    description: props.conference?.description ?? '',
    start_date:  props.conference?.start_date  ?? '',
    end_date:    props.conference?.end_date    ?? '',
    venue:       props.conference?.venue       ?? '',
    city:        props.conference?.city        ?? '',
    country:     props.conference?.country     ?? 'Indonesia',
    theme:       props.conference?.theme       ?? '',
    website:     props.conference?.website     ?? '',
    email:       props.conference?.email       ?? '',
    status:      props.conference?.status      ?? 'draft',
    is_active:   props.conference?.is_active   ?? false,
    logo:        null,
    hero_image:  null,
});

function submit() {
    const url = isEdit
        ? route('admin.conferences.update', props.conference.id)
        : route('admin.conferences.store');
    
    form.post(url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Conference' : 'New Conference'" />
    <AdminLayout>
        <div class="mb-6 flex items-center gap-2">
            <Link :href="route('admin.conferences.index')" class="text-sm font-semibold text-slate-500 hover:text-indigo-600">Conferences</Link>
            <span class="text-slate-300">/</span>
            <h1 class="text-lg font-bold text-slate-800">{{ isEdit ? 'Edit' : 'New' }} Conference</h1>
        </div>

        <div class="max-w-3xl">
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Basic Info Card -->
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-slate-400">Basic Information</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Title -->
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Title <span class="text-red-400">*</span></label>
                            <input v-model="form.title" type="text" class="admin-input" placeholder="e.g. ICHA 2026" required />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>
                        <!-- Year -->
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Year</label>
                            <input v-model="form.year" type="number" class="admin-input" placeholder="e.g. 2026" />
                            <p v-if="form.errors.year" class="mt-1 text-xs text-red-500">{{ form.errors.year }}</p>
                        </div>
                        <!-- Tagline -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tagline</label>
                            <input v-model="form.tagline" type="text" class="admin-input" placeholder="e.g. Healthcare Administration for a Sustainable Future" />
                        </div>
                        <!-- Theme -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Theme</label>
                            <input v-model="form.theme" type="text" class="admin-input" placeholder="Conference theme" />
                        </div>
                        <!-- Description -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                            <textarea v-model="form.description" rows="4" class="admin-input" placeholder="About the conference..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Date & Venue -->
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-slate-400">Date & Venue</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Start Date</label>
                            <input v-model="form.start_date" type="date" class="admin-input" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">End Date</label>
                            <input v-model="form.end_date" type="date" class="admin-input" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Venue</label>
                            <input v-model="form.venue" type="text" class="admin-input" placeholder="e.g. Universitas Muhammadiyah Surabaya" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">City</label>
                            <input v-model="form.city" type="text" class="admin-input" placeholder="Surabaya" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
                            <input v-model="form.country" type="text" class="admin-input" placeholder="Indonesia" />
                        </div>
                    </div>
                </div>

                <!-- Contact & Settings -->
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-slate-400">Contact & Settings</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input v-model="form.email" type="email" class="admin-input" placeholder="contact@icha2026.id" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Website</label>
                            <input v-model="form.website" type="url" class="admin-input" placeholder="https://icha2026.id" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Status <span class="text-red-400">*</span></label>
                            <select v-model="form.status" class="admin-input">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="archived">Archived</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status }}</p>
                        </div>
                        <div class="flex items-center gap-3 pt-5">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
                            <label for="is_active" class="text-sm font-medium text-slate-700">Set as active conference</label>
                        </div>
                    </div>
                </div>

                <!-- Media (Logo & Hero) -->
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-slate-400">Media</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Logo (Max 2MB)</label>
                            <input type="file" @change="e => form.logo = e.target.files[0]" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-dark" accept="image/*" />
                            <p v-if="form.errors.logo" class="mt-1 text-xs text-red-500">{{ form.errors.logo }}</p>
                            <div v-if="isEdit && conference.logo" class="mt-2">
                                <span class="text-xs text-slate-500">Current logo available. Upload new to replace.</span>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Hero Image (Max 5MB)</label>
                            <input type="file" @change="e => form.hero_image = e.target.files[0]" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-dark" accept="image/*" />
                            <p v-if="form.errors.hero_image" class="mt-1 text-xs text-red-500">{{ form.errors.hero_image }}</p>
                            <div v-if="isEdit && conference.hero_image" class="mt-2">
                                <span class="text-xs text-slate-500">Current hero image available. Upload new to replace.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3">
                    <Link :href="route('admin.conferences.index')" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Conference' : 'Create Conference') }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
