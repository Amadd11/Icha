<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const type = ref('success'); // 'success' | 'error' | 'info'
const title = ref('');
const message = ref('');
let timer = null;

function showToast(toastType, toastTitle, toastMessage) {
    if (timer) clearTimeout(timer);

    type.value = toastType;
    title.value = toastTitle;
    message.value = toastMessage;
    visible.value = true;

    timer = setTimeout(() => {
        visible.value = false;
    }, 4500);
}

function closeToast() {
    visible.value = false;
    if (timer) clearTimeout(timer);
}

function checkFlash() {
    const flash = page.props.flash;
    if (!flash) return;

    if (flash.success) {
        showToast('success', 'Berhasil', flash.success);
    } else if (flash.error) {
        showToast('error', 'Gagal', flash.error);
    } else if (flash.message) {
        showToast('info', 'Pemberitahuan', flash.message);
    }
}

watch(
    () => page.props.flash,
    () => {
        checkFlash();
    },
    { deep: true }
);

onMounted(() => {
    checkFlash();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed top-5 right-5 z-[9999] flex max-w-sm w-full items-start gap-3 rounded-2xl bg-white p-4 shadow-2xl border border-slate-100 ring-1 ring-black/5"
            >
                <!-- Icon -->
                <div
                    class="h-9 w-9 shrink-0 rounded-xl flex items-center justify-center text-base font-bold shadow-xs"
                    :class="{
                        'bg-emerald-50 text-emerald-600 border border-emerald-200': type === 'success',
                        'bg-rose-50 text-rose-600 border border-rose-200': type === 'error',
                        'bg-purple-50 text-purple-700 border border-purple-200': type === 'info',
                    }"
                >
                    <span v-if="type === 'success'">✓</span>
                    <span v-else-if="type === 'error'">✕</span>
                    <span v-else>ℹ</span>
                </div>

                <!-- Text Content -->
                <div class="flex-1 pt-0.5 min-w-0">
                    <h4
                        class="text-xs font-black uppercase tracking-wider leading-tight"
                        :class="{
                            'text-emerald-700': type === 'success',
                            'text-rose-700': type === 'error',
                            'text-purple-900': type === 'info',
                        }"
                    >
                        {{ title }}
                    </h4>
                    <p class="mt-1 text-xs font-semibold text-slate-700 leading-relaxed break-words">
                        {{ message }}
                    </p>
                </div>

                <!-- Close Button -->
                <button
                    @click="closeToast"
                    class="h-6 w-6 shrink-0 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center text-xs font-bold transition cursor-pointer"
                >
                    ✕
                </button>
            </div>
        </Transition>
    </Teleport>
</template>
