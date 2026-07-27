<script setup>
import Button from '@/Components/UI/Button.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    commentableType: {
        type: String,
        required: true,
    },
    commentableId: {
        type: [String, Number],
        required: true,
    },
    parentId: {
        type: [String, Number, null],
        default: null,
    },
});

const emit = defineEmits(['submitted']);

const form = useForm({
    commentable_type: props.commentableType,
    commentable_id: props.commentableId,
    parent_id: props.parentId,
    body: '',
});

function submit() {
    if (!form.body.trim()) return;

    form.post(route('comments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.body = '';
            emit('submitted');
        },
    });
}

function onKeydown(event) {
    // Submit on Cmd/Ctrl + Enter
    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        event.preventDefault();
        submit();
    }
}
</script>

<template>
    <div>
        <div class="relative">
            <textarea
                v-model="form.body"
                :rows="parentId ? 2 : 3"
                :placeholder="parentId ? 'Écrire une réponse...' : 'Ajouter un commentaire...'"
                class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-20 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-200': form.errors.body }"
                @keydown="onKeydown"
            />
            <div class="absolute bottom-2 right-2">
                <Button
                    variant="primary"
                    size="sm"
                    :loading="form.processing"
                    :disabled="form.processing || !form.body.trim()"
                    @click="submit"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </Button>
            </div>
        </div>
        <p v-if="form.errors.body" class="mt-1.5 text-xs text-red-600">
            {{ form.errors.body }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
            Appuyez sur <kbd class="rounded bg-slate-100 px-1 py-0.5 text-[10px] font-mono text-slate-500">Ctrl+Entrée</kbd> pour envoyer
        </p>
    </div>
</template>
