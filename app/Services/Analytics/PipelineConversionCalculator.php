<?php

namespace App\Services\Analytics;

use App\Models\Company;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;

class PipelineConversionCalculator
{
    /**
     * Calculate pipeline conversion rates across stages.
     *
     * @return array{
     *     stages: array<int, array{name: string, count: int, conversion_rate: float, drop_off_rate: float}>,
     *     overall_conversion_rate: float,
     * }
     */
    public function calculate(Company $company, ?int $jobId = null): array
    {
        $stages = PipelineStage::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->get();

        if ($stages->isEmpty()) {
            return [
                'stages' => [],
                'overall_conversion_rate' => 0,
            ];
        }

        $stageCounts = [];
        $firstStageCount = 0;

        foreach ($stages as $index => $stage) {
            $query = DB::table('applications')
                ->where('company_id', $company->id)
                ->whereNull('deleted_at');

            if ($jobId) {
                $query->where('job_id', $jobId);
            }

            // Count candidates who have been in this stage or later
            // Either currently in this stage, or have stage history showing they passed through it
            $currentCount = (clone $query)->where('pipeline_stage_id', $stage->id)->count();

            $passedThroughCount = DB::table('application_stage_history as ash')
                ->join('applications as a', 'ash.application_id', '=', 'a.id')
                ->where('a.company_id', $company->id)
                ->whereNull('a.deleted_at')
                ->where('ash.from_stage_id', $stage->id)
                ->when($jobId, fn ($q) => $q->where('a.job_id', $jobId))
                ->distinct('ash.application_id')
                ->count('ash.application_id');

            $count = $currentCount + $passedThroughCount;

            if ($index === 0) {
                $firstStageCount = $count;
            }

            $stageCounts[] = [
                'name' => $stage->name,
                'count' => $count,
            ];
        }

        // Calculate conversion and drop-off rates
        $stagesWithRates = [];
        $previousCount = null;

        foreach ($stageCounts as $index => $stageData) {
            $conversionRate = 0;
            $dropOffRate = 0;

            if ($previousCount !== null && $previousCount > 0) {
                $conversionRate = round(($stageData['count'] / $previousCount) * 100, 1);
                $dropOffRate = round(100 - $conversionRate, 1);
            } elseif ($index === 0) {
                $conversionRate = 100;
                $dropOffRate = 0;
            }

            $stagesWithRates[] = [
                'name' => $stageData['name'],
                'count' => $stageData['count'],
                'conversion_rate' => $conversionRate,
                'drop_off_rate' => $dropOffRate,
            ];

            $previousCount = $stageData['count'];
        }

        $lastStageCount = end($stageCounts)['count'] ?? 0;
        $overallConversionRate = $firstStageCount > 0
            ? round(($lastStageCount / $firstStageCount) * 100, 2)
            : 0;

        return [
            'stages' => $stagesWithRates,
            'overall_conversion_rate' => $overallConversionRate,
        ];
    }
}
