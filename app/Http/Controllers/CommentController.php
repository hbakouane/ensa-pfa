<?php

namespace App\Http\Controllers;

use App\Events\CommentAdded;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\MentionedInCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => ['required', 'string', 'in:App\\Models\\Application,App\\Models\\Candidate,App\\Models\\Interview,App\\Models\\Offer'],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
            'is_private' => ['boolean'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = Comment::create([
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::id(),
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'body' => $validated['body'],
            'is_private' => $validated['is_private'] ?? false,
            'parent_id' => $validated['parent_id'] ?? null,
            'mentions' => $this->parseMentions($validated['body']),
        ]);

        $comment->load('user');

        // Notify mentioned users
        $this->notifyMentionedUsers($comment);

        // Broadcast event
        event(new CommentAdded($comment));

        return back()->with('success', 'Commentaire ajouté.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }

    /**
     * Parse @mentions from the comment body.
     */
    protected function parseMentions(string $body): array
    {
        preg_match_all('/@(\w+)/', $body, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Send notifications to mentioned users.
     */
    protected function notifyMentionedUsers(Comment $comment): void
    {
        if (empty($comment->mentions)) {
            return;
        }

        $mentionedUsers = User::where('company_id', Auth::user()->company_id)
            ->whereIn('name', $comment->mentions)
            ->where('id', '!=', Auth::id())
            ->get();

        foreach ($mentionedUsers as $user) {
            $user->notify(new MentionedInCommentNotification($comment));
        }
    }
}
