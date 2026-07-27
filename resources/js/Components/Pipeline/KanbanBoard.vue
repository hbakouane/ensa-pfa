<script setup>
import KanbanColumn from './KanbanColumn.vue';
import { useKanban } from '@/Composables/useKanban';
import { computed } from 'vue';

const props = defineProps({
    job: Object,
    stages: Array,
    applications: Object,
});

// Merge applications into stages
const initialStages = computed(() =>
    (props.stages ?? []).map((stage) => ({
        ...stage,
        applications: props.applications?.[stage.id] ?? [],
    })),
);

const { stages, moveApplication, rejectApplication, updateStageApplications } = useKanban(
    initialStages.value,
    props.job?.id,
);

function handleMove(applicationId, toStageId, position) {
    moveApplication(applicationId, toStageId, position);
}

function handleReject(applicationId) {
    rejectApplication(applicationId);
}

function handleUpdate(stageId, applications) {
    updateStageApplications(stageId, applications);
}
</script>

<template>
    <div class="flex gap-4 overflow-x-auto pb-4">
        <KanbanColumn
            v-for="stage in stages"
            :key="stage.id"
            :stage="stage"
            :applications="stage.applications"
            @move="handleMove"
            @reject="handleReject"
            @update="handleUpdate"
        />
    </div>
</template>
