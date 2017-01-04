<?php

namespace Nixler\Wikidata;

use GuzzleHttp\Client as GuzzleClient;
use Nixler\Wikidata\SPARQL;
use Nixler\Wikidata\Wikipedia;

class Wikidata

{

    private $entities;

    private $select;

    private $locales;

    private $ids;

    private $search;

    /**
     * Build URL for API call
     *
     * @param array $query
     *
     * @return string
     */ 

    public function apiUrl($query){

        $props = ['labels', 'descriptions', 'aliases', 'claims', 'sitelinks'];

        if(!count($this->locales)){
            $this->languages(array_get($query, 'locales'));
        }

        if(!count($this->ids)){
            $this->whereID(array_get($query, 'ids', []));
        }

        $params = [
            'format' => 'json',
        ];

        if(count($this->ids)){
            $params['action'] = 'wbgetentities';
            $params['props'] = implode("|", array_get($query, 'props', $props));
            $params['languages'] = implode("|", $this->locales);
            $params['ids'] = implode("|", $this->ids);
        } elseif (count($this->searchQuery)){
            $params['action'] = 'wbsearchentities';
            $params['search'] = $this->searchQuery;
            $params['language'] = head($this->locales);
        }

        return sprintf('https://www.wikidata.org/w/api.php?%s', http_build_query($params));

    }



    /**
     * Make API call
     *
     * @param array $query
     *
     * @return string
     */ 

    public function api($query){

        $url = $this->apiUrl($query);

        $client = new GuzzleClient();

        $data = json_decode(($client->request('GET', $url))->getBody(), true);

        if(count($this->ids)){
            $this->entities = array_get($data, 'entities', []);
        } elseif (count($this->searchQuery)){
            $this->entities = array_get($data, 'search', []);
        }

        return $this;

    }



    /**
     * @param array $query
     *
     * @return string
     */ 

    public function withPhotos(){

        $output = [];

        foreach ($this->entities as $key => $entity) {

            $photos = [];

            foreach ($entity['claims'] as $prop => $claim) {
                
                foreach ($claim as $subclaim) {

                    if(isset($subclaim['mainsnak']) 
                        && isset($subclaim['mainsnak']['datatype'])
                        && $subclaim['mainsnak']['datatype'] == 'commonsMedia'){

                        $datavalue = array_get($subclaim['mainsnak'], 'datavalue');
                        $value = array_get($datavalue, 'value');

                        $photos[] = [
                            'path' => $value,
                            'url' => (new Wikipedia)->image($value),
                            'prop' => $prop
                        ];
                    }

                }
            }

            $entity['photos'] = $photos;

            $output[] = $entity;

        }
        
        $this->entities = $output;

        return $this;

    }





    /**
     * Get the entities
     *
     * @param array $query
     *
     * @return string
     */ 

    public function languages(){

        $this->locales = array_filter(func_get_args());

        if(!count($this->locales)){
            $this->locales = config('wikidata.locales');
        }

        return $this;
    }



    /**
     * @param array $query
     *
     * @return string
     */ 

    public function whereID($id){

        if(is_array($id)){
            $this->ids = $id;
        } else {
            $this->ids = [$id];
        }

        return $this;
    }



    /**
     * @param array $query
     *
     * @return string
     */ 

    public function search($query){

        $this->searchQuery = $query;

        $this->api([]);

        return $this;
    }



    /**
     * Get the entities
     *
     * @param array $query
     *
     * @return string
     */ 

    public function select(){

        $args = func_get_args();
        $params = [];

        foreach ($args as $key => $value) {
            $params[] = str_singular($value);
            $params[] = str_plural($value);
        }   

        $this->select = $params;

        return $this;
    }




    /**
     * Get the entities
     *
     * @param array $query
     *
     * @return string
     */ 

    public function raw(){

        return collect($this->entities);

    }




    /**
     * Get the entities
     *
     * @param array $query
     *
     * @return string
     */ 

    public function where($prop, $val){

        $data = (new SPARQL)->select('?item')->where('?item', 'wdt:'.$prop, '"'.$val.'"')->query();

        $results = array_get($data, 'results', []);
        $bindings = array_get($results, 'bindings', []);

        foreach ($bindings as $item) {
            $item = array_get($item, 'item', []);
            $url = array_get($item, 'value');
            if($url){

                $id = basename($url);

                $this->whereID($id);

                return $this;

            }
        }

        return $this;

    }




    /**
     * Get the entities
     *
     * @param array $query
     *
     * @return string
     */ 

    public function get(){

        $output = [];

        if(!$this->entities){
            $this->api([]);
        }

        if($this->select && in_array('photos', $this->select)){
            $this->withPhotos();
        }

        if($this->select && in_array('wiki', $this->select)){
            $this->withWikis();
        }

        foreach ($this->entities as $key => $item) {

            if($this->select){
                $item = collect($item)->only($this->select);
            }

            $entity = [];

            if(isset($item['id'])){
                $entity['id'] = array_get($item, 'id');
            }

            if(isset($item['type'])){
                $entity['type'] = array_get($item, 'type');
            }

            if(isset($item['labels']) || isset($item['label'])){
                $entity['label'] = isset($item['label']) ? $item['label'] : $this->localeMatch($item['labels']);
            }

            if(isset($item['descriptions']) || isset($item['description'])){
                $entity['description'] = isset($item['description']) ? $item['description'] : $this->localeMatch($item['descriptions']);
            }

            if(isset($item['wiki']) || isset($item['wikis'])){
                $entity['wiki'] = $this->parseWikis($item['wikis']);
            }

            if(isset($item['aliases'])){
                $entity['aliases'] = $this->localeMatch($item['aliases']);
            }

            if(isset($item['sitelinks'])){
                $entity['sitelinks'] = $this->groupLinks($item['sitelinks']);
            }

            if(isset($item['photos'])){
                $entity['photos'] = $item['photos'];
            }

            if(isset($item['claims'])){
                $entity['claims'] = $this->transformClaims($item['claims']);
            }

            if(count($entity)){
                $output[] = $entity;
            }

        }

        return collect($output);

    }




    /**
     * Get the first entity
     *
     * @param array $query
     *
     * @return string
     */ 

    public function first(){

        return $this->get()->first();

    }




    /**
     * Find entity by ID
     *
     * @param array $query
     *
     * @return string
     */ 

    public function find($id){

        return $this->api([
            'ids' => [$id]
        ])->get()->first();

    }


    /**
     * Parse Title, text and image from wikipedia
     *
     * @param string $query['id']
     *
     * @return string
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
     * @param string $query['id']
     *
     * @return string
     */ 

    private function groupLinks($input){

       $sites = ['wikinews', 'wiki', 'wikiquote'];

       $output = [];

       foreach ($input as $key => $value) {
           
           foreach ($sites as $site) {
               
               if(ends_with($value['site'], $site)){

                    $locale = str_replace($site, '', $value['site']);

                    $output[$site][$locale] = $value['title'];

               }

           }

       }

       return $output;

    }




    /**
     * Group site links
     *
     * @param string $query['id']
     *
     * @return string
     */ 

    public function withWikis(){

        $output = [];

        foreach ($this->entities as $key => $entity) {

            $wikiLinks = array_get($this->groupLinks($entity['sitelinks']), 'wiki', []);
            $wikis = [];

            foreach ($wikiLinks as $key => $value) {
                if(in_array($key, $this->locales)){
                    $wikis[] = (new Wikipedia)->api($key, $value);
                }
            }

            $entity['wikis'] = $wikis;

            $output[] = $entity;

        }
        
        $this->entities = $output;

        return $this;

    }




    /**
     * Group site links
     *
     * @param string $query['id']
     *
     * @return string
     */ 

    public function parseWikis($wikis){

        $output = [];

        foreach ($wikis as $wiki) {

            $output[$wiki['locale']] = $wiki['text'];

        }
        
        if(count($this->locales) > 1){
            return $output;
        } else {
            return array_first($output);
        }

    }




    /**
     * @param string $query['id']
     *
     * @return string
     */ 

    private function transformClaims($input){

       $output = [];

       foreach ($input as $prop => $claims) {
            
            $claimOutput = [
                'prop' => $prop,
                'items' => []
            ];

            foreach ($claims as $claim) {
                
                if(isset($claim['mainsnak']) 
                    && isset($claim['mainsnak']['datatype'])
                    && isset($claim['mainsnak']['snaktype'])
                    && isset($claim['mainsnak']['datavalue'])){

                    $value = null;
                    $type = $claim['mainsnak']['datatype'];
                    $datavalue = $claim['mainsnak']['datavalue']['value'];

                    if(in_array($type, ['string', 'url', 'globe-coordinate', 'external-id'])){
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
                        'type' => array_get($claim, 'type'),
                        'type' => $type,
                        'value' => $value
                    ];

                    $claimOutput['items'][] = $item;

                }
                
            }

            $output[] = $claimOutput;

       }

       return $output;

    }

}