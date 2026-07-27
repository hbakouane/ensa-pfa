<script setup>
import Button from '@/Components/UI/Button.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    interview: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    overall_rating: 0,
    recommendation: '',
    strengths: '',
    concerns: '',
    notes: '',
    criteria: [],
});

const hoveredStar = ref(0);

const recommendationOptions = [
    { value: 'strong_yes', label: 'Fortement recommandé', color: 'border-emerald-300 bg-emerald-50 text-emerald-700' },
    { value: 'yes', label: 'Recommandé', color: 'border-green-300 bg-green-50 text-green-700' },
    { value: 'maybe', label: 'Peut-être', color: 'border-amber-300 bg-amber-50 text-amber-700' },
    { value: 'no', label: 'Non recommandé', color: 'border-orange-300 bg-orange-50 text-orange-700' },
    { value: 'strong_no', label: 'Fortement non recommandé', color: 'border-red-300 bg-red-50 text-red-700' },
];

function setRating(rating) {
    form.overall_rating = rating;
}

function addCriterion() {
    form.criteria.push({ name: '', rating: 0 });
}

function removeCriterion(index) {
    form.criteria.splice(index, 1);
}

function setCriterionRating(index, rating) {
    form.criteria[index].rating = rating;
}

function submit() {
    form.post(route('scorecards.store', props.interview.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <!-- Overall Rating -->
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">
                Évaluation globale
                <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-1">
                <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    class="rounded p-0.5 transition-transform hover:scale-110 focus:outline-none"
                    @click="setRating(star)"
                    @mouseenter="hoveredStar = star"
                    @mouseleave="hoveredStar = 0"
                >
                    <svg
                        class="h-8 w-8 transition-colors"
                        :class="
                            star <= (hoveredStar || form.overall_rating)
                                ? 'text-amber-400'
                                : 'text-slate-200'
                        "
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
                <span class="ml-2 text-sm text-slate-500">
                    {{ form.overall_rating > 0 ? `${form.overall_rating}/5` : 'Sélectionner une évaluation' }}
                </span>
            </div>
            <p v-if="form.errors.overall_rating" class="mt-1.5 text-xs text-red-600">
                {{ form.errors.overall_rating }}
            </p>
        </div>

        <!-- Recommendation -->
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">
                Recommandation
                <span class="text-red-500">*</span>
            </label>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in recommendationOptions"
                    :key="option.value"
                    type="button"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition-all"
                    :class="
                        form.recommendation === option.value
                            ? option.color + ' ring-2 ring-offset-1 ring-indigo-300'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                    "
                    @click="form.recommendation = option.value"
                >
                    {{ option.label }}
                </button>
            </div>
            <p v-if="form.errors.recommendation" class="mt-1.5 text-xs text-red-600">
                {{ form.errors.recommendation }}
            </p>
        </div>

        <!-- Strengths -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Points forts</label>
            <textarea
                v-model="form.strengths"
                rows="3"
                placeholder="Qu'est-ce qui s'est démarqué positivement chez le candidat ?"
                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p v-if="form.errors.strengths" class="mt-1.5 text-xs text-red-600">
                {{ form.errors.strengths }}
            </p>
        </div>

        <!-- Concerns -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Préoccupations</label>
            <textarea
                v-model="form.concerns"
                rows="3"
                placeholder="Des préoccupations ou des points à améliorer ?"
                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p v-if="form.errors.concerns" class="mt-1.5 text-xs text-red-600">
                {{ form.errors.concerns }}
            </p>
        </div>

        <!-- Notes -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Notes supplémentaires</label>
            <textarea
                v-model="form.notes"
                rows="3"
                placeholder="Autres observations ou commentaires..."
                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p v-if="form.errors.notes" class="mt-1.5 text-xs text-red-600">
                {{ form.errors.notes }}
            </p>
        </div>

        <!-- Dynamic Criteria -->
        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">Critères d'évaluation</label>
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50"
                    @click="addCriterion"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un critère
                </button>
            </div>

            <div v-if="form.criteria.length > 0" class="space-y-3">
                <div
                    v-for="(criterion, index) in form.criteria"
                    :key="index"
                    class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3"
                >
                    <input
                        v-model="criterion.name"
                        type="text"
                        placeholder="ex. Communication, Résolution de problèmes"
                        class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    />
                    <div class="flex items-center gap-0.5">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            class="focus:outline-none"
                            @click="setCriterionRating(index, star)"
                        >
                            <svg
                                class="h-5 w-5 transition-colors"
                                :class="star <= criterion.rating ? 'text-amber-400' : 'text-slate-200'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </button>
                    </div>
                    <button
                        type="button"
                        class="rounded p-1 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500"
                        @click="removeCriterion(index)"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <p v-else class="text-xs text-slate-500">
                Ajoutez des critères spécifiques pour évaluer le candidat.
            </p>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-3 border-t border-slate-200 pt-5">
            <Button
                type="submit"
                variant="primary"
                :loading="form.processing"
                :disabled="form.processing || !form.overall_rating || !form.recommendation"
            >
                Soumettre la fiche d'évaluation
            </Button>
            <p v-if="!form.overall_rating || !form.recommendation" class="text-xs text-slate-500">
                L'évaluation et la recommandation sont requises.
            </p>
        </div>
    </form>
</template>
