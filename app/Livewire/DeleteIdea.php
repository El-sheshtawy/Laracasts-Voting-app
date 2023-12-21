<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Idea;
use App\Models\Vote;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DeleteIdea extends Component
{
    public Idea $idea;

    public function mount(Idea $idea)
    {
        $this->idea = $idea;
    }

    public function deleteIdea()
    {
        abort_if(auth()->guest() || auth()->user()->cannot('delete', $this->idea), Response::HTTP_FORBIDDEN);

        $this->idea->delete();

        return to_route('idea.index')
            ->with('success_message', 'Idea was deleted successfully!');
    }
}
