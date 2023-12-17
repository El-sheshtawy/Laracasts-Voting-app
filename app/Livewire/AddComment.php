<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Idea;
use App\Notifications\CommentAdded;
use App\Traits\WithAuthRedirects;
use Illuminate\Http\Response;
use Livewire\Attributes\Rule;
use Livewire\Component;

class AddComment extends Component
{
    use WithAuthRedirects;

    public Idea $idea;

    #[Rule('required|min:2')]
    public string $body = '';

    public function addComment()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $this->validate();

        $comment = Comment::create([
            'idea_id' => $this->idea->id,
            'user_id' => auth()->id(),
            'body' => $this->body,
            'status_id' => 1,
        ]);

        $this->reset('body');

        $this->dispatch('commentWasAdded', 'Comment was posted!');

        $this->idea->user->notify(new CommentAdded($comment));
    }

    public function render()
    {
        return view('livewire.add-comment');
    }
}
