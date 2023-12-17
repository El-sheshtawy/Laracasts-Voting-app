<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Idea;
use Cocur\Slugify\Slugify;
use Cviebrock\EloquentSluggable\SluggableObserver;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EditIdea extends Component
{
    public $idea;
    #[Rule('required|min:5')]
    public $title = '';

    #[Rule('required|integer|exists:categories,id')]
    public $category;

    #[Rule('required|min:4')]
    public $description = '';

    public function mount(Idea $idea)
    {
        $this->idea = $idea;
        $this->title = $this->idea->title;
        $this->category = $this->idea->category_id;
        $this->description = $this->idea->description;
    }

    public function updateIdea()
    {
        abort_if(auth()->guest() || auth()->user()->cannot('update', $this->idea), Response::HTTP_FORBIDDEN);

        $this->validate();
        $this->idea->update([
            'title' => $this->title,
            'category_id' => $this->category,
            'description' => $this->description,
        ]);
        session()->flash('success_message', 'Idea was added successfully!');

        return to_route('idea.show', $this->idea);

        /*
        $this->dispatch('ideaWasUpdated', 'Idea was updated successfully!');   Not used
        This refresh because I also want to update the url of show idea.
        */
    }

    public function render()
    {
        return view('livewire.edit-idea' , [
            'categories' => Category::select('id', 'name')->get(),
        ]);
    }
}
