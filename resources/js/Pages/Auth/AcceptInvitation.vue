<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: {
        type: Object,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    name: '',
    email: props.invitation.email ?? '',
    password: '',
    password_confirmation: '',
    token: props.token,
});

function submit() {
    form.post(route('invitation.accept.store', { token: props.token }), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Accepter l'invitation" />

        <div class="mb-6 text-center">
            <div
                class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100"
            >
                <svg
                    class="h-6 w-6 text-indigo-600"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                    />
                </svg>
            </div>

            <h1 class="text-xl font-bold text-slate-900">
                Vous avez été invité(e)
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Rejoindre
                <span class="font-semibold text-indigo-600">
                    {{ invitation.company_name ?? "l'équipe" }}
                </span>
                sur RecruitAI
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Votre nom" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
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
                    :disabled="!!invitation.email"
                    :class="{
                        'bg-slate-100 text-slate-500': !!invitation.email,
                    }"
                />
                <InputError class="mt-2" :message="form.errors.email" />
                <p
                    v-if="invitation.email"
                    class="mt-1 text-xs text-slate-400"
                >
                    Cet e-mail a été spécifié dans votre invitation et ne peut pas
                    être modifié.
                </p>
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
                <InputError class="mt-2" :message="form.errors.password" />
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

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Accepter l'invitation et rejoindre
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
