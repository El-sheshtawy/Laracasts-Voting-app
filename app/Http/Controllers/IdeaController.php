<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class IdeaController extends Controller
{
    public function index()
    {
        return view('idea.index');
    }

    public function show(Idea $idea)
    {
        $idea->loadCount('comments', 'votes');

        $commentsCount = $idea->comments_count;
        $votesCount = $idea->votes_count;

        $backUrl = Route::getRoutes()->match(Request::create(url()->previous()))->getName() !== ('idea.index')
            ? route('idea.index')
            : url()->previous();

     //   $backUrl =  url()->previous() !== url()->full() && url()->previous() !== route('login');

        return view('idea.show', compact('idea', 'commentsCount', 'votesCount', 'backUrl'));
    }
}
