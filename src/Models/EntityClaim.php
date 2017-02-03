<?php

namespace Nixler\Wikidata\Models;

use Illuminate\Database\Eloquent\Model;

class EntityClaim extends Model
{
    public $timestamps = false;
    protected $fillable = ['entity_id', 'prop_id', 'type', 'rank', 'value'];
}