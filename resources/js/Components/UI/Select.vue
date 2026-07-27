<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        required: true,
        // Each item: { value: string|number, label: string }
    },
    error: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Select an option',
    },
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).slice(2, 9)}`,
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

const selectClasses = computed(() => [
    'block w-full rounded-lg border px-3 py-2 text-sm text-slate-900 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
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

        <select
            :id="id"
            :value="modelValue"
            :required="required"
            :disabled="disabled"
            :class="selectClasses"
            @change="emit('update:modelValue', $event.target.value)"
        >
            <option value="" disabled>{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <p v-if="error" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
