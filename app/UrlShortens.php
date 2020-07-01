<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UrlShortens extends Model
{
    protected $fillable = ['url', 'short_code', 'hits', 'expiration_date', 'is_deleted'];
}
