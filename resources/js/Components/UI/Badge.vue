<script setup>
defineProps({
    color: {
        type: String,
        default: 'bg-slate-100 text-slate-700',
    },
    label: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            color,
        ]"
    >
        {{ label }}
    </span>
</template>
