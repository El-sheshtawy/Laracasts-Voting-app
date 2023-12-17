<?php

namespace Filters;

use App\Livewire\IdeasIndex;
use App\Models\Category;
use App\Models\Idea;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_selecting_a_category_filters_correctly()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

         Idea::factory()->create([
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create();

        Livewire::test(IdeasIndex::class)
            ->set('category', 'Category 1')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->category->name === 'Category 1';
            });
    }

    /** @test */
    public function test_the_category_query_string_filters_correctly()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
        ]);

         Idea::factory()->create();

        Livewire::withQueryParams(['Ideas-Category' => 'Category 1'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 2
                    && $ideas->first()->category->name === 'Category 1';
            });
    }

    /** @test */
    public function test_selecting_a_status_and_a_category_filters_correctly()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $statusOpen = Status::factory()->create(['name' => 'Open']);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id,
        ]);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusOpen->id,
        ]);

        Idea::factory()->create();

        Livewire::test(IdeasIndex::class)
            ->set('status', 'Open')
            ->set('category', 'Category 1')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                    && $ideas->first()->category->name === 'Category 1'
                    && $ideas->first()->status->name === 'Open';
            });
    }

    /** @test */
    public function test_the_category_query_string_filters_correctly_with_status_and_category()
    {
        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $statusOpen = Status::factory()->create(['name' => 'Open']);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id,
        ]);

        Idea::factory()->create([
            'category_id' => $categoryOne->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusOpen->id,
        ]);

        Idea::factory()->create();

        Livewire::withQueryParams(['status' => 'Open', 'Ideas-Category' => 'Category 1'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 1
                    && $ideas->first()->category->name === 'Category 1'
                    && $ideas->first()->status->name === 'Open';
            });
    }

    /** @test */
    public function test_selecting_all_categories_filters_correctly()
    {
        Idea::factory()->create();
        Idea::factory()->create();
        Idea::factory()->create();

        Livewire::test(IdeasIndex::class)
            ->set('category', 'All Categories')
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3;
            });
    }
}
