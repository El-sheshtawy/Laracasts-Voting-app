<?php

namespace Tests\Feature;

use App\Livewire\EditIdea;
use App\Livewire\IdeaShow;
use App\Models\Category;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Livewire\Livewire;
use Tests\TestCase;

class EditIdeaTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_idea_livewire_show_when_user_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
            ]);

       $this->actingAs(user: $user)
           ->get(route('idea.show', $idea))
           ->assertSeeLivewire('edit-idea');
    }

    public function test_edit_idea_livewire_not_show_when_user_not_authorized()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user1->id,
        ]);

        $this->actingAs(user: $user2)
            ->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('edit-idea');
    }

    public function test_edit_idea_livewire_not_show_when_user_is_guest()
    {

        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))
            ->assertDontSeeLivewire('edit-idea');
    }

    public function test_edit_idea_component_validation_form_works()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditIdea::class, [
                'idea' => $idea
            ])
            ->set('title', '')
            ->set('category_id', '')
            ->set('description', '')
            ->call('update')
            ->assertHasErrors(['title', 'category_id', 'description'])
            ->assertSee('The title field is required');
    }

    public function test_can_edit_idea_successfully_when_authorized()
    {
        $user = User::factory()->create();
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea = Idea::factory()->create([
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditIdea::class, [
                'idea' => $idea
            ])
            ->set('title', 'Allam Updated')
            ->set('category_id', $categoryTwo->id)
            ->set('description', 'this is the allam updated title')
            ->call('update')
            ->assertSet('title', 'Allam Updated')
            ->assertSet('category_id', $categoryTwo->id);

        $this->assertDatabaseHas('ideas', [
            'title' => 'Allam Updated',
            'category_id' =>  $categoryTwo->id,
            'description' => 'this is the allam updated title',
        ]);
    }

    public function test_can_show_edit_idea_sentence_at_idea_show_page_when_authorized()
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
            ->assertSee('Edit Idea');
    }

    public function test_cannot_show_edit_idea_sentence_at_idea_show_page_when_not_authorized()
    {
        $user = User::factory()->create();
        $idea = Idea::factory()->create();

        Livewire::test(IdeaShow::class, [
                'idea' => $idea,
                'votesCount' => 4,
            'commentsCount' => 5
            ])
            ->assertDontSee('Edit Idea');
    }

    public function test_cannot_edit_idea_because_another_user_created_the_idea()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea = Idea::factory()->create([
            'user_id' => $user1->id,
            'category_id' => $categoryOne->id,
        ]);

        Livewire::actingAs($user2)
            ->test(EditIdea::class, [
                'idea' => $idea
            ])
            ->set('title', 'Allam Updated')
            ->set('category_id', $categoryTwo->id)
            ->set('description', 'this is the allam updated title')
            ->call('update')
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseMissing('ideas', [
            'title' => 'Allam Updated',
            'category_id' =>  $categoryTwo->id,
            'description' => 'this is the allam updated title',
        ]);
    }

    public function test_cannot_edit_idea_because_idea_creation_time_pass_one_hour()
    {
        $user = User::factory()->create();

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $idea = Idea::factory()->create([
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'created_at' => now()->subHours(4),
        ]);

        Livewire::actingAs($user)
            ->test(EditIdea::class, [
                'idea' => $idea
            ])
            ->set('title', 'Allam Updated')
            ->set('category_id', $categoryTwo->id)
            ->set('description', 'this is the allam updated title')
            ->call('update')
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseMissing('ideas', [
            'title' => 'Allam Updated',
            'category_id' =>  $categoryTwo->id,
            'description' => 'this is the allam updated title',
        ]);
    }
}
