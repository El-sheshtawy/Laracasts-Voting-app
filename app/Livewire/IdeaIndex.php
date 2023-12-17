<?php

namespace App\Livewire;

use App\Exceptions\DuplicateVoteException;
use App\Exceptions\VoteNotFoundException;
use App\Models\Idea;
use App\Traits\WithAuthRedirects;
use Livewire\Component;

class IdeaIndex extends Component
{
    use WithAuthRedirects;

    public $idea;
    public $votesCount;
    public $hasVoted;

    public function mount(Idea $idea, $votesCount)
    {
        $this->idea = $idea;
        $this->votesCount = $votesCount;
        $this->hasVoted = $idea->voted_by_user;
    }

    public function vote()
    {
        if (auth()->guest()) {
            return $this->redirectToLogin();
        } else {
            if ($this->hasVoted) {
                try {
                    $this->idea->removeVote(auth()->user());
                } catch (VoteNotFoundException $e) {
                    // do nothing
                }
                $this->votesCount--;
                $this->hasVoted = false;
            } else {
                try {
                    $this->idea->vote(auth()->user());
                } catch (DuplicateVoteException $e) {
                    // do nothing
                }
                $this->votesCount++;
                $this->hasVoted = true;
            }
        }
    }

    public function render()
    {
        return view('livewire.idea-index');
    }
}
