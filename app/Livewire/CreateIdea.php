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
    public $category;

    #[Rule('required|min:4')]
    public $description = '';


    public function createIdea()
    {
        abort_if(!auth()->check(), Response::HTTP_FORBIDDEN);

        $this->validate();
        Idea::create([
            'user_id' => auth()->id(),
            'category_id' => $this->category,
            'status_id' => 1,
            'title' => $this->title,
            'description' => $this->description,
        ]);
        $this->reset();

        session()->flash('success_message', 'Idea was added successfully!');
        return redirect()->route('idea.index');
    }

    public function render()
    {
        return view('livewire.create-idea', [
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }
}
