<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();

const flash = computed(() => page.props.flash ?? {});

const visibleMessages = ref([]);

function addMessage(type, text) {
    const id = Date.now() + Math.random();
    visibleMessages.value.push({ id, type, text });

    setTimeout(() => {
        dismiss(id);
    }, 5000);
}

function dismiss(id) {
    visibleMessages.value = visibleMessages.value.filter((m) => m.id !== id);
}

watch(
    flash,
    (val) => {
        if (val.success) addMessage('success', val.success);
        if (val.warning) addMessage('warning', val.warning);
        if (val.error) addMessage('error', val.error);
    },
    { immediate: true },
);

const typeClasses = {
    success: 'bg-emerald-50 border-emerald-400 text-emerald-800',
    warning: 'bg-amber-50 border-amber-400 text-amber-800',
    error: 'bg-red-50 border-red-400 text-red-800',
};

const iconPaths = {
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z',
    error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
};
</script>

<template>
    <div class="fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-3">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <div
                v-for="msg in visibleMessages"
                :key="msg.id"
                :class="[
                    'flex items-start gap-3 rounded-lg border px-4 py-3 shadow-lg',
                    typeClasses[msg.type],
                ]"
            >
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :d="iconPaths[msg.type]"
                    />
                </svg>

                <p class="flex-1 text-sm font-medium">{{ msg.text }}</p>

                <button
                    class="shrink-0 opacity-60 hover:opacity-100"
                    @click="dismiss(msg.id)"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
