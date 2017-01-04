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

Also, add the Wikidata facade to the aliases array in your app configuration file:

```php
'Wikidata' => Nixler\Wikidata\WikidataServiceProvider::class,
```

After registering the Wikidata service provider, you should publish the Wikidata configuration using the  vendor:publish Artisan command. This command will publish the wikidata.php configuration file to your config directory:

```
php artisan vendor:publish --provider="Nixler\Wikidata\WikidataServiceProvider"
```


## Usage

```php
// Retrieving Entities By ID
$laravel = Wikidata::whereId('Q13634357')->first();
// or by array of IDs
$companies = Wikidata::whereId(['Q95', 'Q2283'])->get();

//Languages
$laravel = Wikidata::whereId('Q13634357')->languages('en', 'ru')->get();

//Select Clause
$laravel = Wikidata::select('id', 'label')->whereId('Q13634357')->first();
//available attributes id, label, description, wiki, type, aliases, sitelinks, claims, photos

//Search By Query
$search = Wikidata::search('Adele')->get();

//Search By Prop
$adele = Wikidata::where('P345', 'nm2233157')->first();

```


## Wikidata Data API
- [MediaWiki API help](https://www.wikidata.org/w/api.php)