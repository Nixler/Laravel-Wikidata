Wikidata
=========

![Travis Wikidata Build](https://api.travis-ci.org/alaouy/Youtube.svg?branch=master)

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


## Wikidata Data API
- [MediaWiki API help](https://www.wikidata.org/w/api.php)