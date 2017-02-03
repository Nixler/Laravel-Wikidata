<?php

namespace Nixler\Wikidata\Opperations;

use GuzzleHttp\Client as GuzzleClient;
use Nixler\Wikidata\Wikipedia;
use Nixler\Wikidata\Models\Entity;

class WriteOpperation 
{

	protected $entity = null;

	protected $child = null;

	protected $model = null;
	


    public function __construct($model, $prop, $propValue)
    {

    	$this->child = $model;

    	$this->model = new Entity;

    	$this->loadDataFromApi($prop, $propValue);

    	$this->createEntity();
    	
    	$this->attachMedia();
    	
    	$this->attachClaims();
    	
    	$this->attachSocialAccounts();

    	$this->findSimilarities();
    }


    /**
     * Load data from Wikidata API
     *
     * @param string $prop
     * @param string $value
     *
     * @return void
     */ 

    public function loadDataFromApi($prop, $value){

    	$wikidata = new Wikidata;

    	$select = [
    		'id', 'type', 'label', 'wiki', 'description', 'claims', 'aliases', 
    		'sitelinks', 'photos'
    	];

    	$languages = ['en', 'ru', 'ka', 'pl', 'de', 'es'];

    	$this->entity = $wikidata->select($select)->languages($languages)->where($prop, $value)->first();

    }


    /**
     * Create new entity record with translations
     *
     * @return string
     */ 

    public function createEntity(){

    }


    /**
     * Attach media objects to entity
     *
     * @return string
     */ 

    public function attachMedia(){

    }


    /**
     * Attach photo object to entity
     *
     * @param string $patch
     *
     * @return string
     */ 

    public function attachPhoto($patch){

    }


    /**
     * Attach claims to entity
     *
     * @return string
     */ 

    public function attachClaims(){

    }


    /**
     * Create prop record in database
     *
     * @return string
     */ 

    public function firstOrCreateProp(){

    }


    /**
     * Attach social accounts to entity
     *
     * @return string
     */ 

    public function attachSocialAccounts(){

    }


    /**
     * Fill child model with data
     *
     * @return string
     */ 

    public function fillChild(){

    }


    /**
     * Fetch lastfm api for entity if it has mbid
     *
     * @return string
     */ 

    public function fetchLastfmRelation(){

    }


    /**
     * Fetch Google Books api for entity if it has mbid
     *
     * @return string
     */ 

    public function fetchGoogleBooksRelation(){

    }


    /**
     * Fetch Youtube api for entity if it has mbid
     *
     * @return string
     */ 

    public function fetchYoutubeRelation(){

    }


    /**
     * Find similar entities and attach as relations
     *
     * @return string
     */ 

    public function findSimilarities(){

    }

}