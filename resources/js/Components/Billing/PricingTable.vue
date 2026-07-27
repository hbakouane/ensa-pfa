<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
    currentPlanSlug: {
        type: String,
        default: 'free',
    },
});

const interval = ref('monthly');

const form = useForm({
    plan_slug: '',
    payment_method: '',
    interval: 'monthly',
    new_plan_slug: '',
});

function selectPlan(planSlug) {
    if (planSlug === props.currentPlanSlug) return;

    form.new_plan_slug = planSlug;
    form.interval = interval.value;

    form.patch(route('billing.change-plan'), {
        preserveScroll: true,
    });
}

function getCtaLabel(planSlug) {
    if (planSlug === props.currentPlanSlug) return 'Current Plan';

    const currentIndex = props.plans.findIndex((p) => p.slug === props.currentPlanSlug);
    const targetIndex = props.plans.findIndex((p) => p.slug === planSlug);

    return targetIndex > currentIndex ? 'Upgrade' : 'Downgrade';
}

function getCtaClass(planSlug) {
    if (planSlug === props.currentPlanSlug) {
        return 'cursor-default border-2 border-indigo-600 bg-indigo-50 text-indigo-700';
    }
    return 'border-2 border-slate-200 bg-indigo-600 text-white hover:bg-indigo-700';
}

function formatPrice(plan) {
    const price = interval.value === 'yearly' ? plan.price_yearly : plan.price_monthly;
    if (parseFloat(price) === 0) return 'Free';
    return `$${parseFloat(price).toFixed(0)}`;
}

function formatFeatureValue(value) {
    if (value === -1) return 'Unlimited';
    return value.toLocaleString();
}
</script>

<template>
    <div>
        <!-- Interval Toggle -->
        <div class="mb-6 flex items-center justify-center gap-3">
            <button
                @click="interval = 'monthly'"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="interval === 'monthly' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            >
                Monthly
            </button>
            <button
                @click="interval = 'yearly'"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="interval === 'yearly' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            >
                Yearly
                <span class="ml-1 text-xs opacity-75">Save 20%</span>
            </button>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
                v-for="plan in plans"
                :key="plan.slug"
                class="relative rounded-xl border p-6 transition-shadow hover:shadow-md"
                :class="plan.slug === currentPlanSlug ? 'border-indigo-300 bg-indigo-50/50' : 'border-slate-200 bg-white'"
            >
                <!-- Current badge -->
                <div
                    v-if="plan.slug === currentPlanSlug"
                    class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-indigo-600 px-3 py-0.5 text-xs font-semibold text-white"
                >
                    Current
                </div>

                <h3 class="text-lg font-bold text-slate-900">{{ plan.name }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ plan.description }}</p>

                <div class="mt-4">
                    <span class="text-3xl font-bold text-slate-900">{{ formatPrice(plan) }}</span>
                    <span v-if="parseFloat(interval === 'yearly' ? plan.price_yearly : plan.price_monthly) > 0" class="text-sm text-slate-500">
                        /{{ interval === 'yearly' ? 'year' : 'month' }}
                    </span>
                </div>

                <ul class="mt-6 space-y-3 text-sm text-slate-600">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ formatFeatureValue(plan.max_jobs) }} jobs
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ formatFeatureValue(plan.max_candidates) }} candidates
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ formatFeatureValue(plan.max_users) }} team members
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ formatFeatureValue(plan.ai_parses_per_month) }} AI parses/month
                    </li>
                    <li v-for="feature in (plan.features ?? [])" :key="feature" class="flex items-center gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        {{ feature }}
                    </li>
                </ul>

                <button
                    @click="selectPlan(plan.slug)"
                    :disabled="plan.slug === currentPlanSlug || form.processing"
                    class="mt-6 w-full rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors disabled:opacity-70"
                    :class="getCtaClass(plan.slug)"
                >
                    {{ getCtaLabel(plan.slug) }}
                </button>
            </div>
        </div>
    </div>
</template>
