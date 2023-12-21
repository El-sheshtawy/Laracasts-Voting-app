<div>
    @if ($this->comments->isNotEmpty())

        <div class="comments-container relative space-y-6 md:ml-22 pt-4 my-8 mt-1">

            @foreach ($this->comments as $comment)
                <livewire:idea-comment :key="$comment->id" :$comment />
            @endforeach

        </div>

        <div class="my-8 md:ml-22">
            {{ $this->comments->onEachSide(1)->withQueryString()->links() }}
        </div>
    @else
        <div class="mx-auto w-70 mt-12">
            <img src="{{ asset('img/no-ideas.svg') }}" alt="No Ideas" class="mx-auto mix-blend-luminosity">
            <div class="text-gray-400 text-center font-bold mt-6">No comments yet...</div>
        </div>
    @endif
</div>
