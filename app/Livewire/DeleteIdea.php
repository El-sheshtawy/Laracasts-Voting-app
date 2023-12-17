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
    public $idea;

    public function mount(Idea $idea)
    {
        $this->idea = $idea;
    }

    public function deleteIdea()
    {
        abort_if(auth()->guest() || auth()->user()->cannot('delete', $this->idea), Response::HTTP_FORBIDDEN);

        $this->idea->delete();
        session()->flash('success_message', 'Idea was deleted successfully!');

        return to_route('idea.index');
    }

    public function render()
    {
        return view('livewire.delete-idea');
    }
}
