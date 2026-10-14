<script setup>
defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            total: 0,
            presenter: 0,
            nonPresenter: 0,
            offline: 0,
            online: 0,
        }),
    },
});
</script>

<template>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Paket -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                Total Paket
            </span>
            <span class="text-xl font-black text-slate-900 mt-1 block">
                {{ stats.total }}
            </span>
            <span class="text-[10px] text-slate-400">
                Tersedia di sistem
            </span>
        </div>

        <!-- Paket Presenter -->
        <div class="bg-white p-4 rounded-2xl border border-purple-100 shadow-2xs bg-linear-to-br from-purple-50/40 to-white">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700 block">
                Paket Presenter
            </span>
            <span class="text-xl font-black text-purple-900 mt-1 block">
                {{ stats.presenter }}
            </span>
            <span class="text-[10px] text-purple-600">
                Khusus pemakalah abstrak
            </span>
        </div>

        <!-- Paket Non-Presenter -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-2xs bg-linear-to-br from-emerald-50/40 to-white">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">
                Paket Non-Presenter
            </span>
            <span class="text-xl font-black text-emerald-900 mt-1 block">
                {{ stats.nonPresenter }}
            </span>
            <span class="text-[10px] text-emerald-600">
                Peserta umum & webinar
            </span>
        </div>

        <!-- Format Kehadiran -->
        <div class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs bg-linear-to-br from-sky-50/40 to-white">
            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">
                Format Kehadiran
            </span>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-bold text-slate-800">
                    {{ stats.offline }} On-site
                </span>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-bold text-sky-800">
                    {{ stats.online }} Virtual
                </span>
            </div>
            <span class="text-[10px] text-slate-400">
                Offline & Online
            </span>
        </div>
    </div>
</template>
