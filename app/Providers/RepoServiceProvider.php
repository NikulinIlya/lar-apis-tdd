<?php

namespace App\Providers;

use App\Utopia\Repositories\Eloquent\ProductRepo;
use App\Utopia\Repositories\Interfaces\ProductRepoInterface;
use Illuminate\Support\ServiceProvider;

class RepoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ProductRepoInterface::class, ProductRepo::class);
    }
}
