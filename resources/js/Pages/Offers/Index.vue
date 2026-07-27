<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import DataTable from '@/Components/UI/DataTable.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    offers: {
        type: Object,
        required: true,
    },
});

const columns = [
    { key: 'candidate', label: 'Candidate', sortable: true },
    { key: 'job', label: 'Job', sortable: true },
    { key: 'salary', label: 'Salary', sortable: true },
    { key: 'status', label: 'Status' },
    { key: 'sent_at', label: 'Sent', sortable: true },
    { key: 'responded_at', label: 'Responded' },
    { key: 'actions', label: '' },
];

const statusBadgeColors = {
    draft: 'bg-slate-100 text-slate-700',
    pending_approval: 'bg-amber-100 text-amber-700',
    approved: 'bg-blue-100 text-blue-700',
    sent: 'bg-indigo-100 text-indigo-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    declined: 'bg-red-100 text-red-700',
    withdrawn: 'bg-slate-100 text-slate-600',
    expired: 'bg-orange-100 text-orange-700',
};

function formatCurrency(amount, currency) {
    if (!amount) return '-';
    const cur = currency ?? 'USD';
    try {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: cur,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    } catch {
        return `${cur} ${Number(amount).toLocaleString()}`;
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function statusLabel(status) {
    if (!status) return '-';
    return status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Offers</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Track and manage all job offers sent to candidates.
                </p>
            </div>
        </div>

        <!-- Table -->
        <DataTable :columns="columns" :rows="offers.data" empty-message="No offers have been created yet.">
            <template #cell-candidate="{ row }">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                        {{ (row.application?.candidate?.name ?? '?').split(' ').map((n) => n.charAt(0)).join('').toUpperCase().slice(0, 2) }}
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">
                            {{ row.application?.candidate?.name ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ row.application?.candidate?.email ?? '' }}
                        </p>
                    </div>
                </div>
            </template>

            <template #cell-job="{ row }">
                <span class="text-sm text-slate-700">
                    {{ row.application?.job?.title ?? '-' }}
                </span>
            </template>

            <template #cell-salary="{ row }">
                <div>
                    <span class="text-sm font-medium text-slate-900">
                        {{ formatCurrency(row.salary, row.salary_currency) }}
                    </span>
                    <span v-if="row.salary_period" class="text-xs text-slate-500">
                        / {{ row.salary_period }}
                    </span>
                </div>
            </template>

            <template #cell-status="{ row }">
                <Badge
                    :label="statusLabel(row.status)"
                    :color="statusBadgeColors[row.status] ?? 'bg-slate-100 text-slate-700'"
                />
            </template>

            <template #cell-sent_at="{ row }">
                <span class="text-sm text-slate-700">
                    {{ formatDate(row.sent_at) }}
                </span>
            </template>

            <template #cell-responded_at="{ row }">
                <span class="text-sm text-slate-700">
                    {{ formatDate(row.responded_at) }}
                </span>
            </template>

            <template #cell-actions="{ row }">
                <Link
                    :href="route('offers.show', row.id)"
                    class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800"
                >
                    View
                </Link>
            </template>
        </DataTable>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :links="offers.links" />
        </div>
    </AppLayout>
</template>
