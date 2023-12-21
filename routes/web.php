<?php

use App\Http\Controllers\IdeaController;
use Illuminate\Support\Facades\Route;


require __DIR__.'/auth.php';

Route::get('/', [IdeaController::class, 'index'])->name('idea.index');

Route::get('/ideas/{idea:slug}', [IdeaController::class, 'show'])->name('idea.show');




