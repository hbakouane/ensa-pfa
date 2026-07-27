<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    subscription: {
        type: Object,
        default: null,
    },
    currentPlan: {
        type: Object,
        default: null,
    },
});

const cancelForm = useForm({});
const resumeForm = useForm({});

const isCancelled = computed(() => {
    return props.subscription && props.subscription.ends_at !== null;
});

const isActive = computed(() => {
    return props.subscription && props.subscription.stripe_status === 'active' && !isCancelled.value;
});

const isOnGracePeriod = computed(() => {
    if (!isCancelled.value || !props.subscription?.ends_at) return false;
    return new Date(props.subscription.ends_at) > new Date();
});

function cancelSubscription() {
    if (!confirm('Are you sure you want to cancel your subscription? You will retain access until the end of the current billing period.')) {
        return;
    }

    cancelForm.post(route('billing.cancel'), {
        preserveScroll: true,
    });
}

function resumeSubscription() {
    resumeForm.post(route('billing.resume'), {
        preserveScroll: true,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Subscription</h2>

        <!-- No subscription -->
        <div v-if="!subscription" class="text-sm text-slate-500">
            <p>You are currently on the free plan. Subscribe to a paid plan to unlock more features.</p>
        </div>

        <!-- Active subscription -->
        <div v-else>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Status</span>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="{
                            'bg-emerald-100 text-emerald-700': isActive,
                            'bg-amber-100 text-amber-700': isOnGracePeriod,
                            'bg-red-100 text-red-700': isCancelled && !isOnGracePeriod,
                        }"
                    >
                        <template v-if="isActive">Active</template>
                        <template v-else-if="isOnGracePeriod">Cancelling</template>
                        <template v-else>Cancelled</template>
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Plan</span>
                    <span class="text-sm font-medium text-slate-900">{{ currentPlan?.name ?? 'Unknown' }}</span>
                </div>

                <div v-if="subscription.trial_ends_at" class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Trial Ends</span>
                    <span class="text-sm text-slate-700">{{ formatDate(subscription.trial_ends_at) }}</span>
                </div>

                <div v-if="isCancelled && subscription.ends_at" class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Access Until</span>
                    <span class="text-sm text-slate-700">{{ formatDate(subscription.ends_at) }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex items-center gap-3">
                <button
                    v-if="isActive"
                    @click="cancelSubscription"
                    :disabled="cancelForm.processing"
                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50"
                >
                    Cancel Subscription
                </button>

                <button
                    v-if="isOnGracePeriod"
                    @click="resumeSubscription"
                    :disabled="resumeForm.processing"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                >
                    Resume Subscription
                </button>
            </div>
        </div>
    </div>
</template>
