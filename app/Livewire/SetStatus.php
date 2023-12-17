<?php

namespace App\Livewire;

use App\Jobs\NotifyAllVoters;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\Status;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SetStatus extends Component
{
    public $idea;
    public $body;
    public $status;
    public $notifyAllVoters;

    public function mount(Idea $idea)
    {
        $this->idea = $idea;
        $this->status = $this->idea->status_id;
    }

    public function setStatus()
    {
        abort_if(auth()->guest() || ! auth()->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        if ($this->idea->status_id === (int) $this->status) {
            $this->dispatch('statusWasUpdatedError', 'Status is the same!');
            return;
        }

        DB::transaction(function () {
            $this->idea->update(['status_id' => $this->status]);

            Comment::create([
                'user_id' => auth()->id(),
                'idea_id' => $this->idea->id,
                'status_id' => $this->status,
                'body' => $this->body ?? '',
                'is_status_update' => true,
            ]);

            if($this->notifyAllVoters) {
                NotifyAllVoters::dispatch($this->idea);
            }

            $this->reset('body');

            $this->dispatch('statusWasUpdated', 'Status was updated successfully!');
        });
    }


    public function render()
    {
        return view('livewire.set-status', [
            'statuses' => Status::select('id', 'name')->get(),
        ]);
    }
}
