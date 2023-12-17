<?php

namespace Tests\Feature;

use App\Livewire\DeleteIdea;
use App\Livewire\IdeaShow;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteIdeaTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_idea_livewire_show_when_user_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs(user: $user)
            ->get(route('idea.show', $idea))
            ->assertSeeLivewire('delete-idea');
    }

    public function test_delete_idea_livewire_not_show_when_user_not_authorized()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user1->id,
        ]);

        $this->actingAs(user: $user2)
            ->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('delete-idea');
    }

    public function test_delete_idea_livewire_not_show_when_user_is_guest()
    {

        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('delete-idea');
    }

    public function test_can_delete_idea_successfully_when_authorized()
    {
        $user = User::factory()->create();
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea = Idea::factory()->create([
            'id' => 1000,
            'title' => 'Allam Updated',
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'description' => 'this is the allam updated title',
        ]);

        Livewire::actingAs($user)
            ->test(DeleteIdea::class, [
                'idea' => $idea
            ])
            ->call('deleteIdea')
            ->assertRedirect(route('idea.index'));

        $this->assertEquals(0, Idea::count());

        $this->assertDatabaseMissing('ideas', [
            'id' => 1000,
            'title' => 'Allam Updated',
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'description' => 'this is the allam updated title'
        ]);
    }

    public function test_can_show_delete_idea_sentence_at_idea_show_page_when_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(IdeaShow::class, [
                'idea' => $idea,
                'votesCount' => 4,
                'commentsCount' => 5
            ])
            ->assertSee('Delete Idea');
    }

    public function test_cannot_show_delete_idea_sentence_at_idea_show_page_when_not_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        Livewire::test(IdeaShow::class, [
            'idea' => $idea,
            'votesCount' => 4,
            'commentsCount' => 5
        ])
            ->assertDontSee('Delete Idea');
    }

    public function test_cannot_delete_idea_because_another_user_created_the_idea()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea = Idea::factory()->create([
            'id' => 400 ,
            'title' => 'Allam Updated',
            'category_id' =>  $categoryTwo->id,
            'description' => 'this is the allam updated title',
        ]);

        Livewire::actingAs($user2)
            ->test(DeleteIdea::class, [
                'idea' => $idea
            ])
            ->call('deleteIdea')
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('ideas', [
            'id' => 400 ,
            'title' => 'Allam Updated',
            'category_id' =>  $categoryTwo->id,
            'description' => 'this is the allam updated title',
        ]);
    }

    public function test_can_delete_idea_because_user_is_admin()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'email' => 'ramyalfe22@gmail.com',
        ]);

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea = Idea::factory()->create([
            'id' => 2222,
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'created_at' => now()->subHours(4),
        ]);

        Livewire::actingAs($admin)
            ->test(DeleteIdea::class, [
                'idea' => $idea
            ])
            ->call('deleteIdea')
            ->assertRedirect(route('idea.index'));

        $this->assertDatabaseMissing('ideas', [
            'id' => 2222,
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'created_at' => now()->subHours(4),
        ]);
    }

    public function test_delete_idea_with_votes()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        Vote::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);

        Livewire::actingAs($user)
            ->test(DeleteIdea::class, [
            'idea' => $idea,
                ])
            ->call('deleteIdea')
            ->assertRedirect(route('idea.index'));

        $this->assertEquals(0, Vote::count());
        $this->assertEquals(0, Idea::count());
    }

    public function test_delete_idea_with_comments()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        Comment::factory()->create([
            'user_id' => $user->id,
            'idea_id' => $idea->id,
        ]);

        Livewire::actingAs($user)
            ->test(DeleteIdea::class, [
                'idea' => $idea,
            ])
            ->call('deleteIdea')
            ->assertRedirect(route('idea.index'));

        $this->assertEquals(0, Comment::count());
        $this->assertEquals(0, Idea::count());
    }
}
