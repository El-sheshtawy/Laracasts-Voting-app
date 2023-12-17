<?php

namespace App\Livewire;

use App\Models\Comment;
use Livewire\Attributes\On;
use Livewire\Component;

class IdeaComment extends Component
{
    public Comment $comment;

    public function mount(Comment $comment)
    {
        $this->comment = $comment;
    }

    #[On('commentWasUpdated')]
    public function commentWasUpdated()
    {
        $this->comment->refresh();
    }

    #[On('commentWasMarkedAsSpam')]
    public function commentWasMarkedAsSpam()
    {
        $this->comment->refresh();
    }

    #[On('commentWasMarkedAsNotSpam')]
    public function commentWasMarkedAsNotSpam()
    {
        $this->comment->refresh();
    }

    public function render()
    {
        return view('livewire.idea-comment');
    }
}
