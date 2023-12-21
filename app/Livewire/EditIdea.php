<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Idea;
use Illuminate\Http\Response;
use Livewire\Attributes\Rule;
use Livewire\Component;

class EditIdea extends Component
{
    public Idea $idea;

    #[Rule('required|min:5')]
    public string $title = '';

    #[Rule('required|integer|exists:categories,id')]
    public int $category_id;

    #[Rule('required|min:4')]
    public string $description = '';

    public function mount(Idea $idea)
    {
        $this->idea = $idea;

        $this->fill(
            $idea->only('title', 'description', 'category_id'),
        );
    }

    public function update()
    {
        abort_if(auth()->guest() || auth()->user()->cannot('update', $this->idea), Response::HTTP_FORBIDDEN);

        $this->validate();
        $this->idea->update(
            $this->only('title', 'category_id', 'description')
        );

        return to_route('idea.show', $this->idea)
            ->with('success_message', 'This idea was updated successfully!');
    }

    public function render()
    {
        return view('livewire.edit-idea' , [
            'categories' => Category::query()->select('id', 'name')->get(),
        ]);
    }
}
