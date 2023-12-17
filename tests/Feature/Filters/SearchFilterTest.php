<?php


namespace Filters;

use App\Livewire\IdeasIndex;
use App\Models\Category;
use App\Models\Idea;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_search_working_when_more_than_3_charachters()
    {
        $user = User::factory()->create();

        $ideaOne = Idea::factory()->create([
            'title' => 'My First Idea',
        ]);

        Idea::factory()->create([
            'title' => 'My second Idea',
        ]);

        Idea::factory()->create([
            'title' => 'My second 2 Idea',
        ]);

        Vote::factory()->create([
            'idea_id' => $ideaOne->id,
            'user_id' => $user->id,
        ]);


        Livewire::test(IdeasIndex::class)
            ->set('search', 'second')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->votes()->count() === 0
                    && $ideas->get(1)->title = 'My second 2 Idea';
            });
    }

    public function test_does_not_perform_search_if_less_than_3_charachters()
    {
        $user = User::factory()->create();

        $ideaOne = Idea::factory()->create([
            'title' => 'My First Idea',
        ]);

        Idea::factory()->create([
            'title' => 'My second Idea',
        ]);

        Idea::factory()->create([
            'title' => 'My second 2 Idea',
        ]);

        Vote::factory()->create([
            'idea_id' => $ideaOne->id,
            'user_id' => $user->id,
        ]);


        Livewire::test(IdeasIndex::class)
            ->set('search', 'ab')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3;
            });
    }

    public function test_search_works_correctly_with_category_filters()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);


        Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'title' => 'My First Idea',
        ]);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'title' => 'My Second Idea',
        ]);

        Idea::factory()->create([
            'category_id' => $categoryTwo->id,
            'title' => 'My Third Idea',

        ]);

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->set('search', 'Idea')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2;
            });
    }
}
