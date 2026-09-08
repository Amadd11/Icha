import { onBeforeUnmount, onMounted, ref, unref } from "vue";

export function useCountdown(targetDate) {
    const countdown = ref({
        days: "--",
        hours: "--",
        minutes: "--",
        seconds: "--",
    });

    const updateCountdown = () => {
        const rawDate = unref(targetDate);
        if (!rawDate) return;

        const target = new Date(rawDate).getTime();
        if (isNaN(target)) return;

        const diff = target - Date.now();

        if (diff <= 0) {
            countdown.value = {
                days: "0",
                hours: "0",
                minutes: "0",
                seconds: "0",
            };
            return;
        }

        countdown.value = {
            days: String(Math.floor(diff / 86400000)),
            hours: String(Math.floor((diff % 86400000) / 3600000)),
            minutes: String(Math.floor((diff % 3600000) / 60000)),
            seconds: String(Math.floor((diff % 60000) / 1000)),
        };
    };

    let timer = null;

    onMounted(() => {
        updateCountdown();
        timer = window.setInterval(updateCountdown, 1000);
    });

    onBeforeUnmount(() => {
        if (timer) {
            window.clearInterval(timer);
        }
    });

    return { countdown };
}

