<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Idea;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentNotifications extends Component
{
    const MAX_NOTIFICATION_COUNT = 20;
    public $notificationCount = 0;
    public bool $isLoading;

    #[Computed]
    public function notifications()
    {
        return Auth::user()
            ->unreadNotifications()
            ->select(['id', 'data', 'created_at'])
            ->where('data->user_id', '<>', auth()->id())
            ->latest()
            ->take(self::MAX_NOTIFICATION_COUNT)
            ->get();
    }

    public function mount()
    {
        $this->isLoading = true;
        $this->getNotificationCount();
    }

    #[On('getNotifications')]
    public function getNotifications()
    {
        $this->isLoading = false;
        $this->notifications();
    }

    public function getNotificationCount()
    {
        if (auth()->check()) {
            $this->notificationCount = auth()->user()->unreadNotifications()
                ->where('data->user_id', '<>', auth()->id())
                ->count();
            if ($this->notificationCount > self::MAX_NOTIFICATION_COUNT) {
                $this->notificationCount = self::MAX_NOTIFICATION_COUNT.'+';
            }
        }
    }

    public function markAllAsRead()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $this->notifications->each->markAsRead();
        $this->getNotificationCount();
        $this->getNotifications();
    }


    public function markAsRead($notificationId)
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $notification = DatabaseNotification::findOrFail($notificationId);
        $notification->markAsRead();

        $this->scrollToComment($notification);
    }

    public function scrollToComment($notification)
    {
        $idea = Idea::find($notification->data['idea_id']);

        if (! $idea || $idea->isDirty('slug')) {
            session()->flash('error_message', 'This idea no longer exists!');

            return redirect()->route('idea.index');
        }

        $comment = Comment::find($notification->data['comment_id']);
        if (! $comment) {
            session()->flash('error_message', 'This comment no longer exists!');

            return redirect()->route('idea.index');
        }

        $comments = $idea->comments()->pluck('id');
        $indexOfComment = $comments->search($comment->id);

        $page = (int) ($indexOfComment / $comment->getPerPage()) + 1;

        session()->flash('scrollToComment', $comment->id);

        return redirect()->route('idea.show', [
            'idea' => $notification->data['idea_slug'],
            'page' => $page,
        ]);
    }
}
