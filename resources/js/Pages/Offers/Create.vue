<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import TemplateEditor from '@/Components/Offers/TemplateEditor.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    application: {
        type: Object,
        required: true,
    },
    templates: {
        type: Array,
        default: () => [],
    },
});

const candidate = computed(() => props.application.candidate ?? {});
const job = computed(() => props.application.job ?? {});

const form = useForm({
    application_id: props.application.id,
    template_id: '',
    salary: '',
    salary_currency: 'USD',
    salary_period: 'year',
    start_date: '',
    expiry_date: '',
    content: '',
});

const templateOptions = computed(() =>
    props.templates.map((t) => ({ value: t.id, label: t.name })),
);

const currencyOptions = [
    { value: 'USD', label: 'USD ($)' },
    { value: 'EUR', label: 'EUR' },
    { value: 'GBP', label: 'GBP' },
    { value: 'CAD', label: 'CAD' },
    { value: 'AUD', label: 'AUD' },
    { value: 'MAD', label: 'MAD' },
];

const periodOptions = [
    { value: 'year', label: 'Par an' },
    { value: 'month', label: 'Par mois' },
    { value: 'hour', label: 'Par heure' },
];

// Auto-fill content when template selected
watch(
    () => form.template_id,
    (newId) => {
        if (!newId) return;
        const template = props.templates.find((t) => String(t.id) === String(newId));
        if (template) {
            let content = template.content ?? '';
            content = content.replace(/\{\{candidate_name\}\}/g, candidate.value.name ?? '');
            content = content.replace(/\{\{position_title\}\}/g, job.value.title ?? '');
            content = content.replace(/\{\{salary\}\}/g, form.salary ? Number(form.salary).toLocaleString() : '{{salary}}');
            content = content.replace(/\{\{start_date\}\}/g, form.start_date || '{{start_date}}');
            content = content.replace(/\{\{company_name\}\}/g, '{{company_name}}');
            content = content.replace(/\{\{hiring_manager\}\}/g, '{{hiring_manager}}');
            form.content = content;
        }
    },
);

function submit() {
    form.post(route('offers.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout>
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
            <Link :href="route('offers.index')" class="transition-colors hover:text-indigo-600">
                Offres
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900">Créer une offre</span>
        </nav>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Créer une offre</h1>
            <p class="mt-1 text-sm text-slate-500">
                Préparer une offre pour
                <span class="font-medium text-slate-700">{{ candidate.name }}</span>
                candidat au poste de
                <span class="font-medium text-slate-700">{{ job.title }}</span>.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left column: Form fields -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Template Selection -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Modèle d'offre</h2>
                        <div class="mt-4">
                            <Select
                                v-model="form.template_id"
                                label="Modèle"
                                :options="templateOptions"
                                placeholder="Sélectionner un modèle (optionnel)"
                                :error="form.errors.template_id"
                            />
                        </div>
                    </div>

                    <!-- Compensation -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Rémunération</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <Input
                                v-model="form.salary"
                                label="Salaire"
                                type="number"
                                placeholder="e.g. 85000"
                                :error="form.errors.salary"
                                required
                            />
                            <Select
                                v-model="form.salary_currency"
                                label="Devise"
                                :options="currencyOptions"
                                :error="form.errors.salary_currency"
                                required
                            />
                            <Select
                                v-model="form.salary_period"
                                label="Période"
                                :options="periodOptions"
                                :error="form.errors.salary_period"
                                required
                            />
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Dates</h2><!-- Dates same in French -->
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Input
                                v-model="form.start_date"
                                label="Date de début"
                                type="date"
                                :error="form.errors.start_date"
                                required
                            />
                            <Input
                                v-model="form.expiry_date"
                                label="Date d'expiration"
                                type="date"
                                :error="form.errors.expiry_date"
                                required
                            />
                        </div>
                    </div>

                    <!-- Offer Content -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Contenu de l'offre</h2>
                        <div class="mt-4">
                            <TemplateEditor v-model="form.content" />
                            <p v-if="form.errors.content" class="mt-1.5 text-xs text-red-600">
                                {{ form.errors.content }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right column: Summary -->
                <div>
                    <div class="sticky top-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Résumé</h3>
                        <div class="mt-4 space-y-3">
                            <div>
                                <p class="text-xs text-slate-500">Candidat</p>
                                <p class="text-sm font-medium text-slate-900">{{ candidate.name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Poste</p>
                                <p class="text-sm font-medium text-slate-900">{{ job.title }}</p>
                            </div>
                            <div v-if="form.salary">
                                <p class="text-xs text-slate-500">Salaire</p>
                                <p class="text-sm font-medium text-slate-900">
                                    {{ form.salary_currency }} {{ Number(form.salary).toLocaleString() }} / {{ form.salary_period }}
                                </p>
                            </div>
                            <div v-if="form.start_date">
                                <p class="text-xs text-slate-500">Date de début</p>
                                <p class="text-sm font-medium text-slate-900">{{ form.start_date }}</p>
                            </div>
                            <div v-if="form.expiry_date">
                                <p class="text-xs text-slate-500">Expire le</p>
                                <p class="text-sm font-medium text-slate-900">{{ form.expiry_date }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3">
                            <Button
                                type="submit"
                                variant="primary"
                                :loading="form.processing"
                                :disabled="form.processing"
                                class="w-full justify-center"
                            >
                                Créer l'offre
                            </Button>
                            <Link
                                :href="route('offers.index')"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                            >
                                Annuler
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
