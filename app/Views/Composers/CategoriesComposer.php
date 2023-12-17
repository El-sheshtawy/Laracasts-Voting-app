<?php

namespace App\Views\Composers;

use App\Models\Category;
use Illuminate\View\View;

class CategoriesComposer
{
    public function compose(View $view): void
    {
//        $view->with('categories', Category::select('id', 'name')->get());
    }
}
