<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsService;
use App\Services\Analytics\PipelineConversionCalculator;
use App\Services\Analytics\TimeToHireCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private TimeToHireCalculator $timeToHireCalculator,
        private PipelineConversionCalculator $pipelineConversionCalculator,
    ) {}

    /**
     * Overview analytics dashboard.
     */
    public function index(Request $request): Response
    {
        $company = $request->user()->company;

        $overview = $this->analyticsService->overview($company);

        return Inertia::render('Analytics/Index', [
            'overview' => $overview,
            'company' => $company,
        ]);
    }

    /**
     * Time-to-hire analytics data.
     */
    public function timeToHire(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $from = $request->input('from') ? Carbon::parse($request->input('from')) : null;
        $to = $request->input('to') ? Carbon::parse($request->input('to')) : null;

        $data = $this->timeToHireCalculator->calculate($company, $from, $to);

        return response()->json($data);
    }

    /**
     * Pipeline conversion analytics data.
     */
    public function pipelineConversion(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $jobId = $request->input('job_id') ? (int) $request->input('job_id') : null;

        $data = $this->pipelineConversionCalculator->calculate($company, $jobId);

        return response()->json($data);
    }

    /**
     * Source tracking breakdown.
     */
    public function sources(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $from = $request->input('from') ? Carbon::parse($request->input('from')) : now()->subDays(30);
        $to = $request->input('to') ? Carbon::parse($request->input('to')) : now();

        $sources = DB::table('source_tracking')
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'source' => $row->source,
                'count' => $row->count,
            ])
            ->toArray();

        return response()->json(['sources' => $sources]);
    }

    /**
     * Team performance stats: interviews conducted, offers made, hires per user.
     */
    public function teamPerformance(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $from = $request->input('from') ? Carbon::parse($request->input('from')) : now()->subDays(30);
        $to = $request->input('to') ? Carbon::parse($request->input('to')) : now();

        $users = $company->users()->get(['id', 'name', 'email']);

        $performance = $users->map(function ($user) use ($company, $from, $to) {
            $interviewsConducted = DB::table('interview_interviewers')
                ->join('interviews', 'interview_interviewers.interview_id', '=', 'interviews.id')
                ->where('interviews.company_id', $company->id)
                ->where('interview_interviewers.user_id', $user->id)
                ->where('interviews.status', 'completed')
                ->whereBetween('interviews.scheduled_at', [$from, $to])
                ->count();

            $offersMade = DB::table('offers')
                ->where('company_id', $company->id)
                ->where('created_by', $user->id)
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $hires = DB::table('application_stage_history as ash')
                ->join('pipeline_stages as ps', 'ash.to_stage_id', '=', 'ps.id')
                ->join('applications as a', 'ash.application_id', '=', 'a.id')
                ->where('a.company_id', $company->id)
                ->where('ps.slug', 'hired')
                ->where('ash.moved_by', $user->id)
                ->whereBetween('ash.created_at', [$from, $to])
                ->count();

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'interviews_conducted' => $interviewsConducted,
                'offers_made' => $offersMade,
                'hires' => $hires,
            ];
        })->toArray();

        return response()->json(['team' => $performance]);
    }
}
