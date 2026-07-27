<?php

namespace App\Actions\Applications;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class RejectApplicationAction
{
    public function execute(Application $application, ?int $rejectionReasonId = null, ?string $notes = null): Application
    {
        $application->update([
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'rejection_reason_id' => $rejectionReasonId,
            'rejection_notes' => $notes,
            'status' => 'rejected',
        ]);

        return $application->load(['rejectionReason', 'rejectedBy', 'candidate']);
    }
}
