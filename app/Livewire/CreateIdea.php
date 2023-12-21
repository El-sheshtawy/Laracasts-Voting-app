<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Idea;
use App\Traits\WithAuthRedirects;
use Illuminate\Http\Response;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CreateIdea extends Component
{
    use WithAuthRedirects;

    #[Rule('required|min:5')]
    public $title = '';

    #[Rule('required|integer|exists:categories,id')]
    public $category_id;

    #[Rule('required|min:4')]
    public $description = '';

    public function save()
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $this->validate();
        auth()->user()->ideas()->create($this->only('category_id', 'title', 'description'));

        return redirect()->route('idea.index')
            ->with('success_message', 'Idea was added successfully!');
    }

    public function render()
    {
        return view('livewire.create-idea', [
            'categories' => Category::query()->select('id', 'name')->get(),
        ]);
    }
}
