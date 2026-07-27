<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    headline: '',
    summary: '',
    location: '',
    linkedin_url: '',
    portfolio_url: '',
    resume: null,
    skills: [],
    experiences: [{ company_name: '', title: '', start_date: '', end_date: '', description: '' }],
    educations: [{ institution: '', degree: '', field_of_study: '', start_date: '', end_date: '' }],
});

const newSkill = ref('');
const resumeFileName = ref('');

function addSkill() {
    const skill = newSkill.value.trim();
    if (skill && !form.skills.some(s => s.name === skill)) {
        form.skills.push({ name: skill });
    }
    newSkill.value = '';
}

function removeSkill(idx) {
    form.skills.splice(idx, 1);
}

function addExperience() {
    form.experiences.push({ company_name: '', title: '', start_date: '', end_date: '', description: '' });
}

function removeExperience(idx) {
    form.experiences.splice(idx, 1);
}

function addEducation() {
    form.educations.push({ institution: '', degree: '', field_of_study: '', start_date: '', end_date: '' });
}

function removeEducation(idx) {
    form.educations.splice(idx, 1);
}

function handleResumeUpload(event) {
    const file = event.target.files[0];
    if (file) {
        form.resume = file;
        resumeFileName.value = file.name;
    }
}

function submit() {
    form.post(route('candidates.store'), {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('candidates.index')" class="hover:text-indigo-600">
                    Candidats
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span class="text-slate-700">Ajouter un candidat</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Ajouter un nouveau candidat</h1>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <!-- Personal Information -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Informations personnelles</h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    <Input
                        v-model="form.first_name"
                        label="Prénom"
                        placeholder="John"
                        :error="form.errors.first_name"
                        required
                    />
                    <Input
                        v-model="form.last_name"
                        label="Nom"
                        placeholder="Doe"
                        :error="form.errors.last_name"
                        required
                    />
                    <Input
                        v-model="form.email"
                        label="E-mail"
                        type="email"
                        placeholder="john@example.com"
                        :error="form.errors.email"
                        required
                    />
                    <Input
                        v-model="form.phone"
                        label="Téléphone"
                        type="tel"
                        placeholder="+1 (555) 000-0000"
                        :error="form.errors.phone"
                    />
                    <Input
                        v-model="form.headline"
                        label="Titre professionnel"
                        placeholder="Senior Software Engineer"
                        :error="form.errors.headline"
                    />
                    <Input
                        v-model="form.location"
                        label="Lieu"
                        placeholder="Paris, France"
                        :error="form.errors.location"
                    />
                    <Input
                        v-model="form.linkedin_url"
                        label="LinkedIn URL"
                        type="url"
                        placeholder="https://linkedin.com/in/johndoe"
                        :error="form.errors.linkedin_url"
                    />
                    <Input
                        v-model="form.portfolio_url"
                        label="Portfolio URL"
                        type="url"
                        placeholder="https://johndoe.com"
                        :error="form.errors.portfolio_url"
                    />

                    <div class="sm:col-span-2">
                        <Textarea
                            v-model="form.summary"
                            label="Résumé"
                            rows="3"
                            placeholder="Résumé professionnel bref..."
                            :error="form.errors.summary"
                        />
                    </div>
                </div>
            </div>

            <!-- Resume Upload -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">CV</h2>

                <div>
                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 px-6 py-8 transition-colors hover:border-indigo-400 hover:bg-indigo-50/50"
                    >
                        <svg class="h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <p class="mt-2 text-sm text-slate-600">
                            <span class="font-medium text-indigo-600">Cliquer pour télécharger</span>
                            ou glisser-déposer
                        </p>
                        <p class="mt-1 text-xs text-slate-400">PDF, DOC, DOCX (max 10 Mo)</p>
                        <input
                            type="file"
                            class="hidden"
                            accept=".pdf,.doc,.docx"
                            @change="handleResumeUpload"
                        />
                    </label>

                    <div v-if="resumeFileName" class="mt-3 flex items-center gap-2 text-sm text-slate-700">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ resumeFileName }}
                    </div>

                    <p v-if="form.errors.resume" class="mt-1.5 text-xs text-red-600">
                        {{ form.errors.resume }}
                    </p>
                </div>
            </div>

            <!-- Skills -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Compétences</h2>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <input
                            v-model="newSkill"
                            type="text"
                            placeholder="Ajouter une compétence..."
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
                        {{ skill.name }}
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
            </div>

            <!-- Experience -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Expérience</h2>
                    <Button variant="ghost" size="sm" @click="addExperience">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter
                    </Button>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="(exp, idx) in form.experiences"
                        :key="idx"
                        class="relative rounded-lg border border-slate-200 bg-slate-50 p-4"
                    >
                        <button
                            v-if="form.experiences.length > 1"
                            type="button"
                            class="absolute right-3 top-3 rounded-lg p-1 text-slate-400 hover:bg-white hover:text-red-500"
                            @click="removeExperience(idx)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <Input
                                v-model="exp.company_name"
                                label="Entreprise"
                                placeholder="Nom de l'entreprise"
                                :error="form.errors[`experiences.${idx}.company_name`]"
                            />
                            <Input
                                v-model="exp.title"
                                label="Titre"
                                placeholder="Intitulé du poste"
                                :error="form.errors[`experiences.${idx}.title`]"
                            />
                            <Input
                                v-model="exp.start_date"
                                label="Date de début"
                                type="date"
                                :error="form.errors[`experiences.${idx}.start_date`]"
                            />
                            <Input
                                v-model="exp.end_date"
                                label="Date de fin"
                                type="date"
                                :error="form.errors[`experiences.${idx}.end_date`]"
                            />
                            <div class="sm:col-span-2">
                                <Textarea
                                    v-model="exp.description"
                                    label="Description"
                                    rows="2"
                                    placeholder="Brève description des responsabilités..."
                                    :error="form.errors[`experiences.${idx}.description`]"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Formation</h2>
                    <Button variant="ghost" size="sm" @click="addEducation">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter
                    </Button>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="(edu, idx) in form.educations"
                        :key="idx"
                        class="relative rounded-lg border border-slate-200 bg-slate-50 p-4"
                    >
                        <button
                            v-if="form.educations.length > 1"
                            type="button"
                            class="absolute right-3 top-3 rounded-lg p-1 text-slate-400 hover:bg-white hover:text-red-500"
                            @click="removeEducation(idx)"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <Input
                                v-model="edu.institution"
                                label="Établissement"
                                placeholder="Nom de l'université"
                                :error="form.errors[`educations.${idx}.institution`]"
                            />
                            <Input
                                v-model="edu.degree"
                                label="Diplôme"
                                placeholder="Licence, Master, etc."
                                :error="form.errors[`educations.${idx}.degree`]"
                            />
                            <Input
                                v-model="edu.field_of_study"
                                label="Domaine d'études"
                                placeholder="Informatique"
                                :error="form.errors[`educations.${idx}.field_of_study`]"
                            />
                            <div class="grid grid-cols-2 gap-3">
                                <Input
                                    v-model="edu.start_date"
                                    label="Début"
                                    type="date"
                                    :error="form.errors[`educations.${idx}.start_date`]"
                                />
                                <Input
                                    v-model="edu.end_date"
                                    label="Fin"
                                    type="date"
                                    :error="form.errors[`educations.${idx}.end_date`]"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('candidates.index')">
                    <Button variant="ghost">Annuler</Button>
                </Link>
                <Button
                    type="submit"
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Ajouter le candidat
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
