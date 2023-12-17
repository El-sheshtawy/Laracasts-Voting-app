<?php

namespace App\Livewire;

use App\Models\Comment;
use Illuminate\Http\Response;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EditComment extends Component
{
    public Comment $comment;

    #[Rule('required|min:4')]
    public $body;

    #[On('setEditComment')]
    public function setEditComment($commentId): void
    {
        $this->comment = Comment::findOrFail($commentId);
        $this->body = $this->comment->body;

        $this->dispatch('editCommentWasSet');
    }

    public function updateComment(): void
    {
        if (auth()->guest() || auth()->user()->cannot('update', $this->comment)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->validate();
        $this->comment->update([
            'body' => $this->body,
        ]);

        $this->dispatch('commentWasUpdated', 'Comment was updated!');
    }

    public function render()
    {
        return view('livewire.edit-comment');
    }
}
