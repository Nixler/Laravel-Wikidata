<?php

namespace Nixler\Wikidata\Models;

use Illuminate\Database\Eloquent\Model;

class EntitySimilar extends Model
{
    public $timestamps = false;
    protected $fillable = ['entity_id', 'similar_type', 'similar_id', 'rank'];
}