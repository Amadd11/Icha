<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { countries, getFlagUrl } from '@/Utils/countries';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    countryCode: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Pilih negara...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'update:countryCode', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref(null);
const containerRef = ref(null);
const highlightedIndex = ref(0);

const selectedCountry = computed(() => {
    if (!props.modelValue && !props.countryCode) return null;
    return countries.find(
        (c) =>
            (props.modelValue && c.name.toLowerCase() === props.modelValue.toLowerCase()) ||
            (props.countryCode && c.code.toLowerCase() === props.countryCode.toLowerCase())
    ) || (props.modelValue ? { name: props.modelValue, code: props.countryCode || '' } : null);
});

const filteredCountries = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return countries;
    return countries.filter(
        (c) =>
            c.name.toLowerCase().includes(q) ||
            c.code.toLowerCase().includes(q)
    );
});

watch(isOpen, async (val) => {
    if (val) {
        searchQuery.value = '';
        highlightedIndex.value = 0;
        await nextTick();
        if (searchInput.value) {
            searchInput.value.focus();
        }
    }
});

function toggleDropdown() {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
}

function selectCountry(c) {
    emit('update:modelValue', c.name);
    emit('update:countryCode', c.code);
    emit('change', c);
    isOpen.value = false;
}

function clearSelection(e) {
    e?.stopPropagation();
    emit('update:modelValue', '');
    emit('update:countryCode', '');
    emit('change', null);
}

function handleKeydown(e) {
    if (!isOpen.value) {
        if (e.key === 'Enter' || e.key === 'ArrowDown' || e.key === ' ') {
            e.preventDefault();
            isOpen.value = true;
        }
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (highlightedIndex.value < filteredCountries.value.length - 1) {
            highlightedIndex.value++;
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (highlightedIndex.value > 0) {
            highlightedIndex.value--;
        }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const target = filteredCountries.value[highlightedIndex.value];
        if (target) {
            selectCountry(target);
        }
    } else if (e.key === 'Escape') {
        isOpen.value = false;
    }
}

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
}

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('click', handleClickOutside);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('click', handleClickOutside);
    }
});
</script>

<template>
    <div ref="containerRef" class="relative w-full text-xs" @keydown="handleKeydown">
        <!-- Main Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            :disabled="props.disabled"
            class="w-full flex items-center justify-between gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-left text-xs transition focus:border-purple-600 focus:outline-hidden focus:ring-1 focus:ring-purple-600 hover:border-slate-400 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer shadow-2xs"
            :class="{ 'ring-1 ring-purple-600 border-purple-600': isOpen }"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <template v-if="selectedCountry">
                    <img
                        v-if="selectedCountry.code"
                        :src="getFlagUrl(selectedCountry.code)"
                        :alt="selectedCountry.name"
                        class="h-3.5 w-5 rounded-xs object-cover border border-slate-200 shrink-0 shadow-2xs"
                    />
                    <span class="truncate font-semibold text-slate-900">
                        {{ selectedCountry.name }}
                    </span>
                    <span v-if="selectedCountry.code" class="text-[10px] font-mono text-slate-400 uppercase">
                        ({{ selectedCountry.code }})
                    </span>
                </template>
                <span v-else class="text-slate-400 italic">
                    {{ props.placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1 shrink-0 text-slate-400">
                <button
                    v-if="selectedCountry && !props.disabled"
                    type="button"
                    @click="clearSelection"
                    title="Hapus pilihan negara"
                    class="p-0.5 hover:text-slate-700 rounded-full hover:bg-slate-100 transition cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <svg
                    class="w-4 h-4 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute left-0 right-0 z-50 mt-1.5 max-h-72 w-full rounded-2xl bg-white shadow-xl border border-slate-200 p-2 text-xs animate-fade-in flex flex-col"
        >
            <!-- Search Box -->
            <div class="relative mb-2">
                <svg
                    class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-purple-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    ref="searchInput"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari negara (misal: Iran, Japan, UK)..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-purple-600 focus:outline-hidden focus:ring-1 focus:ring-purple-600 transition"
                    @click.stop
                />
            </div>

            <!-- Country List -->
            <ul class="flex-1 overflow-y-auto space-y-0.5 max-h-56 pr-1 divide-y divide-slate-50">
                <li
                    v-for="(c, idx) in filteredCountries"
                    :key="c.code"
                    @click="selectCountry(c)"
                    @mouseenter="highlightedIndex = idx"
                    class="flex items-center justify-between px-2.5 py-2 rounded-lg cursor-pointer transition"
                    :class="{
                        'bg-purple-50 text-purple-900 font-bold': selectedCountry?.code === c.code,
                        'bg-slate-100/80': highlightedIndex === idx && selectedCountry?.code !== c.code,
                        'text-slate-700': selectedCountry?.code !== c.code,
                    }"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <img
                            :src="getFlagUrl(c.code)"
                            :alt="c.name"
                            loading="lazy"
                            class="h-3.5 w-5 rounded-xs object-cover border border-slate-200 shrink-0 shadow-2xs"
                        />
                        <span class="truncate">{{ c.name }}</span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 font-semibold uppercase shrink-0 ml-2">
                        {{ c.code }}
                    </span>
                </li>

                <!-- Empty State -->
                <li v-if="filteredCountries.length === 0" class="py-6 text-center text-slate-400 italic">
                    Negara "{{ searchQuery }}" tidak ditemukan
                </li>
            </ul>

            <!-- Quick Counter Footer -->
            <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400 px-1">
                <span>Total: {{ filteredCountries.length }} negara</span>
                <span class="italic">Gunakan panah & enter untuk navigasi</span>
            </div>
        </div>
    </div>
</template>
