<?php

namespace Tests\Feature\Comments;

use App\Livewire\AddComment;
use App\Livewire\CommentNotifications;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Livewire;
use Tests\TestCase;

class CommentNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_notifications_livewire_component_renders_when_user_logged_in()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);
        $friend = User::factory()->create();

        Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $friend->id,
        ]);

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire('comment-notifications');
    }

    public function test_comment_notifications_livewire_component_not_renders_when_user_guest()
    {
        $idea = Idea::factory()->create();
        $this->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('comment-notifications');
    }

    public function test_notifications_show_for_logged_in_user()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userACommenting = User::factory()->create();
        $userBCommenting = User::factory()->create();

        Livewire::actingAs($userACommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('body', 'This is the first comment')
            ->call('save');

        Livewire::actingAs($userBCommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('body', 'This is the second comment')
            ->call('save');

        DatabaseNotification::first()->update(['created_at' => now()->subMinute()]);

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->assertSeeInOrder(['This is the second comment', 'This is the first comment'])
            ->assertSet('notificationCount', 2);
    }

    public function test_notification_count_greater_than_threshold_shows_for_logged_in_user()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userACommenting = User::factory()->create();
        $threshold = CommentNotifications::MAX_NOTIFICATION_COUNT;

        foreach (range(1, $threshold + 1) as $item) {
            Livewire::actingAs($userACommenting)
                ->test(AddComment::class, [
                    'idea' => $idea,
                ])
                ->set('body', 'This is the first comment')
                ->call('save');
        }

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->assertSet('notificationCount', $threshold.'+')
            ->assertSee($threshold.'+');
    }

    public function can_mark_all_notifications_as_read()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userACommenting = User::factory()->create();
        $userBCommenting = User::factory()->create();

        Livewire::actingAs($userACommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('comment', 'This is the first comment')
            ->call('addComment');

        Livewire::actingAs($userBCommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('comment', 'This is the second comment')
            ->call('addComment');

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAllAsRead');

        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    public function test_can_mark_individual_notification_as_read()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userACommenting = User::factory()->create();
        $userBCommenting = User::factory()->create();

        Livewire::actingAs($userACommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('body', 'This is the first comment')
            ->call('save');

        Livewire::actingAs($userBCommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('body', 'This is the second comment')
            ->call('save');

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAsRead', DatabaseNotification::first()->id)
            ->assertRedirect(route('idea.show', [
                'idea' => $idea,
                'page' => 1,
            ]));

        $this->assertEquals(1, $user->fresh()->unreadNotifications->count());
    }

    public function test_notification_idea_deleted_redirects_to_index_page()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userACommenting = User::factory()->create();

        Livewire::actingAs($userACommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('body', 'This is the first comment')
            ->call('save');

        $idea->delete();

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAsRead', DatabaseNotification::first()->id)
            ->assertRedirect(route('idea.index'));
    }

    /** @test */
    public function test_notification_comment_deleted_redirects_to_index_page()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $userACommenting = User::factory()->create();

        Livewire::actingAs($userACommenting)
            ->test(AddComment::class, [ 'idea' => $idea, ])
            ->set('body', 'This is the first comment')
            ->call('save');

        $idea->comments()->delete();

        Livewire::actingAs($user)
            ->test(CommentNotifications::class)
            ->call('getNotifications')
            ->call('markAsRead', DatabaseNotification::first()->id)
            ->assertRedirect(route('idea.index'));
    }
}
