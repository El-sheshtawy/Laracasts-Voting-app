<?php

namespace App\Livewire;

use App\Exceptions\DuplicateVoteException;
use App\Exceptions\VoteNotFoundException;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Idea;
use App\Traits\WithAuthRedirects;
use Livewire\Attributes\On;
use Livewire\Component;
use function Laravel\Prompts\alert;

class IdeaShow extends Component
{
    use WithAuthRedirects;

    public Idea $idea;
    public int $votesCount = 0 ;
    public int $commentsCount = 0;
    public bool $hasVoted;

    public function mount(Idea $idea, int $votesCount, int $commentsCount)
    {
        $this->idea = $idea;
        $this->votesCount = $votesCount;
        $this->commentsCount = $commentsCount;
        $this->hasVoted = $idea->isVotedByUser(auth()->user());
    }

    public function vote()
    {
        if (auth()->guest()) {
            return $this->redirectToLogin();
        }

        if ($this->hasVoted) {
            try {
                $this->idea->removeVote(auth()->user());
            } catch (VoteNotFoundException $e) {
                //
            }
            $this->votesCount--;
            $this->hasVoted = false;
        } else {
            try {
                $this->idea->vote(auth()->user());
            } catch (DuplicateVoteException $e) {
               //
            }
            $this->votesCount++;
            $this->hasVoted = true;
        }
    }

    #[On('statusWasUpdated')]
    public function statusWasUpdated()
    {
        $this->commentsCount++;
        $this->idea->refresh();
    }

    #[On('statusWasUpdatedError')]
    public function statusWasUpdatedError()
    {
        $this->idea->refresh();
    }

    #[On('ideaWasMarkedAsSpam')]
    public function ideaWasMarkedAsSpam()
    {
        $this->idea->refresh();}


    #[On('ideaWasMarkedAsNotSpam')]
    public function ideaWasMarkedAsNotSpam()
    {
        $this->idea->refresh();
    }

    #[On('commentWasAdded')]
    public function commentWasAdded()
    {
        $this->commentsCount++;
    }

    #[On('commentWasDeleted')]
    public function commentWasDeleted()
    {
        $this->commentsCount--;
    }
}
