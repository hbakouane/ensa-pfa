import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useKanban(initialStages, jobId) {
    const stages = ref(
        (initialStages ?? []).map((stage) => ({
            ...stage,
            applications: [...(stage.applications ?? [])],
        })),
    );

    /**
     * Move an application from one stage to another with optimistic update.
     * Reverts the move if the server request fails.
     */
    function moveApplication(applicationId, toStageId, position) {
        // Find the application and its current stage
        let application = null;
        let fromStageIndex = -1;
        let fromAppIndex = -1;

        for (let i = 0; i < stages.value.length; i++) {
            const appIdx = stages.value[i].applications.findIndex(
                (app) => app.id === applicationId,
            );
            if (appIdx !== -1) {
                application = { ...stages.value[i].applications[appIdx] };
                fromStageIndex = i;
                fromAppIndex = appIdx;
                break;
            }
        }

        if (!application || fromStageIndex === -1) return;

        const toStageIndex = stages.value.findIndex((s) => s.id === toStageId);
        if (toStageIndex === -1) return;

        // Save state for potential revert
        const previousStages = stages.value.map((stage) => ({
            ...stage,
            applications: [...stage.applications],
        }));

        // Optimistic update: remove from old stage
        stages.value[fromStageIndex].applications.splice(fromAppIndex, 1);

        // Insert into new stage at the specified position
        const insertAt = Math.min(
            position ?? stages.value[toStageIndex].applications.length,
            stages.value[toStageIndex].applications.length,
        );
        stages.value[toStageIndex].applications.splice(insertAt, 0, application);

        // Send to server
        router.patch(
            route('pipeline.move', applicationId),
            {
                stage_id: toStageId,
                position: insertAt,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onError() {
                    // Revert on error
                    stages.value = previousStages;
                },
            },
        );
    }

    /**
     * Reject an application with an optional reason.
     */
    function rejectApplication(applicationId, reasonId) {
        // Find the application
        let fromStageIndex = -1;
        let fromAppIndex = -1;

        for (let i = 0; i < stages.value.length; i++) {
            const appIdx = stages.value[i].applications.findIndex(
                (app) => app.id === applicationId,
            );
            if (appIdx !== -1) {
                fromStageIndex = i;
                fromAppIndex = appIdx;
                break;
            }
        }

        if (fromStageIndex === -1) return;

        // Save state for revert
        const previousStages = stages.value.map((stage) => ({
            ...stage,
            applications: [...stage.applications],
        }));

        // Optimistic: remove from current stage
        stages.value[fromStageIndex].applications.splice(fromAppIndex, 1);

        router.patch(
            route('pipeline.reject', applicationId),
            {
                rejection_reason_id: reasonId ?? null,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onError() {
                    stages.value = previousStages;
                },
            },
        );
    }

    /**
     * Update the local stages (e.g. after a drag-and-drop reorder within the same column).
     */
    function updateStageApplications(stageId, applications) {
        const idx = stages.value.findIndex((s) => s.id === stageId);
        if (idx !== -1) {
            stages.value[idx].applications = applications;
        }
    }

    return {
        stages,
        moveApplication,
        rejectApplication,
        updateStageApplications,
    };
}
