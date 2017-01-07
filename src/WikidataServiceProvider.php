<?php

namespace Nixler\Wikidata;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class WikidataServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes(array(__DIR__ . '/config/wikidata.php' => config_path('wikidata.php')));
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