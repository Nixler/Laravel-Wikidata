<?php

namespace Nixler\Wikidata\Models;

use Illuminate\Database\Eloquent\Model;

class PropTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
}