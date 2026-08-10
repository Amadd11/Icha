<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    { code: 'MD', name: 'Moldova' },
    { code: 'MN', name: 'Mongolia' },
    { code: 'MA', name: 'Morocco' },
    { code: 'MZ', name: 'Mozambique' },
    { code: 'MM', name: 'Myanmar' },
    { code: 'NP', name: 'Nepal' },
    { code: 'NL', name: 'Netherlands' },
    { code: 'NZ', name: 'New Zealand' },
    { code: 'NG', name: 'Nigeria' },
    { code: 'KP', name: 'North Korea' },
    { code: 'NO', name: 'Norway' },
    { code: 'OM', name: 'Oman' },
    { code: 'PK', name: 'Pakistan' },
    { code: 'PS', name: 'Palestine' },
    { code: 'PA', name: 'Panama' },
    { code: 'PY', name: 'Paraguay' },
    { code: 'PE', name: 'Peru' },
    { code: 'PH', name: 'Philippines' },
    { code: 'PL', name: 'Poland' },
    { code: 'PT', name: 'Portugal' },
    { code: 'QA', name: 'Qatar' },
    { code: 'RO', name: 'Romania' },
    { code: 'RU', name: 'Russia' },
    { code: 'RW', name: 'Rwanda' },
    { code: 'SA', name: 'Saudi Arabia' },
    { code: 'SN', name: 'Senegal' },
    { code: 'RS', name: 'Serbia' },
    { code: 'SG', name: 'Singapore' },
    { code: 'SK', name: 'Slovakia' },
    { code: 'SI', name: 'Slovenia' },
    { code: 'ZA', name: 'South Africa' },
    { code: 'KR', name: 'South Korea' },
    { code: 'ES', name: 'Spain' },
    { code: 'LK', name: 'Sri Lanka' },
    { code: 'SD', name: 'Sudan' },
    { code: 'SE', name: 'Sweden' },
    { code: 'CH', name: 'Switzerland' },
    { code: 'SY', name: 'Syria' },
    { code: 'TW', name: 'Taiwan' },
    { code: 'TJ', name: 'Tajikistan' },
    { code: 'TZ', name: 'Tanzania' },
    { code: 'TH', name: 'Thailand' },
    { code: 'TN', name: 'Tunisia' },
    { code: 'TR', name: 'Turkey' },
    { code: 'TM', name: 'Turkmenistan' },
    { code: 'UG', name: 'Uganda' },
    { code: 'UA', name: 'Ukraine' },
    { code: 'AE', name: 'United Arab Emirates' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'US', name: 'United States' },
    { code: 'UY', name: 'Uruguay' },
    { code: 'UZ', name: 'Uzbekistan' },
    { code: 'VE', name: 'Venezuela' },
    { code: 'VN', name: 'Vietnam' },
    { code: 'YE', name: 'Yemen' },
    { code: 'ZM', name: 'Zambia' },
    { code: 'ZW', name: 'Zimbabwe' },
];

/* ── Country search & selection ── */
const countrySearch = ref('');
const showCountryDropdown = ref(false);

const filteredCountries = computed(() => {
    const q = countrySearch.value.toLowerCase().trim();
    if (!q) return countries;
    return countries.filter(c =>
        c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q)
    );
});

const selectedCountryDisplay = computed(() => {
    if (form.country_code && form.country) {
        return form.country;
    }
    return '';
});

function selectCountry(country) {
    form.country = country.name;
    form.country_code = country.code;
    countrySearch.value = '';
    showCountryDropdown.value = false;
}

function clearCountry() {
    form.country = '';
    form.country_code = '';
    countrySearch.value = '';
}

function onCountryFocus() {
    showCountryDropdown.value = true;
}

function onCountryBlur() {
    // Small delay so click on dropdown option registers first
    setTimeout(() => {
        showCountryDropdown.value = false;
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
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('admin.speakers.update', props.speaker.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('admin.speakers.store'), { forceFormData: true });
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Speaker' : 'New Speaker'" />
    <AdminLayout>
        <div class="mb-6 flex items-center gap-2">
            <Link :href="route('admin.speakers.index')" class="text-sm font-semibold text-slate-500 hover:text-indigo-600">Speakers</Link>
            <span class="text-slate-300">/</span>
            <h1 class="text-lg font-bold text-slate-800">{{ isEdit ? 'Edit' : 'New' }} Speaker</h1>
        </div>

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

                        <!-- Country (Searchable Dropdown) -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Country</label>

                            <!-- Selected country display -->
                            <div v-if="form.country_code" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5">
                                <img
                                    :src="`https://flagcdn.com/w40/${form.country_code.toLowerCase()}.png`"
                                    :alt="form.country"
                                    class="h-5 w-5 rounded-full object-cover shadow-sm"
                                />
                                <span class="flex-1 text-sm font-medium text-slate-700">{{ form.country }}</span>
                                <span class="text-xs text-slate-400">{{ form.country_code }}</span>
                                <button
                                    type="button"
                                    @click="clearCountry"
                                    class="ml-1 rounded-md p-0.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
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
                                    class="absolute z-20 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"
                                >
                                    <button
                                        v-for="country in filteredCountries"
                                        :key="country.code"
                                        type="button"
                                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-primary/5"
                                        @mousedown.prevent="selectCountry(country)"
                                    >
                                        <img
                                            :src="`https://flagcdn.com/w40/${country.code.toLowerCase()}.png`"
                                            :alt="country.name"
                                            class="h-4 w-6 rounded-sm object-cover shadow-sm"
                                        />
                                        <span class="flex-1 text-slate-700">{{ country.name }}</span>
                                        <span class="text-xs text-slate-400">{{ country.code }}</span>
                                    </button>
                                    <div v-if="filteredCountries.length === 0" class="px-4 py-3 text-sm text-slate-400 text-center">
                                        No countries found
                                    </div>
                                </div>
                            </div>

                            <p v-if="form.errors.country" class="mt-1 text-xs text-red-500">{{ form.errors.country }}</p>
                            <p v-if="form.errors.country_code" class="mt-1 text-xs text-red-500">{{ form.errors.country_code }}</p>
                        </div>

                        <!-- Order -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Display Order</label>
                            <input v-model.number="form.order" type="number" min="0" class="admin-input" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input v-model="form.email" type="email" class="admin-input" placeholder="speaker@email.com" />
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
    </AdminLayout>
</template>
