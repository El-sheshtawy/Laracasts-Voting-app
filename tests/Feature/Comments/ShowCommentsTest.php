<?php

namespace Tests\Feature\Comments;

use App\Models\Comment;
use App\Models\Idea;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowCommentsTest extends TestCase
{
    use RefreshDatabase;

   public function test_idea_comments_livewire_component_renders()
   {
       $idea = Idea::factory()->create();

       Comment::factory()->create([
           'idea_id' => $idea->id,
           'user_id' => User::factory()->create()->id,
           'body' => 'This is the first comment',
           'status_id' => Status::factory()->create()->id,
       ]);

      $response =  $this->get(route('idea.show', $idea));
      $response->assertSeeLivewire('idea-comments');

      $response->assertSee('This is the first comment');
   }

    public function test_comments_can_show_in_show_page()
    {
        $idea = Idea::factory()->create();

        $firstComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First Comment',
        ]);

        $secondComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'Second Comment',
        ]);

        Comment::factory(10)->create(['idea_id' => $idea->id]);

        $this->get(route('idea.show', $idea))
            ->assertStatus(200)
            ->assertSee('First Comment')
            ->assertDontSee('Allam is the man')
            ->assertSee('12 comments')
            ->assertSeeInOrder(['First Comment', 'Second Comment']);

        $this->assertEquals(12, $idea->comments()->count());
    }

    public function test_comments_can_not_show_idea_not_has_comments()
    {
        $idea1 = Idea::factory()->create();
        $idea2 = Idea::factory()->create();

        Comment::factory()->create([
            'idea_id' => $idea2->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'This is the first comment',
            'status_id' => Status::factory()->create()->id,
        ]);

        $response = $this->get(route('idea.show', $idea1));
        $response->assertStatus(200);
        $response->assertSee('No comments yet...');
        $response->assertDontSee('This is the first comment');
        $this->assertEquals(0, $idea1->comments()->count());
    }

    public function test_idea_comments_count_show_correctly_in_the_index_page()
    {
        $idea = Idea::factory()->create();

        $firstComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First Comment',
        ]);

        $secondComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'Second Comment',
        ]);

        $this->get(route('idea.index'))->assertSee('2 comments');
    }

    public function test_op_badge_shows_if_comment_writer_is_idea_writer()
    {
        $user = User::factory()->create();

        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $firstComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'First Comment',
            'user_id' => $user->id,
        ]);

        $secondComment = Comment::factory()->create([
            'idea_id' => $idea->id,
            'body' => 'Second Comment',
            'user_id' => $user->id,
        ]);

        $this->get(route('idea.show', $idea))
            ->assertSee('OP');
    }
}
