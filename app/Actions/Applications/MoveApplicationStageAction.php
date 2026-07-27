<?php

namespace App\Actions\Applications;

use App\Models\Application;
use App\Models\ApplicationStageHistory;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Auth;

class MoveApplicationStageAction
{
    public function execute(Application $application, PipelineStage $targetStage, ?int $position = null): Application
    {
        $fromStageId = $application->pipeline_stage_id;

        // Record stage history
        ApplicationStageHistory::create([
            'application_id' => $application->id,
            'from_stage_id' => $fromStageId,
            'to_stage_id' => $targetStage->id,
            'moved_by' => Auth::id(),
            'created_at' => now(),
        ]);

        // Update the application
        $application->update([
            'pipeline_stage_id' => $targetStage->id,
            'position_in_stage' => $position ?? 0,
        ]);

        // Fire event
        event(new \App\Events\ApplicationStageChanged($application, $fromStageId, $targetStage->id));

        return $application->load(['pipelineStage', 'candidate']);
    }
}
