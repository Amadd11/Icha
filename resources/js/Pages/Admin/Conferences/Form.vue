<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    conference: Object, // null = create, object = edit
});

const isEdit = !!props.conference;

const logoPreview = ref(null);
const heroPreview = ref(null);

const form = useForm({
    _method:           isEdit ? 'put' : 'post',
    title:             props.conference?.title       ?? '',
    year:              props.conference?.year        ?? new Date().getFullYear(),
    tagline:           props.conference?.tagline     ?? '',
    description:       props.conference?.description ?? '',
    start_date:        props.conference?.start_date  ?? '',
    end_date:          props.conference?.end_date    ?? '',
    venue:             props.conference?.venue       ?? '',
    city:              props.conference?.city        ?? '',
    country:           props.conference?.country     ?? 'Indonesia',
    theme:             props.conference?.theme       ?? '',
    email:             props.conference?.email       ?? '',
    status:            props.conference?.status      ?? 'draft',
    is_active:         props.conference?.is_active   ?? false,
    logo:              null,
    hero_image:        null,
    remove_logo:       false,
    remove_hero_image: false,
});

function handleLogoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        form.remove_logo = false;
        logoPreview.value = URL.createObjectURL(file);
    }
}

function handleHeroChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.hero_image = file;
        form.remove_hero_image = false;
        heroPreview.value = URL.createObjectURL(file);
    }
}

function removeLogo() {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
}

function removeHeroImage() {
    form.hero_image = null;
    form.remove_hero_image = true;
    heroPreview.value = null;
}

function formatStorageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}

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
        <!-- Header & Breadcrumb -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <Link :href="route('admin.conferences.index')" class="font-semibold hover:text-purple-700">Conferences</Link>
                    <span>/</span>
                    <span class="text-slate-800 font-bold">{{ isEdit ? 'Edit Edition' : 'Create New' }}</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ isEdit ? 'Edit Conference Details' : 'Create New Conference' }}</h1>
            </div>
        </div>

        <!-- 2-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Form Inputs (2 cols) -->
            <div class="lg:col-span-2">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Basic Info Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                        <div class="mb-5 border-b border-slate-100 pb-3">
                            <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">Basic Information</h2>
                        </div>
                        
                        <div class="grid gap-5 sm:grid-cols-2">
                            <!-- Title -->
                            <div class="sm:col-span-1">
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Title <span class="text-red-500">*</span></label>
                                <input v-model="form.title" type="text" class="admin-input" placeholder="e.g. ICHA 2026" required />
                                <p v-if="form.errors.title" class="mt-1 text-xs text-red-500 font-semibold">{{ form.errors.title }}</p>
                            </div>

                            <!-- Year -->
                            <div class="sm:col-span-1">
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Year</label>
                                <input v-model="form.year" type="number" class="admin-input" placeholder="e.g. 2026" />
                                <p v-if="form.errors.year" class="mt-1 text-xs text-red-500 font-semibold">{{ form.errors.year }}</p>
                            </div>

                            <!-- Tagline -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Tagline</label>
                                <input v-model="form.tagline" type="text" class="admin-input" placeholder="e.g. 11th International Conference on Healthcare Administration" />
                            </div>

                            <!-- Theme -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Theme</label>
                                <input v-model="form.theme" type="text" class="admin-input" placeholder="e.g. Healthcare Administration for a Sustainable Future" />
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Description</label>
                                <textarea v-model="form.description" rows="4" class="admin-input" placeholder="Detailed description of the conference event..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Venue Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                        <div class="mb-5 border-b border-slate-100 pb-3">
                            <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">Date & Venue Settings</h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Start Date</label>
                                <input v-model="form.start_date" type="date" class="admin-input" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">End Date</label>
                                <input v-model="form.end_date" type="date" class="admin-input" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Venue</label>
                                <input v-model="form.venue" type="text" class="admin-input" placeholder="e.g. Surabaya International Convention Center" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">City</label>
                                <input v-model="form.city" type="text" class="admin-input" placeholder="Surabaya" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Country</label>
                                <input v-model="form.country" type="text" class="admin-input" placeholder="Indonesia" />
                            </div>
                        </div>
                    </div>

                    <!-- Media Uploads Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                        <div class="mb-5 border-b border-slate-100 pb-3">
                            <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">Media Assets</h2>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- Logo Upload -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Logo (Max 2MB)</label>
                                <input type="file" @change="handleLogoChange" class="block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-gold file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-slate-950 hover:file:bg-amber-400 cursor-pointer" accept="image/*" />
                                <p v-if="form.errors.logo" class="mt-1 text-xs text-red-500 font-semibold">{{ form.errors.logo }}</p>
                                
                                <div v-if="!form.remove_logo && (logoPreview || (isEdit && conference.logo))" class="mt-3 flex items-center justify-between gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50">
                                    <div class="flex items-center gap-3">
                                        <img :src="logoPreview || formatStorageUrl(conference.logo)" alt="Logo preview" class="h-10 w-auto rounded-lg object-contain border border-slate-200 p-1 bg-white" />
                                        <span class="text-xs text-slate-600 font-bold">{{ logoPreview ? 'New logo selected' : 'Current active logo' }}</span>
                                    </div>
                                    <button type="button" @click="removeLogo" class="text-xs font-extrabold text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                        Delete
                                    </button>
                                </div>
                                <div v-else-if="form.remove_logo" class="mt-2 text-xs font-bold text-amber-600">
                                    Logo will be removed upon saving.
                                </div>
                            </div>

                            <!-- Hero Image Banner -->
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Hero Banner (Max 10MB)</label>
                                <input type="file" @change="handleHeroChange" class="block w-full text-xs text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-gold file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-slate-950 hover:file:bg-amber-400 cursor-pointer" accept="image/*" />
                                <p v-if="form.errors.hero_image" class="mt-1 text-xs text-red-500 font-semibold">{{ form.errors.hero_image }}</p>
                                
                                <div v-if="!form.remove_hero_image && (heroPreview || (isEdit && conference.hero_image))" class="mt-3 flex items-center justify-between gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50">
                                    <div class="flex items-center gap-3">
                                        <img :src="heroPreview || formatStorageUrl(conference.hero_image)" alt="Hero preview" class="h-14 w-24 rounded-lg object-cover border border-slate-200 shadow-xs" />
                                        <span class="text-xs text-slate-600 font-bold">{{ heroPreview ? 'New hero banner selected' : 'Current active hero banner' }}</span>
                                    </div>
                                    <button type="button" @click="removeHeroImage" class="text-xs font-extrabold text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition cursor-pointer">
                                        Delete
                                    </button>
                                </div>
                                <div v-else-if="form.remove_hero_image" class="mt-2 text-xs font-bold text-amber-600">
                                    Hero banner will be removed upon saving.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Publication Settings Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                        <div class="mb-5 border-b border-slate-100 pb-3">
                            <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">Status & Portal Settings</h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Contact Email</label>
                                <input v-model="form.email" type="email" class="admin-input" placeholder="info@icha2026.id" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Lifecycle Status <span class="text-red-500">*</span></label>
                                <select v-model="form.status" class="admin-input font-bold">
                                    <option value="draft">Draft (In Preparation)</option>
                                    <option value="active">Active (Registration Open)</option>
                                    <option value="archived">Archived (Past Event)</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-xs text-red-500 font-semibold">{{ form.errors.status }}</p>
                            </div>

                            <!-- Set Active Portal Checkbox -->
                            <div class="sm:col-span-2 rounded-xl bg-purple-50/60 border border-purple-200/80 p-4">
                                <div class="flex items-start gap-3">
                                    <input id="is_active" v-model="form.is_active" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-purple-300 text-purple-900 focus:ring-purple-900 cursor-pointer" />
                                    <div>
                                        <label for="is_active" class="text-xs font-black uppercase tracking-wider text-purple-950 cursor-pointer block">Set as Active Live Portal</label>
                                        <p class="text-xs text-purple-900/80 mt-0.5">
                                            Activating this conference will publish its content on the main home landing page (<code class="bg-white/80 px-1 py-0.5 rounded text-purple-950">/</code>) and automatically set other conferences as inactive.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Action Bar -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <Link :href="route('admin.conferences.index')" class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-extrabold px-8 py-3 text-xs shadow-md transition disabled:opacity-50 cursor-pointer"
                        >
                            {{ form.processing ? 'Saving Changes...' : (isEdit ? 'Update Conference' : 'Create Conference') }}
                        </button>
                    </div>

                </form>
            </div>

            <!-- Right Column: Admin Guidelines & Helper Notes (1 col) -->
            <div class="lg:col-span-1 space-y-6 sticky top-6">
                
                <!-- Quick Guidelines Card -->
                <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/80 via-yellow-50/40 to-white p-6 shadow-xs">
                    <div class="mb-4 pb-2 border-b border-amber-200/60">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-900">Admin Setup Notes</h3>
                    </div>

                    <div class="space-y-4 text-xs leading-relaxed text-slate-700">
                        <div class="border-b border-amber-200/60 pb-3">
                            <p class="font-extrabold text-slate-900 mb-1">Active Live Portal Rule</p>
                            <p class="text-slate-600">
                                Only <strong>1 conference</strong> can be the active live portal at a time. Checking <em>"Set as Active Live Portal"</em> will automatically deactivate all previous editions on the main homepage.
                            </p>
                        </div>

                        <div class="border-b border-amber-200/60 pb-3">
                            <p class="font-extrabold text-slate-900 mb-1">Hero Banner Best Practices</p>
                            <p class="text-slate-600">
                                Upload a high-resolution horizontal banner (recommended ratio ~16:9, max size <strong>10MB</strong>). This banner will appear as the primary slide in the Hero Section.
                            </p>
                        </div>

                        <div class="border-b border-amber-200/60 pb-3">
                            <p class="font-extrabold text-slate-900 mb-1">Logo Image Guidelines</p>
                            <p class="text-slate-600">
                                Use a clean transparent PNG or SVG logo file (max <strong>2MB</strong>). It will be displayed in the portal header and footer.
                            </p>
                        </div>

                        <div>
                            <p class="font-extrabold text-slate-900 mb-1">Master Data Checklist</p>
                            <p class="text-slate-600 mb-2">After saving conference details, remember to configure its master data under the sidebar menu:</p>
                            <ul class="list-disc pl-4 space-y-1 text-slate-600 font-medium">
                                <li><strong>Speakers</strong> (Keynote & invited speakers)</li>
                                <li><strong>Timeline</strong> (Important dates & agenda)</li>
                                <li><strong>Categories</strong> (Scientific tracks)</li>
                                <li><strong>Committees & Sponsors</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Status Guide Card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                    <div class="mb-3 pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-900">Status Guide</h3>
                    </div>
                    <ul class="space-y-3 text-xs">
                        <li class="flex items-start gap-2">
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-extrabold text-slate-600 shrink-0">Draft</span>
                            <span class="text-slate-600">Events in preparation. Hidden from general public view.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-extrabold text-emerald-700 shrink-0">Active</span>
                            <span class="text-slate-600">Ready for active registrations, payments, and paper submissions.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="rounded bg-amber-50 px-2 py-0.5 text-[10px] font-extrabold text-amber-700 shrink-0">Archived</span>
                            <span class="text-slate-600">Past event kept for historical records & certificates.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>
