<?php

namespace Salupro\ERP\Providers;

use App\Http\Controllers\Api\GraphQLController;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::get('graphql', GraphQLController::class.'@execute')->middleware(['auth:api']);
    }

}
