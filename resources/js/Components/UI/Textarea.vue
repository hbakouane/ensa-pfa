<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    rows: {
        type: [Number, String],
        default: 4,
    },
    error: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => `textarea-${Math.random().toString(36).slice(2, 9)}`,
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const textareaClasses = computed(() => [
    'block w-full rounded-lg border px-3 py-2 text-sm text-slate-900 shadow-sm transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
    props.error
        ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
        : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200',
]);
</script>

<template>
    <div>
        <label
            v-if="label"
            :for="id"
            class="mb-1.5 block text-sm font-medium text-slate-700"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <textarea
            :id="id"
            :value="modelValue"
            :rows="rows"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :class="textareaClasses"
            @input="emit('update:modelValue', $event.target.value)"
        />

        <p v-if="error" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
