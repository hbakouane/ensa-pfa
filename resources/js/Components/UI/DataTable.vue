<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Each item: { key: string, label: string, sortable?: boolean }
    },
    rows: {
        type: Array,
        required: true,
    },
    emptyMessage: {
        type: String,
        default: 'Aucune donnée disponible.',
    },
});

const emit = defineEmits(['sort']);

const sortKey = ref(null);
const sortDir = ref('asc');

const sortedRows = computed(() => {
    if (!sortKey.value) return props.rows;

    const col = props.columns.find((c) => c.key === sortKey.value);
    if (!col?.sortable) return props.rows;

    return [...props.rows].sort((a, b) => {
        const aVal = a[sortKey.value];
        const bVal = b[sortKey.value];

        if (aVal == null) return 1;
        if (bVal == null) return -1;

        let result = 0;
        if (typeof aVal === 'string') {
            result = aVal.localeCompare(bVal);
        } else {
            result = aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
        }

        return sortDir.value === 'asc' ? result : -result;
    });
});

function toggleSort(column) {
    if (!column.sortable) return;

    if (sortKey.value === column.key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortDir.value = 'asc';
    }

    emit('sort', { key: sortKey.value, direction: sortDir.value });
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                            :class="{
                                'cursor-pointer select-none hover:text-slate-700':
                                    col.sortable,
                            }"
                            @click="toggleSort(col)"
                        >
                            <div class="flex items-center gap-1.5">
                                <span>{{ col.label }}</span>
                                <span
                                    v-if="col.sortable"
                                    class="text-slate-400"
                                >
                                    <svg
                                        v-if="sortKey !== col.key"
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M8 9l4-4 4 4m0 6l-4 4-4-4"
                                        />
                                    </svg>
                                    <svg
                                        v-else-if="sortDir === 'asc'"
                                        class="h-3.5 w-3.5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 15l7-7 7 7"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-3.5 w-3.5 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="(row, idx) in sortedRows"
                        :key="row.id ?? idx"
                        class="transition-colors hover:bg-slate-50"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="whitespace-nowrap px-6 py-4 text-sm text-slate-700"
                        >
                            <slot
                                :name="`cell-${col.key}`"
                                :row="row"
                                :value="row[col.key]"
                            >
                                {{ row[col.key] ?? '-' }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div
            v-if="sortedRows.length === 0"
            class="flex flex-col items-center justify-center py-12"
        >
            <svg
                class="h-12 w-12 text-slate-300"
                fill="none"
                stroke="currentColor"
                stroke-width="1"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                />
            </svg>
            <p class="mt-3 text-sm text-slate-500">{{ emptyMessage }}</p>
        </div>
    </div>
</template>
