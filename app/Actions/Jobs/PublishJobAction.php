<?php

namespace App\Actions\Jobs;

use App\Models\JobPosting;

class PublishJobAction
{
    public function execute(JobPosting $job): JobPosting
    {
        $job->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $job->refresh();
    }
}
