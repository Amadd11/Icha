<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    speaker: Object,
    conferences: Array,
});

const isEdit = !!props.speaker;
const photoPreview = ref(props.speaker?.photo ? '/storage/' + props.speaker.photo : null);

const form = useForm({
    conference_id: props.speaker?.conference_id ?? '',
    name:          props.speaker?.name          ?? '',
    title:         props.speaker?.title         ?? '',
    institution:   props.speaker?.institution   ?? '',
    country:       props.speaker?.country       ?? '',
    bio:           props.speaker?.bio           ?? '',
    email:         props.speaker?.email         ?? '',
    type:          props.speaker?.type          ?? 'invited',
    order:         props.speaker?.order         ?? 0,
    photo:         null,
});

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    if (isEdit) {
        form.post(route('admin.speakers.update', props.speaker.id), {
            method: 'put',
            forceFormData: true,
        });
    } else {
        form.post(route('admin.speakers.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Speaker' : 'Add Speaker'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.speakers.index')" class="text-sm text-slate-400 hover:text-primary">Speakers</Link>
                <span class="text-slate-300">/</span>
                <h1 class="text-lg font-bold text-slate-800">{{ isEdit ? 'Edit' : 'Add' }} Speaker</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-2">

                        <!-- Photo -->
                        <div class="sm:col-span-2 flex items-center gap-5">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full bg-slate-100">
                                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center text-2xl text-slate-300">📷</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Photo</label>
                                <input type="file" accept="image/*" @change="onPhotoChange" class="text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/20" />
                                <p class="mt-1 text-xs text-slate-400">Max 2MB (JPEG, PNG, WebP)</p>
                                <p v-if="form.errors.photo" class="mt-1 text-xs text-red-500">{{ form.errors.photo }}</p>
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
                            <label class="mb-1 block text-sm font-medium text-slate-700">Full Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" class="admin-input" placeholder="Prof. Dr. John Doe" required />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Academic Title</label>
                            <input v-model="form.title" type="text" class="admin-input" placeholder="Prof. Dr." />
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Speaker Type <span class="text-red-400">*</span></label>
                            <select v-model="form.type" class="admin-input" required>
                                <option value="keynote">Keynote</option>
                                <option value="plenary">Plenary</option>
                                <option value="invited">Invited</option>
                            </select>
                        </div>

                        <!-- Institution -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Institution</label>
                            <input v-model="form.institution" type="text" class="admin-input" placeholder="University of..." />
                        </div>

                        <!-- Country -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>
                            <input v-model="form.country" type="text" class="admin-input" placeholder="Indonesia" />
                        </div>

                        <!-- Order -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Display Order</label>
                            <input v-model.number="form.order" type="number" min="0" class="admin-input" />
                        </div>

                        <!-- Bio -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Biography</label>
                            <textarea v-model="form.bio" rows="4" class="admin-input" placeholder="Short bio..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link :href="route('admin.speakers.index')" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Speaker' : 'Add Speaker') }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
