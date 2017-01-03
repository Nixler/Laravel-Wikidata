<?php

namespace Nixler\Wikidata;

use GuzzleHttp\Client as GuzzleClient;
use Nixler\Wikidata\SPARQL;

class Wikipedia
{


	/**
     * Build URL for API call
     *
     * @param array $query
     *
     * @return string
     */ 

    public function apiUrl($locale, $title){

        $params = [
            'format' => 'json',
            'action' => 'query',
            'prop' => 'extracts|pageimages',
            'exintro' => '',
            'explaintext' => '',
            'titles' => $title,
        ];

        return sprintf('http://%s.wikipedia.org/w/api.php?%s', $locale, http_build_query($params));

    }




    /**
     * Make API call
     *
     * @param array $query
     *
     * @return string
     */ 

    public function api($locale, $title){

        $url = $this->buildURL($locale, $title);

        $client = new \GuzzleHttp\Client();

        $data = json_decode(($client->request('GET', $url))->getBody(), true);

        $request = array_first(array_get(array_get($data, 'query', []), 'pages', []));

        $title = array_get($request, 'title');
        $text = array_get($request, 'extract');
        $source = sprintf('http://%s.wikipedia.org/%s', $locale, $title);
        $image = $this->wikiImageParse(array_get($request, 'pageimage'));

        return compact('title', 'text', 'image', 'source', 'locale');

    }



    /**
     * Generate image url based on path
     *
     * @param string $path
     *
     * @return string
     */ 

    public function image($path) { 

        if(!$path) return null;

           $name = str_replace(" ","_",$path);
           $md5 = md5($name);
           $url = 'https://upload.wikimedia.org/wikipedia/commons/'.str_limit($md5,1,false).'/'.str_limit($md5,2,false).'/'.$name;
           $ext = pathinfo($url, PATHINFO_EXTENSION);
           if($ext == 'svg'){ 
               $url = str_replace('commons/', 'commons/thumb/', $url.'/1200px-'.$name.'.png');
           }

           return $url;
    }

}