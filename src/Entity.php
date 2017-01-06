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

    private $visible = [];

    private $locales = [];


    public function __construct(array $data = [], array $visible = ['*'], array $locales = [])
    {

        $this->makeVisible($visible);

        $this->withLocales($locales);

        $this->fill($data);

        return $this;

    }



    /**
     * Fill model with data
     *
     * @param array $data
     *
     * @return void
     */ 

    public function fill($data){

        $this->id = array_get($data, 'id');

        $this->type = array_get($data, 'type');

        $this->label = $this->localeMatch(array_get($data, 'labels', []));

        $this->alias = $this->localeMatch(array_get($data, 'aliases', []));

        $this->description = $this->localeMatch(array_get($data, 'descriptions', []));

        $this->sitelinks = $this->parseLinks(array_get($data, 'sitelinks', []));

        $this->wikiOriginal = $this->parseWikis($this->sitelinks);

        $this->wiki = $this->transformWikis();

        $this->claims = $this->transformClaims(array_get($data, 'claims', []));

        $this->photos = $this->parsePhotos();

    }




    /**
     * Match text with its locale and return key value array
     *
     * @param array $input
     *
     * @return array
     */ 

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
     * @return array
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
     * Parse wikipedia articles
     *
     * @return array
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
     * Make localeMatching but for wikipedia
     *
     * @return array|mixed
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
     * Clean and mutate claims
     *
     * @param array $input
     *
     * @return array
     */ 

    private function transformClaims($input){

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

            $output[$prop] = $items;

       }

       return $output;

    }


    /**
     * Return collection of photos parsed from claims and wikipedia articles
     *
     * @return collections
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
     * Add photo to collection
     *
     * @param string $url - Path or full url of image
     * @param string $source - Source of image
     * @param boolean $isPath - Define its path or not
     *
     * @return void
     */ 

    private function pushPhoto($url, $source, $isPath = true){

        if($isPath){
            $url = (new Wikipedia)->image($url);
        }

        if(!$this->photos->contains('url', $url)){

            $this->photos->push([
                'url' => $url,
                'source' => $source
            ]);

        }

    }


    /**
     * Take photos from claims
     *
     * @return void
     */ 

    private function parsePhotosFromClaims(){

        $claims = collect($this->claims)->flatten(1);

        foreach ($claims as $claim) {

            if($claim['type'] != 'commonsMedia'){ continue; }

            $this->pushPhoto($claim['value'], $claim['prop']);

        }

    }


    /**
     * Parse photos from wikipedia articles
     *
     * @return void
     */ 

    private function parsePhotosFromWikis(){

        foreach ($this->wikiOriginal as $wiki) {

            $image = array_get($wiki, 'image');

            $source = array_get($wiki, 'source');

            if(!$image || !$source){ continue; }

            $this->pushPhoto($image, $source, false);

        }

    }


    /**
     * Make visible array of attributes
     *
     * @param array $data
     *
     * @return self
     */ 

    public function makeVisible($data){

        $this->visible = $data;

        return $this;

    }


    /**
     * Set locales to parse
     *
     * @param array $data
     *
     * @return self
     */ 

    public function withLocales($data){

        $this->locales = $data;

        return $this;

    }


    /**
     * Return entity
     *
     * @return array
     */ 

    public function get(){

        $output = [];

        foreach ($this->visible as $item) {
            if(isset($this->{$item})){
                $output[$item] = $this->{$item};
            }
        }

        return $output;

    }


}