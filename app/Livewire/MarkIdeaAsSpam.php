<?php

namespace App\Livewire;

use App\Models\Idea;
use Illuminate\Http\Response;
use Livewire\Component;

class MarkIdeaAsSpam extends Component
{
    public $idea;

    public function mount(Idea $idea)
    {
        $this->idea = $idea;
    }

    public function markAsSpam()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $this->idea->increment('spam_reports');

        $this->dispatch('ideaWasMarkedAsSpam', 'Idea was marked as spam!');
    }
}
