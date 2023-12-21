<?php

namespace App\Livewire;

use App\Models\Comment;
use Illuminate\Http\Response;
use Livewire\Attributes\On;
use Livewire\Component;

class DeleteComment extends Component
{
    public Comment $comment;

    #[On('setDeleteComment')]
    public function setDeleteComment($commentId)
    {
        $this->comment = Comment::findOrFail($commentId);
        $this->dispatch('deleteCommentWasSet');
    }

    public function deleteComment()
    {
        abort_if(auth()->guest() || auth()->user()->cannot('delete', $this->comment), Response::HTTP_FORBIDDEN );

       $this->comment->delete();

        $this->dispatch('commentWasDeleted', 'Comment was deleted!');
    }
}
