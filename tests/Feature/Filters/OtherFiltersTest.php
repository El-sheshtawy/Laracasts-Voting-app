<?php

namespace Filters;

use App\Livewire\IdeasIndex;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OtherFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_top_voted_filter_works()
    {
        $user = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        $ideaOne = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        $ideaTwo = Idea::factory()->create([
            'user_id' => $user->id,
        ]);

        Vote::factory()->create([
            'idea_id' => $ideaOne->id,
            'user_id' => $user->id,
        ]);

        Vote::factory()->create([
            'idea_id' => $ideaOne->id,
            'user_id' => $userB->id,
        ]);

        Vote::factory()->create([
            'idea_id' => $ideaTwo->id,
            'user_id' => $userC->id,
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('filter', 'Top Voted')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->votes()->count() === 2
                    && $ideas->get(1)->votes()->count() === 1;
            });
    }

    /** @test */
    public function test_my_ideas_filter_works_correctly_when_user_logged_in()
    {
        $user = User::factory()->create();
        $userB = User::factory()->create();

         Idea::factory()->create([
            'user_id' => $user->id,
             'title' => 'My First Idea',
        ]);

         Idea::factory()->create([
            'user_id' => $user->id,
             'title' => 'My Second Idea',
        ]);

         Idea::factory()->create([
            'user_id' => $userB->id,
         ]);

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'My Ideas')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->title === 'My Second Idea'
                    && $ideas->get(1)->title === 'My First Idea';
            });
    }

    /** @test */
    public function test_my_ideas_filter_works_correctly_when_user_is_not_logged_in()
    {
        $user = User::factory()->create();
        $userB = User::factory()->create();


        Idea::factory()->create([
            'user_id' => $user->id,
        ]);

         Idea::factory()->create([
            'user_id' => $user->id,
        ]);

         Idea::factory()->create([
            'user_id' => $userB->id,
        ]);

        Livewire::test(IdeasIndex::class)
            ->set('filter', 'My Ideas')
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function test_my_ideas_filter_works_correctly_with_categories_filter()
    {
        $user = User::factory()->create();

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);


        Idea::factory()->create([
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'title' => 'My First Idea',
        ]);

        Idea::factory()->create([
            'user_id' => $user->id,
            'category_id' => $categoryOne->id,
            'title' => 'My Second Idea'
        ]);

        Idea::factory()->create([
            'user_id' => $user->id,
            'category_id' => $categoryTwo->id,
        ]);

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->set('filter', 'My Ideas')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->title === 'My Second Idea'
                    && $ideas->get(1)->title === 'My First Idea';
            });
    }

    /** @test */
    public function test_no_filters_works_correctly()
    {
         Idea::factory()->create();

         Idea::factory()->create([
             'title' => 'My Second Idea',
         ]);

         Idea::factory()->create([
             'title' => 'My Third Idea',
         ]);

        Livewire::test(IdeasIndex::class)
            ->set('filter', 'No Filter')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->title === 'My Third Idea'
                    && $ideas->get(1)->title === 'My Second Idea';
            });
    }

    public function test_spam_ideas_filter_works()
    {
        $user = User::factory()->admin()->create();

        $ideaOne = Idea::factory()->create([
            'title' => 'Idea One',
            'spam_reports' => 1
        ]);

        $ideaTwo = Idea::factory()->create([
            'title' => 'Idea Two',
            'spam_reports' => 2
        ]);

        $ideaThree = Idea::factory()->create([
            'title' => 'Idea Three',
            'spam_reports' => 3
        ]);

        $ideaFour = Idea::factory()->create();

        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'Spam Ideas')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->title === 'Idea Three'
                    && $ideas->get(1)->title === 'Idea Two'
                    && $ideas->get(2)->title === 'Idea One';
            });
    }

    public function test_spam_filter_works()
    {
        $user = User::factory()->admin()->create();

        $idea1 = Idea::factory()->create([
            'title' => 'First Idea'
        ]);
        $idea2 = Idea::factory()->create([
            'title' => 'Second Idea'
        ]);
        $idea3 = Idea::factory()->create([
            'title' => 'Third Idea'
        ]);

        Idea::factory()->create();
        Idea::factory()->create();
        Idea::factory()->create();
        Idea::factory()->create();


        Comment::factory()->create([
            'spam_reports' => 55,
            'idea_id' => $idea1->id,
        ]);

        Comment::factory()->create([
            'spam_reports' => 65,
            'idea_id' => $idea2->id,
        ]);

        Comment::factory()->create([
            'spam_reports' => 23,
            'idea_id' => $idea3->id,
        ]);


        Livewire::actingAs($user)
            ->test(IdeasIndex::class)
            ->set('filter', 'Spam Comments')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->title ==='Second Idea'
                    && $ideas->get(1)->title === 'First Idea'
                    && $ideas->get(2)->title === 'Third Idea';
            });
    }
}
