<?php

namespace Nixler\Wikidata;

use GuzzleHttp\Client as GuzzleClient;

class SPARQL

{

 	private $select;

 	private $where;


    /**
     * @param array $query
     *
     * @return string
     */ 

    public function select(){

    	$this->select[] = implode(",", func_get_args());

    	return $this;

    }

    /**
     * @param array $query
     *
     * @return string
     */ 

    public function where(){

    	$this->where[] = implode(" ", func_get_args());

    	return $this;

    }

    /**
     * @param array $query
     *
     * @return string
     */ 

    public function query(){

    	$select = implode(",", $this->select);
    	$where = implode(",", $this->where);

    	$params = [
    		'format' => 'json',
    		'query' => 'SELECT '.$select.'  WHERE { '.$where.'}'
    	];

    	$url = sprintf('https://query.wikidata.org/sparql?%s', http_build_query($params));
    	$client = new GuzzleClient();

    	return $data = json_decode(($client->request('GET', $url))->getBody(), true);

    }


}