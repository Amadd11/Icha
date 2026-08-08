import { onBeforeUnmount, onMounted, ref } from "vue";

export function useCountdown(targetDate) {
    const countdown = ref({
        days: "--",
        hours: "--",
        minutes: "--",
        seconds: "--",
    });

    const updateCountdown = () => {
        const target = new Date(targetDate).getTime();
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

    onMounted(() => {
        updateCountdown();
        const timer = window.setInterval(updateCountdown, 1000);

        onBeforeUnmount(() => {
            window.clearInterval(timer);
        });
    });

    return { countdown };
}
