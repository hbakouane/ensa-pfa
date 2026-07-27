<?php

namespace App\Http\Controllers;

use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    public function index(): JsonResponse
    {
        $stages = PipelineStage::orderBy('position')->get();

        return response()->json($stages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'position' => ['required', 'integer', 'min:0'],
        ]);

        $stage = PipelineStage::create($validated);

        return response()->json($stage, 201);
    }

    public function update(Request $request, PipelineStage $stage): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $stage->update($validated);

        return response()->json($stage);
    }

    public function destroy(PipelineStage $stage): JsonResponse
    {
        // Prevent deleting stages that have applications
        if ($stage->applications()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer une étape du pipeline qui contient des candidatures. Déplacez ou supprimez d\'abord les candidatures.',
            ], 422);
        }

        $stage->delete();

        return response()->json([
            'message' => 'Étape du pipeline supprimée avec succès.',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stages' => ['required', 'array'],
            'stages.*.id' => ['required', 'exists:pipeline_stages,id'],
            'stages.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['stages'] as $stageData) {
            PipelineStage::where('id', $stageData['id'])->update([
                'position' => $stageData['position'],
            ]);
        }

        $stages = PipelineStage::orderBy('position')->get();

        return response()->json([
            'message' => 'Étapes du pipeline réorganisées avec succès.',
            'stages' => $stages,
        ]);
    }
}
