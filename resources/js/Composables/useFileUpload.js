import { ref, computed, onUnmounted } from 'vue';

export function useFileUpload(options = {}) {
    const {
        maxSizeMb = 10,
        allowedTypes = [], // e.g. ['.pdf', '.png', '.jpg', '.jpeg', '.doc', '.docx']
        initialPreview = null,
    } = options;

    const file = ref(null);
    const fileName = ref('');
    const previewUrl = ref(initialPreview);
    const error = ref('');

    const isImage = computed(() => {
        if (!file.value && previewUrl.value) {
            return /\.(png|jpg|jpeg|webp|gif|svg)$/i.test(previewUrl.value);
        }
        return file.value ? file.value.type.startsWith('image/') : false;
    });

    const isPdf = computed(() => {
        if (!file.value && previewUrl.value) {
            return /\.pdf$/i.test(previewUrl.value);
        }
        return file.value ? file.value.type === 'application/pdf' : false;
    });

    const fileSizeFormatted = computed(() => {
        if (!file.value) return '';
        const bytes = file.value.size;
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    });

    function validateFile(selectedFile) {
        error.value = '';

        if (!selectedFile) return false;

        // Size check
        const maxBytes = maxSizeMb * 1024 * 1024;
        if (selectedFile.size > maxBytes) {
            error.value = `File size exceeds the ${maxSizeMb}MB limit (${(selectedFile.size / (1024 * 1024)).toFixed(1)}MB).`;
            return false;
        }

        // Type / Extension check
        if (allowedTypes.length > 0) {
            const ext = '.' + selectedFile.name.split('.').pop().toLowerCase();
            const mime = selectedFile.type.toLowerCase();

            const isAllowedExt = allowedTypes.some(t => t.toLowerCase() === ext);
            const isAllowedMime = allowedTypes.some(t => mime.includes(t.replace('*', '').replace('.', '')));

            if (!isAllowedExt && !isAllowedMime) {
                error.value = `File type not supported. Allowed formats: ${allowedTypes.join(', ')}`;
                return false;
            }
        }

        return true;
    }

    function handleFileChange(e) {
        const selected = e.target?.files ? e.target.files[0] : e;
        if (!selected) return;

        if (!validateFile(selected)) {
            clearFile();
            return;
        }

        // Clean previous object URL
        if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
            URL.revokeObjectURL(previewUrl.value);
        }

        file.value = selected;
        fileName.value = selected.name;

        if (selected.type.startsWith('image/') || selected.type === 'application/pdf') {
            previewUrl.value = URL.createObjectURL(selected);
        } else {
            previewUrl.value = null;
        }
    }

    function clearFile() {
        if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
            URL.revokeObjectURL(previewUrl.value);
        }
        file.value = null;
        fileName.value = '';
        previewUrl.value = initialPreview;
        error.value = '';
    }

    onUnmounted(() => {
        if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
            URL.revokeObjectURL(previewUrl.value);
        }
    });

    return {
        file,
        fileName,
        previewUrl,
        error,
        isImage,
        isPdf,
        fileSizeFormatted,
        handleFileChange,
        clearFile,
        validateFile,
    };
}
