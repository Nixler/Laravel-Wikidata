Wikidata
=========

![Travis Wikidata Build](https://api.travis-ci.org/nixler/Wikidata.svg?branch=master)

Laravel PHP Facade/Wrapper for the Wikidata Data API

## Installation

Add `nixler/wikidata` to your `composer.json`.
```
"nixler/wikidata": "dev-master"
```

Run `composer update` to pull down the latest version of the package.

Next, you should add the WikidataServiceProvider to the providers array of your config/app.php configuration file:

```php
Nixler\Wikidata\WikidataServiceProvider::class,
```

After registering the Wikidata service provider, you should publish the Wikidata configuration using the  vendor:publish Artisan command. This command will publish the wikidata.php configuration file to your config directory:

```
php artisan vendor:publish --provider="Nixler\Wikidata\WikidataServiceProvider"
```


## Usage

```php

use Nixler\Wikidata\Wikidata;
...

// Retrieving Entities By ID
$laravel = (new Wikidata)->whereId('Q13634357')->first();
// or by array of IDs
$companies = (new Wikidata)->whereId(['Q95', 'Q2283'])->get();

//Languages
$laravel = (new Wikidata)->whereId('Q13634357')->languages('en', 'ru')->get();

//Select Clause
$laravel = (new Wikidata)->select('id', 'label')->whereId('Q13634357')->first();
//available attributes id, label, description, wiki, type, aliases, sitelinks, claims, photos

//Search By Query
$search = (new Wikidata)->search('Adele')->get();

//Search By Prop
$adele = (new Wikidata)->where('P345', 'nm2233157')->first();

```


## Wikidata Data API
- [MediaWiki API help](https://www.wikidata.org/w/api.php)





#Map

for first there should be functionality to add just title, headline, type, wiki, photos


Write
Model has Enitable trait
We make opperation $model->entity()->fetch('wikidata', 'Q1'); 
or $model->fetchDataFrom('lastfm', 'Adele');
or $model->fetchDataFrom('gbooks', 'Some book');
or $model->fetchDataFrom('tmdb', 'ID');
or $model->fetchDataFrom('imdb', 'ID');
or $model->fetchDataFrom('youtube', 'Some song or video');
entity if not exists it creates new and associates current external IDs
Then system make update of entity, it fetches all relations and populates entity with data.

Read
$model->getData([
	'info' => 'id,type,title,headline,wiki,entities',
	'links' => 'facebook,twitter',
	'photos' => 20,
	'similars' => 5
]);

takes info (translated), links to profiles in social networks, similars, photos
in entities it takes structured data such as ['date_of_birth' => 'timestamp']

there should be write opperation file for each type of entity - person, music, book, album, country

Search should be performed this way - entities('country')->where('')