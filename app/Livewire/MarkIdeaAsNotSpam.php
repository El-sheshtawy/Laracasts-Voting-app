<?php

namespace App\Livewire;

use App\Models\Idea;
use Illuminate\Http\Response;
use Livewire\Component;

class MarkIdeaAsNotSpam extends Component
{
    public $idea;

    public function mount(Idea $idea)
    {
        $this->idea = $idea;
    }

    public function markAsNotSpam()
    {
        abort_if(auth()->guest() || ! auth()->user()->isAdmin(), Response::HTTP_FORBIDDEN);
        $this->idea->update(['spam_reports' => 0]);
        $this->dispatch('ideaWasMarkedAsNotSpam', 'Spam Counter was reset!');
    }

    public function render()
    {
        return view('livewire.mark-idea-as-not-spam');
    }
}
