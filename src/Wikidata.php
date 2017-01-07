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
     * Define language versions for each entity
     *
     * @return self
     */ 

    public function languages(){

        $locales = array_filter(func_get_args());

        if(count($locales) == 1 && is_array(array_first($locales))){
            $locales = array_first($locales);
        }

        $this->locales = $locales;

        if(!count($this->locales)){
            $this->locales = config('wikidata.locales');
        }

        return $this;
    }



    /**
     * Set entity IDs to parse
     *
     * @param string|array $id
     *
     * @return self
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
     * Set search query 
     *
     * @param array $query
     *
     * @return self
     */ 

    public function search($query){

        $this->searchQuery = $query;

        $this->api([]);

        return $this;
    }



    /**
     * Define which fields should be visible
     *
     * @return self
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
     * Search entities by prop
     *
     * @param string $prop
     * @param string $val
     *
     * @return self
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
     * Get entities by query
     *
     * @return collection
     */ 

    public function get(){

        $entities = [];

        if(!$this->entities){
            $this->api([]);
        }

        foreach ($this->entities as $key => $item) {

            $entity = new Entity($item, $this->select, $this->locales);
            $entities[] = $entity->get();

        }

        return collect($entities);

    }




    /**
     * Get the first entity
     *
     * @return array
     */ 

    public function first(){

        return $this->get()->first();

    }




    /**
     * Find entity by ID
     *
     * @param string $id
     *
     * @return array
     */ 

    public function find($id){

        return $this->api([
            'ids' => [$id]
        ])->get()->first();

    }

}