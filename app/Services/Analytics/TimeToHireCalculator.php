<?php

namespace App\Services\Analytics;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeToHireCalculator
{
    /**
     * Calculate time-to-hire metrics for a company.
     *
     * @return array{
     *     average_days: float|null,
     *     median_days: float|null,
     *     by_department: array,
     *     by_job: array,
     *     trend: array,
     * }
     */
    public function calculate(Company $company, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(90);
        $to = $to ?? now();

        $hires = $this->getHireData($company, $from, $to);

        $days = $hires->pluck('days_to_hire')->filter()->values();

        return [
            'average_days' => $days->isNotEmpty() ? round($days->avg(), 1) : null,
            'median_days' => $days->isNotEmpty() ? round($this->median($days->toArray()), 1) : null,
            'by_department' => $this->byDepartment($company, $from, $to),
            'by_job' => $this->byJob($company, $from, $to),
            'trend' => $this->trend($company, $from, $to),
        ];
    }

    /**
     * Get all hire events with the number of days between application and hire.
     */
    private function getHireData(Company $company, Carbon $from, Carbon $to)
    {
        return DB::table('application_stage_history as ash')
            ->join('pipeline_stages as ps', 'ash.to_stage_id', '=', 'ps.id')
            ->join('applications as a', 'ash.application_id', '=', 'a.id')
            ->where('a.company_id', $company->id)
            ->where('ps.slug', 'hired')
            ->whereBetween('ash.created_at', [$from, $to])
            ->select([
                'a.id as application_id',
                'a.job_id',
                'a.created_at as applied_at',
                'ash.created_at as hired_at',
                DB::raw('DATEDIFF(ash.created_at, a.created_at) as days_to_hire'),
            ])
            ->get();
    }

    /**
     * Calculate time-to-hire broken down by department.
     */
    private function byDepartment(Company $company, Carbon $from, Carbon $to): array
    {
        return DB::table('application_stage_history as ash')
            ->join('pipeline_stages as ps', 'ash.to_stage_id', '=', 'ps.id')
            ->join('applications as a', 'ash.application_id', '=', 'a.id')
            ->join('jobs as j', 'a.job_id', '=', 'j.id')
            ->leftJoin('departments as d', 'j.department_id', '=', 'd.id')
            ->where('a.company_id', $company->id)
            ->where('ps.slug', 'hired')
            ->whereBetween('ash.created_at', [$from, $to])
            ->groupBy('d.id', 'd.name')
            ->select([
                'd.name as department',
                DB::raw('ROUND(AVG(DATEDIFF(ash.created_at, a.created_at)), 1) as average_days'),
                DB::raw('COUNT(*) as hires'),
            ])
            ->get()
            ->map(fn ($row) => [
                'department' => $row->department ?? 'Unassigned',
                'average_days' => (float) $row->average_days,
                'hires' => $row->hires,
            ])
            ->toArray();
    }

    /**
     * Calculate time-to-hire broken down by job.
     */
    private function byJob(Company $company, Carbon $from, Carbon $to): array
    {
        return DB::table('application_stage_history as ash')
            ->join('pipeline_stages as ps', 'ash.to_stage_id', '=', 'ps.id')
            ->join('applications as a', 'ash.application_id', '=', 'a.id')
            ->join('jobs as j', 'a.job_id', '=', 'j.id')
            ->where('a.company_id', $company->id)
            ->where('ps.slug', 'hired')
            ->whereBetween('ash.created_at', [$from, $to])
            ->groupBy('j.id', 'j.title')
            ->select([
                'j.title as job',
                DB::raw('ROUND(AVG(DATEDIFF(ash.created_at, a.created_at)), 1) as average_days'),
                DB::raw('COUNT(*) as hires'),
            ])
            ->get()
            ->map(fn ($row) => [
                'job' => $row->job,
                'average_days' => (float) $row->average_days,
                'hires' => $row->hires,
            ])
            ->toArray();
    }

    /**
     * Calculate time-to-hire trend over monthly periods.
     */
    private function trend(Company $company, Carbon $from, Carbon $to): array
    {
        return DB::table('application_stage_history as ash')
            ->join('pipeline_stages as ps', 'ash.to_stage_id', '=', 'ps.id')
            ->join('applications as a', 'ash.application_id', '=', 'a.id')
            ->where('a.company_id', $company->id)
            ->where('ps.slug', 'hired')
            ->whereBetween('ash.created_at', [$from, $to])
            ->groupBy(DB::raw("DATE_FORMAT(ash.created_at, '%Y-%m')"))
            ->orderBy('period')
            ->select([
                DB::raw("DATE_FORMAT(ash.created_at, '%Y-%m') as period"),
                DB::raw('ROUND(AVG(DATEDIFF(ash.created_at, a.created_at)), 1) as days'),
            ])
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'days' => (float) $row->days,
            ])
            ->toArray();
    }

    /**
     * Calculate the median of an array of numbers.
     */
    private function median(array $values): float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return 0;
        }

        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }
}
