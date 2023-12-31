<?php

namespace App\Livewire;

use App\Models\Comment;
use Illuminate\Http\Response;
use Livewire\Attributes\On;
use Livewire\Component;

class MarkCommentAsSpam extends Component
{
    public Comment $comment;

    #[On('setMarkAsSpamComment')]
    public function setMarkAsSpamComment($commentId)
    {
        $this->comment = Comment::findOrFail($commentId);

        $this->dispatch('markAsSpamCommentWasSet');
    }

    public function markAsSpam()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN );

        $this->comment->increment('spam_reports');

        $this->dispatch('commentWasMarkedAsSpam', 'Comment was marked as spam!');
    }
}
