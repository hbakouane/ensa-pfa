<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    job: Object,
    departments: Array,
    locations: Array,
    categories: Array,
});

const form = useForm({
    title: props.job.title ?? '',
    description: props.job.description ?? '',
    requirements: props.job.requirements ?? '',
    responsibilities: props.job.responsibilities ?? '',
    benefits: props.job.benefits ?? '',
    department_id: props.job.department_id ?? '',
    location_id: props.job.location_id ?? '',
    category_id: props.job.category_id ?? '',
    employment_type: props.job.employment_type ?? '',
    experience_level: props.job.experience_level ?? '',
    salary_min: props.job.salary_min ?? '',
    salary_max: props.job.salary_max ?? '',
    salary_currency: props.job.salary_currency ?? 'USD',
    show_salary: props.job.show_salary ?? true,
    remote_policy: props.job.remote_policy ?? '',
    positions_count: props.job.positions_count ?? 1,
    skills: props.job.skills?.map((s) => (typeof s === 'string' ? s : s.name)) ?? [],
});

const newSkill = ref('');

const employmentTypeOptions = [
    { value: 'full_time', label: 'Temps plein' },
    { value: 'part_time', label: 'Temps partiel' },
    { value: 'contract', label: 'CDD' },
    { value: 'internship', label: 'Stage' },
    { value: 'temporary', label: 'Temporaire' },
];

const experienceLevelOptions = [
    { value: 'entry', label: 'Débutant' },
    { value: 'mid', label: 'Intermédiaire' },
    { value: 'senior', label: 'Senior' },
    { value: 'lead', label: 'Lead' },
    { value: 'executive', label: 'Dirigeant' },
];

const remotePolicyOptions = [
    { value: 'onsite', label: 'Sur site' },
    { value: 'remote', label: 'Télétravail' },
    { value: 'hybrid', label: 'Hybride' },
];

const currencyOptions = [
    { value: 'USD', label: 'USD' },
    { value: 'EUR', label: 'EUR' },
    { value: 'GBP', label: 'GBP' },
    { value: 'MAD', label: 'MAD' },
    { value: 'CAD', label: 'CAD' },
];

const departmentOptions = (props.departments ?? []).map((d) => ({
    value: d.id,
    label: d.name,
}));

const locationOptions = (props.locations ?? []).map((l) => ({
    value: l.id,
    label: l.name,
}));

const categoryOptions = (props.categories ?? []).map((c) => ({
    value: c.id,
    label: c.name,
}));

function addSkill() {
    const skill = newSkill.value.trim();
    if (skill && !form.skills.includes(skill)) {
        form.skills.push(skill);
    }
    newSkill.value = '';
}

function removeSkill(index) {
    form.skills.splice(index, 1);
}

function submit() {
    form.put(route('jobs.update', props.job.id));
}
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('jobs.index')" class="hover:text-indigo-600">
                    Offres d'emploi
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <Link :href="route('jobs.show', job.id)" class="hover:text-indigo-600">
                    {{ job.title }}
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span class="text-slate-700">Modifier</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Modifier l'offre</h1>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <!-- Basic Information -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">
                    Informations générales
                </h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <Input
                            v-model="form.title"
                            label="Titre du poste"
                            placeholder="ex. Ingénieur logiciel senior"
                            :error="form.errors.title"
                            required
                        />
                    </div>

                    <Select
                        v-model="form.department_id"
                        label="Département"
                        :options="departmentOptions"
                        placeholder="Sélectionner un département"
                        :error="form.errors.department_id"
                    />

                    <Select
                        v-model="form.location_id"
                        label="Lieu"
                        :options="locationOptions"
                        placeholder="Sélectionner un lieu"
                        :error="form.errors.location_id"
                    />

                    <Select
                        v-model="form.category_id"
                        label="Catégorie"
                        :options="categoryOptions"
                        placeholder="Sélectionner une catégorie"
                        :error="form.errors.category_id"
                    />

                    <Select
                        v-model="form.employment_type"
                        label="Type de contrat"
                        :options="employmentTypeOptions"
                        placeholder="Sélectionner un type"
                        :error="form.errors.employment_type"
                        required
                    />

                    <Select
                        v-model="form.experience_level"
                        label="Niveau d'expérience"
                        :options="experienceLevelOptions"
                        placeholder="Sélectionner un niveau"
                        :error="form.errors.experience_level"
                    />

                    <Select
                        v-model="form.remote_policy"
                        label="Politique de télétravail"
                        :options="remotePolicyOptions"
                        placeholder="Sélectionner une politique"
                        :error="form.errors.remote_policy"
                    />

                    <Input
                        v-model="form.positions_count"
                        label="Nombre de postes"
                        type="number"
                        placeholder="1"
                        :error="form.errors.positions_count"
                    />
                </div>
            </div>

            <!-- Job Details -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">
                    Détails du poste
                </h2>

                <div class="space-y-5">
                    <Textarea
                        v-model="form.description"
                        label="Description"
                        rows="5"
                        placeholder="Décrivez le rôle et son impact..."
                        :error="form.errors.description"
                        required
                    />

                    <Textarea
                        v-model="form.requirements"
                        label="Exigences"
                        rows="4"
                        placeholder="Listez les qualifications et exigences..."
                        :error="form.errors.requirements"
                    />

                    <Textarea
                        v-model="form.responsibilities"
                        label="Responsabilités"
                        rows="4"
                        placeholder="Listez les responsabilités principales..."
                        :error="form.errors.responsibilities"
                    />

                    <Textarea
                        v-model="form.benefits"
                        label="Avantages"
                        rows="3"
                        placeholder="Listez les avantages..."
                        :error="form.errors.benefits"
                    />
                </div>
            </div>

            <!-- Compensation -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">
                    Rémunération
                </h2>

                <div class="grid gap-5 sm:grid-cols-3">
                    <Input
                        v-model="form.salary_min"
                        label="Salaire minimum"
                        type="number"
                        placeholder="50000"
                        :error="form.errors.salary_min"
                    />

                    <Input
                        v-model="form.salary_max"
                        label="Salaire maximum"
                        type="number"
                        placeholder="80000"
                        :error="form.errors.salary_max"
                    />

                    <Select
                        v-model="form.salary_currency"
                        label="Devise"
                        :options="currencyOptions"
                        :error="form.errors.salary_currency"
                    />
                </div>

                <div class="mt-4">
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.show_salary"
                            type="checkbox"
                            class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="text-sm text-slate-700">
                            Afficher le salaire sur l'offre publique
                        </span>
                    </label>
                </div>
            </div>

            <!-- Skills -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">
                    Compétences
                </h2>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <input
                            v-model="newSkill"
                            type="text"
                            placeholder="Ajouter une compétence (ex. JavaScript, Python)"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                            @keydown.enter.prevent="addSkill"
                        />
                    </div>
                    <Button variant="secondary" @click="addSkill">Ajouter</Button>
                </div>

                <div v-if="form.skills.length" class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="(skill, idx) in form.skills"
                        :key="idx"
                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700"
                    >
                        {{ skill }}
                        <button
                            type="button"
                            class="ml-0.5 rounded-full p-0.5 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-600"
                            @click="removeSkill(idx)"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                </div>

                <p v-if="form.errors.skills" class="mt-1.5 text-xs text-red-600">
                    {{ form.errors.skills }}
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('jobs.show', job.id)">
                    <Button variant="ghost">Annuler</Button>
                </Link>
                <Button
                    type="submit"
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Mettre à jour l'offre
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
