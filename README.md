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