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

Now open up `app/config/app.php` and add the service provider to your `providers` array.

```php
'providers' => array(
	'Nixler\Wikidata\YoutubeServiceProvider',
)
```

## Configuration
### For Laravel 5
Run `php artisan vendor:publish --provider="Nixler\Wikidata\YoutubeServiceProvider"` and set your API key in the file:

```
/app/config/wikidata.php
```


## Wikidata Data API
- [MediaWiki API help](https://www.wikidata.org/w/api.php)