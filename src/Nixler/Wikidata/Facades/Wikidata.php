<?php
namespace Nixler\Wikidata\Facades;
use Illuminate\Support\Facades\Facade;

class Wikidata extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'wikidata'; }
}