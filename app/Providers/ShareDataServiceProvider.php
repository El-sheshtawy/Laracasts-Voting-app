<?php

namespace App\Providers;

use App\Views\Composers\CategoriesComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades;
use Illuminate\View\View;
class ShareDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
//        Facades\View::composer(['layouts.app'], CategoriesComposer::class);
    }
}
