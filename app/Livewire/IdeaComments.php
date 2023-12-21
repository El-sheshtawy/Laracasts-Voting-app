<?php

namespace App\Livewire;


use App\Models\Idea;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class IdeaComments extends Component
{
    use WithPagination;

    public Idea $idea;

    public function mount(Idea $idea): void
    {
        $this->idea = $idea;
        $this->idea->load('comments');
    }

    #[Computed]
    public function comments()
    {
        return $this->idea->comments()
            ->select(['id', 'idea_id', 'user_id', 'body', 'is_status_update', 'status_id', 'created_at'])
            ->with(['idea:id,user_id', 'status:id,name', 'user:id,name,email'])
            ->paginate();
    }

    #[On('commentWasAdded')]
    public function commentWasAdded()
    {
        $this->idea->refresh();
        $this->goToPage($this->idea->comments()->paginate()->lastPage());
    }

    #[On('commentWasDeleted')]
    public function commentWasDeleted()
    {
        $this->idea->refresh();
        $this->goToPage(1);
    }

    #[On('statusWasUpdated')]
    public function statusWasUpdated()
    {
        $this->idea->refresh();
        $this->goToPage($this->idea->comments()->paginate()->lastPage());
    }
}
