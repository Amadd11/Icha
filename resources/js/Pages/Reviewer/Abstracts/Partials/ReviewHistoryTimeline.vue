<script setup>
const props = defineProps({
    history: {
        type: Array,
        default: () => [],
    },
});

const RECOMMENDATION_GROUPS = {
    accept: ['ORAL', 'POSTER', 'ACCEPT', 'ACCEPTED'],
    revision: ['REVISION', 'REVISION_REQUIRED'],
    reject: ['REJECT', 'REJECTED'],
};

function getRecommendationCategory(rec) {
    const upper = (rec || '').toUpperCase();
    for (const [cat, values] of Object.entries(RECOMMENDATION_GROUPS)) {
        if (values.includes(upper)) return cat;
    }
    return 'other';
}

function getRecommendationStyle(rec) {
    const styles = {
        accept:   { cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500', label: 'Accepted' },
        revision: { cls: 'bg-amber-50 text-amber-700 border-amber-200',       dot: 'bg-amber-500',   label: 'Revision' },
        reject:   { cls: 'bg-rose-50 text-rose-700 border-rose-200',          dot: 'bg-rose-500',    label: 'Rejected' },
        other:    { cls: 'bg-purple-50 text-purple-700 border-purple-100',     dot: 'bg-purple-500',  label: rec || 'Reviewed' },
    };
    return styles[getRecommendationCategory(rec)] || styles.other;
}
</script>

<template>
    <div v-if="history && history.length" class="mt-4 pt-4 border-t border-slate-200 space-y-2.5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <span>📜</span> Riwayat Telaah Sebelumnya
            </span>
            <span class="text-[10px] text-slate-400 font-semibold">{{ history.length }} telaah terdahulu</span>
        </div>

        <div class="space-y-2">
            <div
                v-for="(hist, hIdx) in history"
                :key="hIdx"
                class="p-3 rounded-2xl border border-slate-200/90 bg-white space-y-1.5 shadow-2xs"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-900">
                            {{ hist.round_number === 1 ? 'Telaah Awal' : 'Revisi ' + (hist.round_number - 1) }}
                        </span>
                        <span v-if="hist.review?.updated_at" class="text-[10px] text-slate-400 font-medium">
                            • {{ hist.review.updated_at }}
                        </span>
                    </div>
                    <span
                        v-if="hist.review"
                        :class="[
                            'inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full border',
                            getRecommendationStyle(hist.review.recommendation).cls
                        ]"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="getRecommendationStyle(hist.review.recommendation).dot"></span>
                        {{ getRecommendationCategory(hist.review.recommendation) === 'accept' ? 'Accept (' + hist.review.recommendation + ')' : getRecommendationStyle(hist.review.recommendation).label }}
                    </span>
                    <span v-else class="text-[10px] text-slate-400 italic">Belum dinilai</span>
                </div>

                <div v-if="hist.review" class="space-y-1.5">
                    <div class="flex items-center gap-3 text-xs text-slate-600 font-medium">
                        <span>Skor: <strong class="text-purple-900 font-bold">{{ hist.review.total_score }}/10</strong></span>
                        <span class="text-slate-300">|</span>
                        <span class="text-[11px] text-slate-500">K1: {{ hist.review.score_criteria_1 }}/5</span>
                        <span class="text-slate-300">|</span>
                        <span class="text-[11px] text-slate-500">K2: {{ hist.review.score_criteria_2 }}/5</span>
                    </div>
                    <div v-if="hist.review.summary" class="bg-slate-50 rounded-xl p-2.5 text-xs text-slate-700 border border-slate-200/80 leading-relaxed whitespace-pre-wrap">
                        <span class="font-bold text-slate-800 block text-[10px] uppercase tracking-wider mb-0.5">Catatan {{ hist.round_number === 1 ? 'Telaah Awal' : 'Revisi ' + (hist.round_number - 1) }}:</span>
                        {{ hist.review.summary }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
