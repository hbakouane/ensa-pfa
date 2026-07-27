<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const textareaRef = ref(null);

const placeholders = [
    { tag: '{{candidate_name}}', label: 'Candidate Name' },
    { tag: '{{position_title}}', label: 'Position Title' },
    { tag: '{{salary}}', label: 'Salary' },
    { tag: '{{start_date}}', label: 'Start Date' },
    { tag: '{{company_name}}', label: 'Company Name' },
    { tag: '{{hiring_manager}}', label: 'Hiring Manager' },
];

function insertPlaceholder(tag) {
    const textarea = textareaRef.value;
    if (!textarea) {
        emit('update:modelValue', props.modelValue + tag);
        return;
    }

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const before = props.modelValue.slice(0, start);
    const after = props.modelValue.slice(end);
    const newValue = before + tag + after;

    emit('update:modelValue', newValue);

    // Restore cursor position after the inserted tag
    requestAnimationFrame(() => {
        textarea.focus();
        const cursorPos = start + tag.length;
        textarea.setSelectionRange(cursorPos, cursorPos);
    });
}

function onInput(event) {
    emit('update:modelValue', event.target.value);
}
</script>

<template>
    <div>
        <!-- Placeholder chips -->
        <div class="mb-3 rounded-lg bg-slate-50 p-3">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Insert Placeholder
            </p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="placeholder in placeholders"
                    :key="placeholder.tag"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 transition-colors hover:border-indigo-300 hover:bg-indigo-100"
                    @click="insertPlaceholder(placeholder.tag)"
                >
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ placeholder.label }}
                </button>
            </div>
        </div>

        <!-- Textarea -->
        <textarea
            ref="textareaRef"
            :value="modelValue"
            rows="16"
            placeholder="Write the offer letter content here. Use the placeholder buttons above to insert dynamic values that will be replaced with actual data when the offer is generated..."
            class="block w-full rounded-lg border border-slate-300 px-4 py-3 font-mono text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            @input="onInput"
        />
    </div>
</template>
