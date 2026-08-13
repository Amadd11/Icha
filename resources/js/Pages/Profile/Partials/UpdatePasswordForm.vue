<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};

const inputClass = 'block w-full text-xs rounded-xl border border-slate-300 bg-slate-50/50 py-2.5 pl-3.5 pr-12 transition hover:border-slate-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20';
</script>

<template>
    <section>
        <form @submit.prevent="updatePassword" class="space-y-4">
            <!-- Current Password -->
            <div>
                <InputLabel for="current_password" value="Current Password" class="text-xs font-bold text-slate-700 mb-1" />
                <div class="relative">
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        :class="inputClass"
                        autocomplete="current-password"
                        placeholder="Enter your current password"
                    />
                    <!-- Vertical Divider -->
                    <span class="absolute inset-y-2 right-10 w-px bg-slate-200"></span>
                    <!-- Toggle button -->
                    <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute inset-y-1.5 right-1.5 flex items-center justify-center w-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
                        title="Toggle password visibility"
                    >
                        <svg v-if="!showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.current_password" class="mt-1" />
            </div>

            <!-- New & Confirm Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="password" value="New Password" class="text-xs font-bold text-slate-700 mb-1" />
                    <div class="relative">
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showNewPassword ? 'text' : 'password'"
                            :class="inputClass"
                            autocomplete="new-password"
                            placeholder="Min. 8 characters"
                        />
                        <!-- Vertical Divider -->
                        <span class="absolute inset-y-2 right-10 w-px bg-slate-200"></span>
                        <!-- Toggle button -->
                        <button
                            type="button"
                            @click="showNewPassword = !showNewPassword"
                            class="absolute inset-y-1.5 right-1.5 flex items-center justify-center w-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
                            title="Toggle password visibility"
                        >
                            <svg v-if="!showNewPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirm New Password" class="text-xs font-bold text-slate-700 mb-1" />
                    <div class="relative">
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            :class="inputClass"
                            autocomplete="new-password"
                            placeholder="Re-enter new password"
                        />
                        <!-- Vertical Divider -->
                        <span class="absolute inset-y-2 right-10 w-px bg-slate-200"></span>
                        <!-- Toggle button -->
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-1.5 right-1.5 flex items-center justify-center w-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
                            title="Toggle password visibility"
                        >
                            <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" class="mt-1" />
                </div>
            </div>

            <!-- Submit button -->
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs px-7 py-3 shadow-md transition cursor-pointer disabled:opacity-50"
                >
                    <span>{{ form.processing ? 'Updating Password...' : 'Update Password' }}</span>
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0 translate-x-2"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs text-emerald-700 font-extrabold bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200"
                    >
                        Password updated successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
