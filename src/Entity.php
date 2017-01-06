<?php

namespace Nixler\Wikidata;

class Entity
{

    private $id = null;

    private $type = null;

    private $label = null;

    private $alias = null;

    private $description = null;

    private $sitelinks = null;

    private $wikiOriginal = null;

    private $wiki = null;

    private $claims = null;

    private $photos = null;

    private $visible = null;

    private $locales = [];


    public function __construct(array $data = [], array $visible = ['*'], array $locales = [], )
    {
    	$instance = new static;

        $instance->fill($data);

        $instance->makeVisible($visible);

    	$instance->withLocales($locales);

    }


    public function fill($data){

    	$this->id = array_get($data, 'id');

    	$this->type = array_get($data, 'type');

    	$this->label = $this->localeMatch(array_get($data, 'label', []));

    	$this->alias = $this->localeMatch(array_get($data, 'alias', []));

    	$this->description = $this->localeMatch(array_get($data, 'description', []));

    	$this->sitelinks = $this->parseLinks(array_get($data, 'sitelinks', []));

    	$this->wikiOriginal = $this->parseWikis($this->sitelinks);

    	$this->wiki = $this->transformWikis();

    	$this->claims = $this->transformClaims(array_get($data, 'claims', []));

    	$this->photos = $this->parsePhotos();

    }


    private function localeMatch($input){

        if(empty($input)) return [];

        foreach ($input as $key => $item){

            if(is_array(head($item))){

                $sub = [];

                foreach ($item as $subitem) {
                    $sub[] = array_get($subitem, 'value');
                }

                if(count($this->locales) > 1){
                    $output[$key] = $sub;
                } else {
                    $output = $sub;
                }

            } else {

                if(count($this->locales) > 1){
                    $output[$key] = $item['value'];
                } else {
                    $output = $item['value'];
                }
                

            }
            
        }

        return $output;
    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function parseLinks($links = array()){

       	$sites = ['wikinews', 'wiki', 'wikiquote'];

       	$parsed = [];

	    foreach ($links as $key => $value) {
	           
	        foreach ($sites as $site) {
	               
	            if(ends_with($value['site'], $site)){

	                 $locale = str_replace($site, '', $value['site']);

	                 $parsed[$site][$locale] = $value['title'];

	            }

	        }

	    }

       	return $parsed;

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function parseWikis(){

    	$wikiLinks = array_get($this->sitelinks, 'wiki');

    	$wikis = [];

    	if(!$this->sitelinks || !in_array('wiki', $this->visible) || !$wikiLinks){
    		return $wikis;
    	}

        foreach ($wikiLinks as $key => $value) {
            if(in_array($key, $this->locales)){
                $wikis[] = (new Wikipedia)->api($key, $value);
            }
        }

        return $wikis;

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function transformWikis(){

    	$output = [];

        foreach ($this->wikiOriginal as $wiki) {

            $output[$wiki['locale']] = $wiki['text'];

        }
        
        if(count($this->locales) > 1){
            return $output;
        } else {
            return array_first($output);
        }

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function transformClaims(){

        $output = [];

    	foreach ($input as $prop => $claims) {
            
            $items = [];

            foreach ($claims as $claim) {
                
                if(isset($claim['mainsnak']) 
                    && isset($claim['mainsnak']['datatype'])
                    && isset($claim['mainsnak']['snaktype'])
                    && isset($claim['mainsnak']['datavalue'])){

                    $value = null;
                    $type = $claim['mainsnak']['datatype'];
                    $datavalue = $claim['mainsnak']['datavalue']['value'];

                    if(in_array($type, ['string', 'url', 'globe-coordinate', 'external-id', 'commonsMedia'])){
                        $value = $datavalue;
                    } elseif($type == 'wikibase-item'){
                        $value = $datavalue['numeric-id'];
                    } elseif($type == 'time'){
                        $value = $datavalue['time'];
                    } elseif($type == 'monolingualtext'){
                        $value = $datavalue['text'];
                    } elseif($type == 'amount'){
                        $value = $datavalue['amount'];
                    }

                    $item = [
                        'id' => array_get($claim, 'id'),
                        'rank' => array_get($claim, 'rank'),
                        'stype' => array_get($claim, 'type'),
                        'type' => $type,
                        'prop' => $prop,
                        'value' => $value
                    ];

                    $items[] = $item;

                }
                
            }

            $output[$prop] = $claimOutput;

       }

       return $output;

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function parsePhotos(){
 
        if(is_null($this->photos)){
            $this->photos = collect([]);
        }

    	if(in_array('photos', $this->visible)){
            $this->parsePhotosFromClaims();
            $this->parsePhotosFromWikis();
    	}

    	return $this->photos;

    }	


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function pushPhoto($path, $source){

        $url = (new Wikipedia)->image($path);

        if(!$this->photos->contains('url', $url)){

            $this->photos->push([
                'url' => $url,
                'source' => $source
            ]);

        }

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function parsePhotosFromClaims(){

    	$claims = array_flatten($this->claims);

    	foreach ($claims as $claim) {

    		if($claim['type'] != 'commonsMedia'){ continue; }

    		$this->pushPhoto($claim['value'], $claim['prop']);

    	}

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    private function parsePhotosFromWikis(){

        foreach ($this->wikiOriginal as $wiki) {

            $image = array_get($wiki, 'image');

            $source = array_get($wiki, 'source');

            if(!$image || !$source){ continue; }

            $this->pushPhoto($image, $source);

        }

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    public function makeVisible($data){

        $this->visible = $data;

        return $this;

    }


    /**
     * Group site links
     *
     * @param array $links
     *
     * @return object
     */ 

    public function withLocales($data){

        $this->locales = $data;

        return $this;

    }

}