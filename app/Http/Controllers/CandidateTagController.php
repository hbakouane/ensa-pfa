<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Tag;
use Illuminate\Http\Request;

class CandidateTagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    public function attachToCandidate(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $candidate->tags()->syncWithoutDetaching($validated['tag_ids']);

        return response()->json([
            'message' => 'Tags attachés avec succès.',
            'tags' => $candidate->tags()->get(),
        ]);
    }

    public function detachFromCandidate(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ]);

        $candidate->tags()->detach($validated['tag_ids']);

        return response()->json([
            'message' => 'Tags détachés avec succès.',
            'tags' => $candidate->tags()->get(),
        ]);
    }
}
