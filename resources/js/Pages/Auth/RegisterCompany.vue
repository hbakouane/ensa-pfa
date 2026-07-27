<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    company_name: '',
    industry: '',
    company_size: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const companySizes = [
    { value: '1-10', label: '1-10 employés' },
    { value: '11-50', label: '11-50 employés' },
    { value: '51-200', label: '51-200 employés' },
    { value: '201-500', label: '201-500 employés' },
    { value: '501-1000', label: '501-1 000 employés' },
    { value: '1001+', label: '1 001+ employés' },
];

function submit() {
    form.post(route('company.register.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Créez votre compte entreprise" />

        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-slate-900">
                Créez votre compte entreprise
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Démarrez avec RecruitAI en quelques minutes
            </p>
        </div>

        <form @submit.prevent="submit">
            <!-- Company section -->
            <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <h2 class="mb-3 text-sm font-semibold text-slate-700">
                    Informations de l'entreprise
                </h2>

                <div>
                    <InputLabel for="company_name" value="Nom de l'entreprise" />
                    <TextInput
                        id="company_name"
                        v-model="form.company_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        placeholder="Acme Inc."
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.company_name"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel for="industry" value="Secteur d'activité" />
                    <TextInput
                        id="industry"
                        v-model="form.industry"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="ex. Technologie, Santé, Finance"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.industry"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel for="company_size" value="Taille de l'entreprise" />
                    <select
                        id="company_size"
                        v-model="form.company_size"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="" disabled>Sélectionner la taille de l'entreprise</option>
                        <option
                            v-for="size in companySizes"
                            :key="size.value"
                            :value="size.value"
                        >
                            {{ size.label }}
                        </option>
                    </select>
                    <InputError
                        class="mt-2"
                        :message="form.errors.company_size"
                    />
                </div>
            </div>

            <!-- User section -->
            <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <h2 class="mb-3 text-sm font-semibold text-slate-700">
                    Compte administrateur
                </h2>

                <div>
                    <InputLabel for="name" value="Votre nom" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="John Doe"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="email" value="E-mail" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        placeholder="john@acme.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Mot de passe" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        placeholder="Minimum 8 caractères"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="password_confirmation"
                        value="Confirmer le mot de passe"
                    />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        placeholder="Répétez votre mot de passe"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation"
                    />
                </div>
            </div>

            <PrimaryButton
                class="w-full justify-center"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Créer le compte
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
