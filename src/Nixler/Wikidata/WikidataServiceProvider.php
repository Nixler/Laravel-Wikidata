<?php

namespace Nixler\Wikidata;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class WikidataServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Wikidata', 'Nixler\Wikidata\Facades\Wikidata');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes(array(__DIR__ . '/../../config/wikidata.php' => config_path('wikidata.php')));

        //Laravel 5.1+ fix
        if(floatval(Application::VERSION) >= 5.1){
            $this->app->bind("wikidata", function(){
                return $this->app->make('Nixler\Wikidata\Wikidata', [config('wikidata.KEY')]);
            });
        }else{
            $this->app->bindShared('wikidata', function () {
                return $this->app->make('Nixler\Wikidata\Wikidata', [config('wikidata.KEY')]);
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('wikidata');
    }

}