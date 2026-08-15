<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Deletion',
    },
    message: {
        type: String,
        default: 'Are you sure you want to delete this item? This action will move the data to trash.',
    },
    itemName: {
        type: String,
        default: '',
    },
    confirmText: {
        type: String,
        default: 'Yes, Delete',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
                @click.self="!loading && emit('close')"
            >
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="show"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-200 text-xs text-slate-700"
                        role="dialog"
                        aria-modal="true"
                    >
                        <!-- Modal Header with Red Danger Icon -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ title }}</h3>
                                <p class="text-xs text-slate-500 font-medium">Permanent or soft delete action</p>
                            </div>
                        </div>

                        <!-- Highlighted Item Box (if itemName provided) -->
                        <div v-if="itemName" class="my-3.5 rounded-xl bg-red-50/60 border border-red-100 p-3 text-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-red-600 block mb-0.5">Item to be removed:</span>
                            <span class="font-bold text-slate-900 break-all text-xs">{{ itemName }}</span>
                        </div>

                        <!-- Body Message -->
                        <p class="text-slate-500 mb-5 leading-relaxed">
                            {{ message }}
                        </p>

                        <!-- Action Footer -->
                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                :disabled="loading"
                                @click="emit('close')"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer disabled:opacity-50"
                            >
                                {{ cancelText }}
                            </button>
                            <button
                                type="button"
                                :disabled="loading"
                                @click="emit('confirm')"
                                class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-2 font-bold transition disabled:opacity-50 cursor-pointer shadow-xs inline-flex items-center gap-1.5"
                            >
                                <svg v-if="loading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ loading ? 'Deleting...' : confirmText }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
