<?php

namespace App\Jobs;

use App\Mail\IdeaStatusUpdatedMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyAllVoters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $idea;

    /**
     * Create a new job instance.
     */
    public function __construct($idea)
    {
        $this->idea = $idea;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->idea->votes()
            ->select('name', 'email')
            ->chunk(50, function ($voters) {
                foreach ($voters as $voter) {
                    Mail::to($voter)
                        ->queue(new IdeaStatusUpdatedMailable($this->idea));
                }
            });
    }
}
