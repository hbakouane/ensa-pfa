<script setup>
import draggable from 'vuedraggable';
import KanbanCard from './KanbanCard.vue';
import { computed } from 'vue';

const props = defineProps({
    stage: Object,
    applications: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['move', 'reject', 'update']);

const localApplications = computed({
    get: () => props.applications,
    set: (value) => emit('update', props.stage.id, value),
});

const applicationCount = computed(() => props.applications.length);

function onEnd(evt) {
    if (evt.to !== evt.from || evt.newIndex !== evt.oldIndex) {
        const applicationId = parseInt(evt.item.dataset.id);
        const toStageId = parseInt(evt.to.dataset.stageId);
        emit('move', applicationId, toStageId, evt.newIndex);
    }
}
</script>

<template>
    <div class="flex w-72 shrink-0 flex-col rounded-lg bg-slate-100">
        <!-- Column header -->
        <div class="flex items-center justify-between border-b border-slate-200 px-3 py-2.5">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-slate-700">{{ stage.name }}</h3>
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-slate-200 px-1.5 text-[10px] font-semibold text-slate-600">
                    {{ applicationCount }}
                </span>
            </div>
        </div>

        <!-- Cards container -->
        <draggable
            v-model="localApplications"
            item-key="id"
            group="applications"
            ghost-class="opacity-30"
            drag-class="rotate-2"
            :data-stage-id="stage.id"
            class="flex flex-1 flex-col gap-2 overflow-y-auto p-2"
            style="min-height: 120px"
            @end="onEnd"
        >
            <template #item="{ element }">
                <div :data-id="element.id">
                    <KanbanCard
                        :application="element"
                        @reject="(id) => emit('reject', id)"
                    />
                </div>
            </template>
        </draggable>
    </div>
</template>
