<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Idea;
use App\Models\Status;
use App\Models\Vote;
use App\Traits\WithAuthRedirects;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IdeasIndex extends Component
{
    use WithPagination, WithAuthRedirects;

    #[Url]
    public $status = '';

    #[Url(as: 'Ideas-Category')]
    public $category = '';

    #[Url(as: 'Filtering_results')]
    public $filter = '';

    #[Url(as: 'Searching_for', history: true)]
    public string $search = '';


    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        if ($this->filter === 'My Ideas') {
            if (auth()->guest()) {
                return $this->redirectToLogin();
            }
        }
    }


    #[On('queryStringUpdatedStatus')]
    public function queryStringUpdatedStatus($newStatus)
    {
        $this->resetPage();
        $this->status = $newStatus;
    }


    public function render()
    {
        $statuses = Status::all()->pluck('id', 'name');
        $categories = Category::select('id', 'name')->get();

        return view('livewire.ideas-index', [
            'ideas' => Idea::query()
                ->with('user:id,name,email', 'category:id,name', 'status:id,name')
                ->withCount('votes', 'comments')
                ->addSelect(['voted_by_user' => Vote::select('id')
                    ->where('user_id', auth()->id())
                    ->whereColumn('idea_id', 'ideas.id')
                ])
                ->when($this->status && $this->status !== 'All', function (Builder $query) use ($statuses) {
                    return $query->where('status_id', $statuses->get($this->status));
                })
                ->when($this->category && $this->category !== 'All Categories', function (Builder $query) use ($categories) {
                    return $query->where('category_id', $categories->pluck('id', 'name')->get($this->category));
                })
                ->when($this->filter && $this->filter === 'Top Voted', function (Builder $query) {
                    return $query->orderByDesc('votes_count');
                })
                ->when($this->filter && $this->filter === 'My Ideas', function (Builder $query) {
                    return $query->where('user_id', auth()->id());
                })
                ->when(strlen($this->search) >= 3, function (Builder $query) {
                    return $query->where('title', 'like', '%'. $this->search. '%');
                })
                ->when($this->filter && $this->filter === 'Spam Ideas', function (Builder $query) {
                    return $query->where('spam_reports', '>', 0)
                        ->orderByDesc('spam_reports');
                })
                ->when($this->filter && $this->filter === 'Spam Comments', function (Builder $query) {
                    return $query->whereHas('comments', function (Builder $builder) {
                        $builder->where('spam_reports', '>', 0);
                    })
                        ->orderByDesc(function ($subquery) {
                            $subquery->selectRaw('SUM(spam_reports)')
                                ->from('comments')
                                ->whereColumn('comments.idea_id', 'ideas.id');
                        });
                })
                ->orderByDesc('id')
                ->simplePaginate(),
            'filters' => ['No Filter', 'Top Voted', 'My Ideas',],
            'adminFilters' => ['Spam Ideas', 'Spam Comments'],
            'categories' => $categories,
        ]);
    }
}
