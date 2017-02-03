<?php

namespace Nixler\Wikidata\Models;

use Illuminate\Database\Eloquent\Model;

class EntityTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'headline', 'wiki'];
}