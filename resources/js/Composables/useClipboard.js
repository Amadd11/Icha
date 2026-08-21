import { ref } from 'vue';

export function useClipboard() {
    const isCopied = ref(false);
    const copiedKey = ref(null);
    let timeoutId = null;

    async function copy(text, duration = 2000) {
        if (!text) return false;

        try {
            if (navigator?.clipboard?.writeText) {
                await navigator.clipboard.writeText(String(text));
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = String(text);
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }

            isCopied.value = true;

            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                isCopied.value = false;
            }, duration);

            return true;
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            return false;
        }
    }

    async function copyItem(key, text, duration = 2000) {
        const success = await copy(text, duration);
        if (success) {
            copiedKey.value = key;
            setTimeout(() => {
                if (copiedKey.value === key) {
                    copiedKey.value = null;
                }
            }, duration);
        }
        return success;
    }

    return {
        isCopied,
        copiedKey,
        copy,
        copyItem,
    };
}
