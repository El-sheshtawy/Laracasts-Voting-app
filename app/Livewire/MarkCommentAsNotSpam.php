<?php

namespace App\Livewire;

use App\Models\Comment;
use Illuminate\Http\Response;
use Livewire\Attributes\On;
use Livewire\Component;

class MarkCommentAsNotSpam extends Component
{
    public Comment $comment;

    #[On('setMarkAsNotSpamComment')]
    public function setMarkAsNotSpamComment($commentId)
    {
        $this->comment = Comment::findOrFail($commentId);

        $this->dispatch('markAsNotSpamCommentWasSet');
    }

    public function markAsNotSpam()
    {
        abort_if(auth()->guest() || ! auth()->user()->isAdmin(), Response::HTTP_FORBIDDEN );

        $this->comment->update([
            'spam_reports' => 0,
        ]);

        $this->dispatch('commentWasMarkedAsNotSpam', 'Comment spam counter was reset!');
    }

    public function render()
    {
        return view('livewire.mark-comment-as-not-spam');
    }
}
