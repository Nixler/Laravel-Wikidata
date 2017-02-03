<?php

namespace Nixler\Wikidata\Models;

use Illuminate\Database\Eloquent\Model;

class EntityAccount extends Model
{
    public $timestamps = false;
    protected $fillable = ['entity_id', 'provider', 'account_id'];
}