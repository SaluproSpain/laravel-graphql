<?php

namespace Salupro\GraphQL\Providers;

use Illuminate\Support\ServiceProvider;
use Salupro\GraphQL\Controllers\GraphQLController;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        \Route::post('api/graphql', GraphQLController::class.'@execute')->middleware(['auth:api']);
    }

}
