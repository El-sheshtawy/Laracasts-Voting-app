<?php

namespace Tests\Feature\Comments;

use App\Livewire\DeleteComment;
use App\Livewire\IdeaComment;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteCommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_delete_comment_not_show_when_idea_not_have_comments()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $idea->comments()->count() === 0;

        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSee('delete-comment');
    }

    /** @test */
    public function test_shows_delete_comment_livewire_component_when_user_has_authorization()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();
        Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);
        $this->actingAs($user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire('delete-comment');
    }

    /** @test */
    public function test_does_not_show_delete_comment_livewire_component_when_user_does_not_have_authorization()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('delete-comment');
    }

    /** @test */
    public function test_delete_comment_is_set_correctly_when_user_clicks_it_from_menu()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'body' => 'This is my first comment',
        ]);

        Livewire::actingAs($user)
            ->test(DeleteComment::class)
            ->call('setDeleteComment', $comment->id)
            ->assertDispatched('deleteCommentWasSet');
    }

    /** @test */
    public function test_deleting_a_comment_works_when_user_has_authorization()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'body' => 'This is my first comment',
        ]);

        Livewire::actingAs($user)
            ->test(DeleteComment::class)
            ->call('setDeleteComment', $comment->id)
            ->call('deleteComment')
            ->assertDispatched('commentWasDeleted');

        $this->assertEquals(0, Comment::count());
    }

    /** @test */
    public function test_deleting_a_comment_does_not_work_when_user_does_not_have_authorization()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'This is my first comment',
        ]);

        Livewire::actingAs($user)
            ->test(DeleteComment::class)
            ->call('setDeleteComment', $comment->id)
            ->call('deleteComment')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function test_deleting_a_comment_shows_on_menu_when_user_has_authorization()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
            'body' => 'This is my first comment',
        ]);

        Livewire::actingAs($user)
            ->test(IdeaComment::class, [
                'comment' => $comment,
            ])
            ->assertSee('Delete Comment');
    }

    /** @test */
    public function test_deleting_a_comment_does_not_show_on_menu_when_user_does_not_have_authorization()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'This is my first comment',
        ]);

        Livewire::actingAs($user)
            ->test(IdeaComment::class, [
                'comment' => $comment,
            ])
            ->assertDontSee('Delete Comment');
    }

    /** @test */
    public function test_admin_can_test_any_comment_even_if_admin_not_comment_writter()
    {
        $admin = User::factory()->create([
            'email' => 'ramyalfe22@gmail.com',
        ]);

        $user = User::factory()->create();

        $comment = Comment::factory()->create([
            'body' => 'Allam is super man',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($admin)
            ->test(DeleteComment::class)
            ->call('setDeleteComment', $comment->id)
            ->assertDispatched('deleteCommentWasSet')
            ->call('deleteComment', $comment->id)
            ->assertDispatched('commentWasDeleted');

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
            'body' => 'Allam is super man',
            'user_id' => $user->id,
        ]);
    }
}
