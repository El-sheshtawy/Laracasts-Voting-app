<?php

namespace Tests\Feature\Comments;

use App\Livewire\EditComment;
use App\Livewire\EditIdea;
use App\Livewire\IdeaComment;
use App\Livewire\IdeaShow;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Livewire\Livewire;
use Tests\TestCase;

class EditCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_comment_livewire_show_when_user_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        Comment::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs(user: $user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire('edit-comment');
    }

    public function test_edit_comment_livewire_not_show_when_user_not_authorized()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user1->id,
        ]);

        Comment::factory()->create([
            'user_id' => $user1->id,
        ]);

        $this->actingAs(user: $user2)
            ->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('edit-idea');
    }

    public function test_edit_comment_livewire_not_show_when_user_is_guest()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('edit-idea');
    }

    public function test_edit_comment_component_validation_form_works()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);


        Livewire::actingAs($user)
            ->test(EditComment::class)
            ->call('setEditComment', $comment->id)
            ->set('body', '')
            ->call('updateComment')
            ->assertHasErrors(['body'])
            ->assertSee('The body field is required');
    }

    public function test_edit_comment_is_set_correctly_when_user_clicks_it_from_menu()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'body' => 'This is my first comment',
        ]);

        Livewire::actingAs($user)
            ->test(EditComment::class)
            ->call('setEditComment', $comment->id)
            ->assertSet('body', $comment->body)
            ->assertDispatched('editCommentWasSet');
    }

    public function test_can_edit_comment_successfully_when_authorized()
    {
        $user = User::factory()->create();

        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditComment::class)
            ->call('setEditComment', $comment->id)
            ->set('body', 'Allam Updated')
            ->call('updateComment')
            ->assertSet('body', 'Allam Updated')
            ->assertDispatched('commentWasUpdated');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Allam Updated',
            'idea_id' => $idea->id,
        ]);
    }

    public function test_can_show_edit_comment_sentence_at_idea_show_page_when_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();
        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(IdeaComment::class, [
                'comment' => $comment,
            ])
            ->assertSee('Edit Comment');
    }

    public function test_cannot_show_edit_idea_sentence_at_idea_show_page_when_not_authorized()
    {
        $idea = Idea::factory()->create();

        Livewire::test(IdeaShow::class, [
            'idea' => $idea,
            'votesCount' => 4,
            'commentsCount' => 5
        ])
            ->assertDontSee('Edit Comment');
    }

    public function test_cannot_edit_idea_because_another_user_created_the_idea()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $idea = Idea::factory()->create();

        $comment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'user_id' => $user2->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditComment::class)
            ->call('setEditComment', $comment->id)
            ->set('body', 'Allam Updated')
            ->call('updateComment')
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseMissing('comments', [
            'body' => 'Allam Updated',
        ]);
    }
}
