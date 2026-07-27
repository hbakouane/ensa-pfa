<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    company: Object,
    job: Object,
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    resume: null,
    cover_letter: '',
});

function handleFileChange(e) {
    form.resume = e.target.files[0] ?? null;
}

function submit() {
    form.post(route('careers.submit', [props.company.slug, props.job.slug]), {
        forceFormData: true,
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Apply - ${job.title} - ${company.name}`" />

        <!-- Breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('careers.index', company.slug)" class="hover:text-indigo-600">All Positions</Link>
                <span>/</span>
                <Link :href="route('careers.show', [company.slug, job.slug])" class="hover:text-indigo-600">{{ job.title }}</Link>
                <span>/</span>
                <span class="text-slate-700">Apply</span>
            </div>
        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-800">{{ successMessage }}</p>
        </div>

        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-slate-900">Apply for {{ job.title }}</h1>
            <p class="mt-1 text-slate-600">Fill out the form below to submit your application.</p>

            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 sm:grid-cols-2">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700">First Name *</label>
                        <input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.first_name" class="mt-1 text-xs text-red-600">{{ form.errors.first_name }}</p>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700">Last Name *</label>
                        <input
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            required
                            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.last_name" class="mt-1 text-xs text-red-600">{{ form.errors.last_name }}</p>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address *</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                </div>

                <!-- Resume -->
                <div>
                    <label for="resume" class="block text-sm font-medium text-slate-700">Resume</label>
                    <input
                        id="resume"
                        type="file"
                        accept=".pdf,.doc,.docx"
                        class="mt-1 w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                        @change="handleFileChange"
                    />
                    <p class="mt-1 text-xs text-slate-500">PDF, DOC, or DOCX (max 10MB)</p>
                    <p v-if="form.errors.resume" class="mt-1 text-xs text-red-600">{{ form.errors.resume }}</p>
                </div>

                <!-- Cover Letter -->
                <div>
                    <label for="cover_letter" class="block text-sm font-medium text-slate-700">Cover Letter</label>
                    <textarea
                        id="cover_letter"
                        v-model="form.cover_letter"
                        rows="5"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        placeholder="Tell us why you're a great fit for this role..."
                    />
                    <p v-if="form.errors.cover_letter" class="mt-1 text-xs text-red-600">{{ form.errors.cover_letter }}</p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('careers.show', [company.slug, job.slug])"
                        class="rounded-lg px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                    </button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
