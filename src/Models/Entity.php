<?php

namespace Nixler\Wikidata\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\Mediable\Mediable;
use Dimsav\Translatable\Translatable;

class Entity extends Model
{
	use Mediable, Translatable;

	public $translatedAttributes = ['title', 'headline', 'wiki'];

    protected $fillable = ['type'];


    /**
     * The artists that similar to the entity.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function similars()
    {
        return $this->belongsToMany('App\Artist', 'relations', 'model', 'relative')->wherePivot('relation', 'entity:similars');
    }




    /**
     * The users that liked artist
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function likers()
    {
        return $this->belongsToMany('App\User', 'relations', 'model', 'relative')->wherePivot('relation', 'user:entities');
    }



    /**
     * Create the url attribute for artist
     * 
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('e/'.urlencode(str_replace( '+' , '_' , $this->attributes['name'])));
    }


}
