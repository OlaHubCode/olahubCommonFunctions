<?php

namespace OlaHub\Providers;

use Illuminate\Support\ServiceProvider;
use OlaHub\Models\OlaHubCommonModels;

class AppServiceProvider extends ServiceProvider
{
    
    public function boot(){
        OlaHubCommonModels::observe("OlaHub\\Observers\\OlaHubCommonObserve");
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
