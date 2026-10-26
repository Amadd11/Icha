<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    value: {
        type: [Number, String],
        default: null,
    },
    target: {
        type: [Number, String],
        default: null,
    },
    duration: {
        type: Number,
        default: 2000,
    },
    prefix: {
        type: String,
        default: '',
    },
    suffix: {
        type: String,
        default: '',
    },
});

function getNumericTarget() {
    const raw = props.value ?? props.target ?? 0;
    if (typeof raw === 'number') return raw;
    if (!raw) return 0;
    const clean = String(raw).replace(/[^0-9.]/g, '');
    const parsed = parseFloat(clean);
    return isNaN(parsed) ? 0 : parsed;
}

// Initialize with target value directly so it NEVER disappears or shows blank
const targetVal = getNumericTarget();
const displayValue = ref(targetVal);
const elementRef = ref(null);
let animationFrameId = null;

function runAnimation() {
    const target = getNumericTarget();
    if (target === 0) {
        displayValue.value = 0;
        return;
    }

    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }

    displayValue.value = 0;
    const startTime = performance.now();
    const duration = props.duration;

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Smooth Ease Out Cubic for visible, relaxed count-up
        const ease = 1 - Math.pow(1 - progress, 3);
        
        displayValue.value = Math.floor(ease * target);

        if (progress < 1) {
            animationFrameId = requestAnimationFrame(step);
        } else {
            displayValue.value = target;
        }
    }

    animationFrameId = requestAnimationFrame(step);
}

onMounted(() => {
    nextTick(() => {
        runAnimation();
    });
});

watch([() => props.value, () => props.target], () => {
    nextTick(() => {
        runAnimation();
    });
});
</script>

<template>
    <span ref="elementRef" class="tabular-nums inline-block">
        {{ props.prefix }}{{ displayValue.toLocaleString('id-ID') }}{{ props.suffix }}
    </span>
</template>
