<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    timeline: Object,
    conferences: Array,
});

const isEditing = Boolean(props.timeline);

const form = useForm({
    conference_id: props.timeline?.conference_id || (props.conferences?.[0]?.id || ''),
    title: props.timeline?.title || '',
    period: props.timeline?.period || '',
    date: props.timeline?.date || '',
    description: props.timeline?.description || '',
    order: props.timeline?.order ?? 0,
    is_completed: Boolean(props.timeline?.is_completed ?? false),
});

function submit() {
    if (isEditing) {
        form.put(route('admin.timelines.update', props.timeline.id));
    } else {
        form.post(route('admin.timelines.store'));
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Edit Timeline Item' : 'Add Timeline Item'" />

    <AdminLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ isEditing ? 'Edit Timeline Item' : 'Add New Timeline Item' }}</h1>
                    <p class="text-xs text-slate-500">Configure schedule periods and milestone descriptions for the conference.</p>
                </div>
                <Link
                    :href="route('admin.timelines.index')"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition"
                >
                    &larr; Back to List
                </Link>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] p-6 lg:p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Conference</label>
                        <select v-model="form.conference_id" class="admin-input" required>
                            <option value="" disabled>Select conference...</option>
                            <option v-for="conf in props.conferences" :key="conf.id" :value="conf.id">
                                {{ conf.title }}
                            </option>
                        </select>
                        <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.conference_id }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Milestone Title</label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="e.g. Abstract Selection & Review"
                            class="admin-input"
                            required
                        />
                        <span v-if="form.errors.title" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.title }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Display Period Label</label>
                            <input
                                v-model="form.period"
                                type="text"
                                placeholder="e.g. July - August 2026"
                                class="admin-input"
                            />
                            <span v-if="form.errors.period" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.period }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Order Index</label>
                            <input
                                v-model="form.order"
                                type="number"
                                min="0"
                                class="admin-input"
                            />
                            <span v-if="form.errors.order" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.order }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Detailed Points / Description</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Enter milestone bullet points (one per line)..."
                            class="admin-input"
                        ></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Tip: Each line will render as a bullet point on the public timeline.</p>
                        <span v-if="form.errors.description" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.description }}</span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            v-model="form.is_completed"
                            type="checkbox"
                            id="is_completed"
                            class="rounded border-slate-300 text-primary focus:ring-primary"
                        />
                        <label for="is_completed" class="text-xs font-bold text-slate-700 cursor-pointer">
                            Mark this milestone as completed
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <Link
                            :href="route('admin.timelines.index')"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-dark transition shadow-md disabled:opacity-50 cursor-pointer"
                        >
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Update Timeline' : 'Create Timeline') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AdminLayout>
</template>
