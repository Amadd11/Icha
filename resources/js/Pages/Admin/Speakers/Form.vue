<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    speaker: Object,
    conferences: Array,
});

const isEdit = !!props.speaker;

function formatStorageUrl(path) {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path;
}

const photoPreview = ref(formatStorageUrl(props.speaker?.photo));

const form = useForm({
    conference_id: props.speaker?.conference_id ?? '',
    name:          props.speaker?.name          ?? '',
    title:         props.speaker?.title         ?? '',
    institution:   props.speaker?.institution   ?? '',
    country:       props.speaker?.country       ?? '',
    country_code:  props.speaker?.country_code  ?? '',
    bio:           props.speaker?.bio           ?? '',
    email:         props.speaker?.email         ?? '',
    type:          props.speaker?.type          ?? 'invited',
    order:         props.speaker?.order         ?? 0,
    photo:         null,
});

/* ── Country list with ISO codes ── */
const countries = [
    { code: 'AF', name: 'Afghanistan' },
    { code: 'AL', name: 'Albania' },
    { code: 'DZ', name: 'Algeria' },
    { code: 'AR', name: 'Argentina' },
    { code: 'AM', name: 'Armenia' },
    { code: 'AU', name: 'Australia' },
    { code: 'AT', name: 'Austria' },
    { code: 'AZ', name: 'Azerbaijan' },
    { code: 'BH', name: 'Bahrain' },
    { code: 'BD', name: 'Bangladesh' },
    { code: 'BY', name: 'Belarus' },
    { code: 'BE', name: 'Belgium' },
    { code: 'BJ', name: 'Benin' },
    { code: 'BO', name: 'Bolivia' },
    { code: 'BA', name: 'Bosnia and Herzegovina' },
    { code: 'BW', name: 'Botswana' },
    { code: 'BR', name: 'Brazil' },
    { code: 'BN', name: 'Brunei' },
    { code: 'BG', name: 'Bulgaria' },
    { code: 'BF', name: 'Burkina Faso' },
    { code: 'KH', name: 'Cambodia' },
    { code: 'CM', name: 'Cameroon' },
    { code: 'CA', name: 'Canada' },
    { code: 'CL', name: 'Chile' },
    { code: 'CN', name: 'China' },
    { code: 'CO', name: 'Colombia' },
    { code: 'CR', name: 'Costa Rica' },
    { code: 'HR', name: 'Croatia' },
    { code: 'CU', name: 'Cuba' },
    { code: 'CY', name: 'Cyprus' },
    { code: 'CZ', name: 'Czech Republic' },
    { code: 'DK', name: 'Denmark' },
    { code: 'EC', name: 'Ecuador' },
    { code: 'EG', name: 'Egypt' },
    { code: 'EE', name: 'Estonia' },
    { code: 'ET', name: 'Ethiopia' },
    { code: 'FI', name: 'Finland' },
    { code: 'FR', name: 'France' },
    { code: 'GE', name: 'Georgia' },
    { code: 'DE', name: 'Germany' },
    { code: 'GH', name: 'Ghana' },
    { code: 'GR', name: 'Greece' },
    { code: 'GT', name: 'Guatemala' },
    { code: 'HK', name: 'Hong Kong' },
    { code: 'HU', name: 'Hungary' },
    { code: 'IS', name: 'Iceland' },
    { code: 'IN', name: 'India' },
    { code: 'ID', name: 'Indonesia' },
    { code: 'IR', name: 'Iran' },
    { code: 'IQ', name: 'Iraq' },
    { code: 'IE', name: 'Ireland' },
    { code: 'IL', name: 'Israel' },
    { code: 'IT', name: 'Italy' },
    { code: 'JM', name: 'Jamaica' },
    { code: 'JP', name: 'Japan' },
    { code: 'JO', name: 'Jordan' },
    { code: 'KZ', name: 'Kazakhstan' },
    { code: 'KE', name: 'Kenya' },
    { code: 'KW', name: 'Kuwait' },
    { code: 'KG', name: 'Kyrgyzstan' },
    { code: 'LA', name: 'Laos' },
    { code: 'LV', name: 'Latvia' },
    { code: 'LB', name: 'Lebanon' },
    { code: 'LY', name: 'Libya' },
    { code: 'LT', name: 'Lithuania' },
    { code: 'LU', name: 'Luxembourg' },
    { code: 'MO', name: 'Macao' },
    { code: 'MY', name: 'Malaysia' },
    { code: 'MV', name: 'Maldives' },
    { code: 'MT', name: 'Malta' },
    { code: 'MX', name: 'Mexico' },
    { code: 'MN', name: 'Mongolia' },
    { code: 'MA', name: 'Morocco' },
    { code: 'NP', name: 'Nepal' },
    { code: 'NL', name: 'Netherlands' },
    { code: 'NZ', name: 'New Zealand' },
    { code: 'NG', name: 'Nigeria' },
    { code: 'NO', name: 'Norway' },
    { code: 'OM', name: 'Oman' },
    { code: 'PK', name: 'Pakistan' },
    { code: 'PS', name: 'Palestine' },
    { code: 'PA', name: 'Panama' },
    { code: 'PE', name: 'Peru' },
    { code: 'PH', name: 'Philippines' },
    { code: 'PL', name: 'Poland' },
    { code: 'PT', name: 'Portugal' },
    { code: 'QA', name: 'Qatar' },
    { code: 'RO', name: 'Romania' },
    { code: 'RU', name: 'Russia' },
    { code: 'SA', name: 'Saudi Arabia' },
    { code: 'SG', name: 'Singapore' },
    { code: 'ZA', name: 'South Africa' },
    { code: 'KR', name: 'South Korea' },
    { code: 'ES', name: 'Spain' },
    { code: 'LK', name: 'Sri Lanka' },
    { code: 'SE', name: 'Sweden' },
    { code: 'CH', name: 'Switzerland' },
    { code: 'TW', name: 'Taiwan' },
    { code: 'TH', name: 'Thailand' },
    { code: 'TR', name: 'Turkey' },
    { code: 'UA', name: 'Ukraine' },
    { code: 'AE', name: 'United Arab Emirates' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'US', name: 'United States' },
    { code: 'VN', name: 'Vietnam' },
];

const countrySearch = ref('');
const isCountryFocused = ref(false);

const filteredCountries = computed(() => {
    if (!countrySearch.value) return countries;
    const q = countrySearch.value.toLowerCase();
    return countries.filter(c => c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q));
});

const showCountryDropdown = computed(() => {
    return isCountryFocused.value && !form.country_code;
});

function selectCountry(c) {
    form.country = c.name;
    form.country_code = c.code;
    countrySearch.value = '';
    isCountryFocused.value = false;
}

function clearCountry() {
    form.country = '';
    form.country_code = '';
    countrySearch.value = '';
}

function onCountryFocus() {
    isCountryFocused.value = true;
}

function onCountryBlur() {
    setTimeout(() => {
        isCountryFocused.value = false;
    }, 200);
}

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
            forceFormData: true,
            _method: 'put',
        });
    } else {
        form.post(route('admin.speakers.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Speaker' : 'Add Speaker'" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Row -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                        <Link :href="route('admin.speakers.index')" class="hover:text-purple-700">Speakers</Link>
                        <span>/</span>
                        <span class="text-slate-800 font-bold">{{ isEdit ? 'Edit Speaker' : 'New Speaker' }}</span>
                    </div>
                    <h1 class="text-xl font-bold text-slate-900">{{ isEdit ? 'Edit Speaker Details' : 'Add New Speaker' }}</h1>
                </div>
            </div>

            <!-- Form Card -->
            <div class="max-w-3xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 space-y-6">
                        
                        <!-- Photo Upload Preview -->
                        <div class="flex items-center gap-5 pb-4 border-b border-slate-100">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center">
                                <img v-if="photoPreview" :src="photoPreview" class="h-full w-full object-cover" />
                                <span v-else class="text-xs font-bold text-slate-400">Photo</span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Speaker Photo</label>
                                <input type="file" accept="image/*" @change="onPhotoChange" class="text-xs text-slate-600 file:mr-3 file:rounded-lg file:border file:border-slate-200 file:bg-slate-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" />
                                <p class="mt-1 text-[11px] text-slate-400">Max 2MB (JPEG, PNG, WebP)</p>
                                <p v-if="form.errors.photo" class="mt-1 text-xs text-red-500">{{ form.errors.photo }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 text-xs">
                            <!-- Conference -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Conference <span class="text-red-500">*</span></label>
                                <select v-model="form.conference_id" class="admin-input font-semibold" required>
                                    <option value="">Select conference</option>
                                    <option v-for="c in conferences" :key="c.id" :value="c.id">{{ c.title }}</option>
                                </select>
                                <p v-if="form.errors.conference_id" class="mt-1 text-xs text-red-500">{{ form.errors.conference_id }}</p>
                            </div>

                            <!-- Full Name -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Full Name & Degree <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" class="admin-input" placeholder="e.g. Prof. Dr. John Doe, M.Sc." required />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Academic Title</label>
                                <input v-model="form.title" type="text" class="admin-input" placeholder="e.g. Professor / Keynote Lecturer" />
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Speaker Category <span class="text-red-500">*</span></label>
                                <select v-model="form.type" class="admin-input font-semibold" required>
                                    <option value="keynote">Keynote Speaker</option>
                                    <option value="plenary">Plenary Speaker</option>
                                    <option value="invited">Invited Speaker</option>
                                </select>
                            </div>

                            <!-- Institution -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Institution / University</label>
                                <input v-model="form.institution" type="text" class="admin-input" placeholder="e.g. Universitas Muhammadiyah Surakarta" />
                            </div>

                            <!-- Country (Searchable Dropdown) -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Country</label>

                                <!-- Selected country display -->
                                <div v-if="form.country_code" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5">
                                    <img
                                        :src="`https://flagcdn.com/w40/${form.country_code.toLowerCase()}.png`"
                                        :alt="form.country"
                                        class="h-4 w-5 rounded-xs object-cover"
                                    />
                                    <span class="flex-1 font-bold text-slate-800">{{ form.country }}</span>
                                    <span class="text-xs text-slate-400 font-semibold">{{ form.country_code }}</span>
                                    <button
                                        type="button"
                                        @click="clearCountry"
                                        class="ml-1 rounded-md p-1 text-slate-400 hover:text-slate-600 transition cursor-pointer"
                                    >
                                        ✕
                                    </button>
                                </div>

                                <!-- Search input -->
                                <div v-else class="relative">
                                    <input
                                        v-model="countrySearch"
                                        type="text"
                                        class="admin-input"
                                        placeholder="Search country..."
                                        @focus="onCountryFocus"
                                        @blur="onCountryBlur"
                                    />

                                    <!-- Dropdown list -->
                                    <div
                                        v-if="showCountryDropdown"
                                        class="absolute z-20 mt-1 w-full max-h-48 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-md"
                                    >
                                        <button
                                            v-for="country in filteredCountries"
                                            :key="country.code"
                                            type="button"
                                            class="flex w-full items-center gap-3 px-4 py-2 text-left text-xs font-semibold hover:bg-slate-50"
                                            @mousedown.prevent="selectCountry(country)"
                                        >
                                            <img
                                                :src="`https://flagcdn.com/w40/${country.code.toLowerCase()}.png`"
                                                :alt="country.name"
                                                class="h-3.5 w-5 rounded-xs object-cover"
                                            />
                                            <span class="flex-1 text-slate-700">{{ country.name }}</span>
                                            <span class="text-[11px] text-slate-400">{{ country.code }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Display Order -->
                            <div>
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Display Order Sequence</label>
                                <input v-model.number="form.order" type="number" min="0" class="admin-input font-bold" />
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Email Address</label>
                                <input v-model="form.email" type="email" class="admin-input" placeholder="speaker@email.com" />
                            </div>

                            <!-- Bio -->
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block font-bold uppercase tracking-wider text-slate-700">Short Biography</label>
                                <textarea v-model="form.bio" rows="3" class="admin-input" placeholder="Brief academic profile or key research focus..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('admin.speakers.index')" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-gold hover:bg-amber-400 text-slate-950 font-bold px-6 py-2.5 text-xs transition disabled:opacity-50 cursor-pointer shadow-xs"
                        >
                            {{ form.processing ? 'Saving...' : (isEdit ? 'Update Speaker' : 'Save Speaker') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
