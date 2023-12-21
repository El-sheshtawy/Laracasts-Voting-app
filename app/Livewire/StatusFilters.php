<?php

namespace App\Livewire;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class StatusFilters extends Component
{
    public ?string $status;
    public $statusCount;

    public function mount()
    {
        $this->statusCount = Status::getCount();
        $this->status = request()->status ?? 'All';

        if (Route::currentRouteName() === 'idea.show') {
            $this->status = null;
        }
    }

    public function setStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->dispatch('queryStringUpdatedStatus', $this->status);

        if ($this->getPreviousRouteName() === 'idea.show') {
            return to_route('idea.index', [
                'status' => $this->status,
            ]);
        }
    }

    public function getPreviousRouteName()
    {
        return Route::getRoutes()->match(Request::create(url()->previous()))->getName();
        //    return app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();
    }
}
