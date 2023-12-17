<?php

namespace Tests\Feature\Comments;

use App\Livewire\AddComment;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use App\Notifications\CommentAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AddCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_see_add_comment_livewire_component_renders()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('add-comment');
    }

    public function test_show_message_to_unauthenticated_user_if_try_to_add_comment()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertSeeLivewire('add-comment')
            ->assertSee('Please login or create an account to post a comment.');
    }

    public function test_show_add_comment_form_if_user_is_authenticated()
    {
        $idea = Idea::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire('add-comment')
            ->assertSee('Post Comment');
    }

    public function test_add_comment_form_validation_required_works()
    {
        $idea = Idea::factory()->create();
        $user = User::factory()->create();

       Livewire::actingAs($user)
           ->test(AddComment::class,[
               'idea' => $idea
           ])
           ->set('body', '')
           ->call('addComment')
           ->assertHasErrors(['body'])
           ->assertSee('The body field is required');
    }

    public function test_add_comment_form_validation_min_works()
    {
        $idea = Idea::factory()->create();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AddComment::class,[
                'idea' => $idea
            ])
            ->set('body', 'a')
            ->call('addComment')
            ->assertHasErrors(['body'])
            ->assertSee('The body field must be at least 2 characters.');
    }

    public function test_add_comment_works()
    {
        $idea = Idea::factory()->create();
        $user = User::factory()->create();

        Notification::fake();

        // Perform order shipping...

        // Assert that no notifications were sent...
        Notification::assertNothingSent();

        Livewire::actingAs($user)
            ->test(AddComment::class,[
                'idea' => $idea
            ])
            ->set('body', 'Body comment')
            ->set('idea_id', $idea->id)
            ->set('user_id', $user->id)
            ->call('addComment')
            ->assertDispatched('commentWasAdded');

        Notification::assertSentTo(
            [$idea->user], CommentAdded::class
        );

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'idea_id' => $idea->id,
            'body' => 'Body comment',
        ]);

        $this->assertEquals(1, Comment::count());
    }

    public function test_comments_pagination_works()
    {
        $idea = Idea::factory()->create();

        $firstComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First Comment',
        ]);

        Comment::factory()->count($firstComment->getPerPage())->create([
            'idea_id' => $idea->id,
        ]);

        $response = $this->get(route('idea.show', $idea));

        $response->assertSee($firstComment->body);
        $response->assertDontSee(Comment::find(Comment::count())->body);

        $response2 = $this->get(route('idea.show', [
            'idea' => $idea,
            'page' => 2,
        ]));

        $response2->assertSee(Comment::find(Comment::count())->body);
        $response2->assertDontSee($firstComment->body);

    }
}
