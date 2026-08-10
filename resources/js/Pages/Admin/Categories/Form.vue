<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    category: Object,
    conferences: Array,
});

const isEditing = Boolean(props.category);

const form = useForm({
    conference_id: props.category?.conference_id || (props.conferences?.[0]?.id || ''),
    name: props.category?.name || '',
    badge: props.category?.badge || '',
    description: props.category?.description || '',
    icon: props.category?.icon || '🎓',
    order: props.category?.order ?? 0,
});

function submit() {
    if (isEditing) {
        form.put(route('admin.categories.update', props.category.id));
    } else {
        form.post(route('admin.categories.store'));
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Edit Category' : 'Add Category'" />

    <AdminLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ isEditing ? 'Edit Track Category' : 'Add New Track Category' }}</h1>
                    <p class="text-xs text-slate-500">Define scientific tracks, topic areas, and descriptions.</p>
                </div>
                <Link
                    :href="route('admin.categories.index')"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition"
                >
                    &larr; Back to List
                </Link>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.04)] p-6 lg:p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Conference</label>
                        <select v-model="form.conference_id" class="admin-input" required>
                            <option value="" disabled>Select conference...</option>
                            <option v-for="conf in props.conferences" :key="conf.id" :value="conf.id">
                                {{ conf.title }}
                            </option>
                        </select>
                        <span v-if="form.errors.conference_id" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.conference_id }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Track Name / Title</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Healthcare Administration Education"
                            class="admin-input"
                            required
                        />
                        <span v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.name }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Badge Code</label>
                            <input
                                v-model="form.badge"
                                type="text"
                                placeholder="e.g. Track 01"
                                class="admin-input"
                            />
                            <span v-if="form.errors.badge" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.badge }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Icon / Emoji</label>
                            <input
                                v-model="form.icon"
                                type="text"
                                placeholder="e.g. 🎓 or 🏥"
                                class="admin-input"
                            />
                            <span v-if="form.errors.icon" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.icon }}</span>
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Track Description</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Briefly describe the scope of research for this scientific track..."
                            class="admin-input"
                        ></textarea>
                        <span v-if="form.errors.description" class="text-[10px] text-rose-500 font-bold mt-1 block">{{ form.errors.description }}</span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <Link
                            :href="route('admin.categories.index')"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-dark transition shadow-md disabled:opacity-50 cursor-pointer"
                        >
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Update Category' : 'Create Category') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AdminLayout>
</template>
