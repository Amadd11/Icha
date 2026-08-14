<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    registrationFee: Object,
    conferences: Array,
});

const isEdit = !!props.registrationFee;

const form = useForm({
    conference_id: props.registrationFee?.conference_id ?? props.conferences?.[0]?.id ?? '',
    name:          props.registrationFee?.name          ?? '',
    mode:          props.registrationFee?.mode          ?? 'offline',
    price:         props.registrationFee?.price         ?? 0,
});

function submit() {
    if (isEdit) {
        form.put(route('admin.registration-fees.update', props.registrationFee.id));
    } else {
        form.post(route('admin.registration-fees.store'));
    }
}

const inputClass = 'block w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 py-2.5 px-3.5 transition hover:border-slate-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20';
</script>

<template>
    <Head :title="isEdit ? 'Edit Registration Fee - Admin' : 'Add Registration Fee - Admin'" />

    <AdminLayout>
        <div class="max-w-2xl space-y-6">
            
            <!-- Back & Title -->
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.registration-fees.index')"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                >
                    &larr; Back
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        {{ isEdit ? 'Edit Registration Fee' : 'Add Registration Fee' }}
                    </h1>
                    <p class="text-xs text-slate-500">Configure conference event, package name, mode, and registration price.</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-xs">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Conference Select -->
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-700">Conference Event <span class="text-red-500">*</span></label>
                        <select v-model="form.conference_id" :class="inputClass" required>
                            <option v-for="conf in props.conferences" :key="conf.id" :value="conf.id">
                                {{ conf.title }}
                            </option>
                        </select>
                        <p v-if="form.errors.conference_id" class="mt-1 text-xs text-red-500">{{ form.errors.conference_id }}</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-700">Package Name <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" :class="inputClass" placeholder="e.g. Presenter Nasional Offline / Mahasiswa Online" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Mode Select -->
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-700">Attendance Mode <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="form.mode = 'offline'"
                                :class="[
                                    'py-3 px-4 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                    form.mode === 'offline'
                                        ? 'bg-purple-50 border-primary text-primary shadow-xs'
                                        : 'bg-slate-50/70 border-slate-300 text-slate-600 hover:bg-slate-100'
                                ]"
                            >
                                Offline (On-Site)
                            </button>
                            <button
                                type="button"
                                @click="form.mode = 'online'"
                                :class="[
                                    'py-3 px-4 rounded-xl border text-xs font-bold transition flex items-center justify-center cursor-pointer',
                                    form.mode === 'online'
                                        ? 'bg-emerald-50 border-emerald-500 text-emerald-700 shadow-xs'
                                        : 'bg-slate-50/70 border-slate-300 text-slate-600 hover:bg-slate-100'
                                ]"
                            >
                                Online (Virtual)
                            </button>
                        </div>
                        <p v-if="form.errors.mode" class="mt-1 text-xs text-red-500">{{ form.errors.mode }}</p>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-700">Registration Fee / Price (Rp) <span class="text-red-500">*</span></label>
                        <input v-model="form.price" type="number" step="1000" min="0" :class="inputClass" placeholder="e.g. 500000" required />
                        <p v-if="form.errors.price" class="mt-1 text-xs text-red-500">{{ form.errors.price }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Link
                            :href="route('admin.registration-fees.index')"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-black text-xs px-7 py-2.5 shadow-md transition cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : (isEdit ? 'Update Fee' : 'Save Fee') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </AdminLayout>
</template>
