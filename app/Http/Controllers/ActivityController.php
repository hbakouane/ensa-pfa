<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'subject_type' => ['required', 'string'],
            'subject_id' => ['required', 'integer'],
        ]);

        $activities = Activity::where('subject_type', $request->input('subject_type'))
            ->where('subject_id', $request->input('subject_id'))
            ->with('user')
            ->latest('created_at')
            ->paginate(20);

        return response()->json($activities);
    }
}
