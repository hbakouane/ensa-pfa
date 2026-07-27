<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import TemplateEditor from '@/Components/Offers/TemplateEditor.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    templates: {
        type: Array,
        default: () => [],
    },
});

const showForm = ref(false);
const editingTemplate = ref(null);

const form = useForm({
    name: '',
    content: '',
});

function openCreateForm() {
    editingTemplate.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
}

function openEditForm(template) {
    editingTemplate.value = template;
    form.name = template.name;
    form.content = template.content ?? '';
    form.clearErrors();
    showForm.value = true;
}

function cancelForm() {
    showForm.value = false;
    editingTemplate.value = null;
    form.reset();
    form.clearErrors();
}

function submit() {
    if (editingTemplate.value) {
        form.put(route('offer-templates.update', editingTemplate.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                editingTemplate.value = null;
                form.reset();
            },
        });
    } else {
        form.post(route('offer-templates.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                form.reset();
            },
        });
    }
}

function deleteTemplate(template) {
    if (!confirm(`Are you sure you want to delete the template "${template.name}"?`)) return;
    router.delete(route('offer-templates.destroy', template.id), {
        preserveScroll: true,
    });
}

const placeholderHints = [
    '{{candidate_name}}',
    '{{position_title}}',
    '{{salary}}',
    '{{start_date}}',
    '{{company_name}}',
    '{{hiring_manager}}',
];
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Offer Templates</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Create and manage reusable offer letter templates.
                </p>
            </div>
            <Button v-if="!showForm" variant="primary" @click="openCreateForm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                New Template
            </Button>
        </div>

        <!-- Create / Edit Form -->
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="showForm" class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ editingTemplate ? 'Edit Template' : 'Create New Template' }}
                </h2>

                <form @submit.prevent="submit" class="mt-4 space-y-4">
                    <Input
                        v-model="form.name"
                        label="Template Name"
                        placeholder="e.g. Standard Full-Time Offer"
                        :error="form.errors.name"
                        required
                    />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            Content
                            <span class="text-red-500">*</span>
                        </label>
                        <TemplateEditor v-model="form.content" />
                        <p v-if="form.errors.content" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.content }}
                        </p>
                    </div>

                    <!-- Placeholder hints -->
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Available Placeholders</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span
                                v-for="placeholder in placeholderHints"
                                :key="placeholder"
                                class="inline-flex rounded-md bg-indigo-50 px-2 py-1 text-xs font-mono text-indigo-700"
                            >
                                {{ placeholder }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing"
                        >
                            {{ editingTemplate ? 'Update Template' : 'Create Template' }}
                        </Button>
                        <Button variant="secondary" @click="cancelForm">
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </Transition>

        <!-- Templates List -->
        <div class="space-y-4">
            <div
                v-for="template in templates"
                :key="template.id"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-slate-900">{{ template.name }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-500">
                            {{ template.content ? template.content.replace(/<[^>]*>/g, '').slice(0, 200) : 'No content' }}
                        </p>
                        <p class="mt-2 text-xs text-slate-400">
                            Last updated: {{ new Date(template.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </p>
                    </div>
                    <div class="ml-4 flex items-center gap-2">
                        <button
                            class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                            title="Edit template"
                            @click="openEditForm(template)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                            title="Delete template"
                            @click="deleteTemplate(template)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="templates.length === 0 && !showForm"
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 py-16"
        >
            <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-500">No templates yet</p>
            <p class="mt-1 text-sm text-slate-400">Create your first offer letter template to get started.</p>
            <Button variant="primary" size="sm" class="mt-4" @click="openCreateForm">
                Create Template
            </Button>
        </div>
    </AppLayout>
</template>
