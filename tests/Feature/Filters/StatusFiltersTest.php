<?php

namespace Filters;

use App\Livewire\StatusFilters;
use App\Models\Idea;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StatusFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_contains_status_filters_livewire_component()
    {
        Idea::factory()->create();

        $this->get(route('idea.index'))->assertSeeLivewire('status-filters');
    }

    public function test_show_page_contains_status_filters_livewire_component()
    {
        $idea = Idea::factory()->create();

        $this->get(route('idea.show', $idea))->assertSeeLivewire('status-filters');
    }

    public function test_show_correct_status_count()
    {
        $statusImplemented = Status::factory()->create([ 'id' => 4,'name' => 'Implemented']);

        Idea::factory()->create([
            'status_id' => $statusImplemented->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusImplemented->id,
        ]);

        Livewire::test(StatusFilters::class)
            ->assertSee('All Ideas (2)')
            ->assertSee('Implemented (2)');
    }

    public function filtering_works_when_query_string_in_place()
    {
        $statusOpen = Status::factory()->create(['name' => 'Open']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering']);
        $statusInProgress = Status::factory()->create(['name' => 'In Progress']);
        $statusImplemented = Status::factory()->create(['name' => 'Implemented']);
        $statusClosed = Status::factory()->create(['name' => 'Closed']);

        Idea::factory()->create([
            'status_id' => $statusConsidering->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusConsidering->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusInProgress->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusInProgress->id,
        ]);

        Idea::factory()->create([
            'status_id' => $statusInProgress->id,
        ]);

        Livewire::withQueryParams(['status' => 'In Progress'])
            ->test(IdeasIndex::class)
            ->assertViewHas('ideas', function ($ideas) {
                return $ideas->count() === 3
                    && $ideas->first()->status->name === 'In Progress';
            });
    }

}
