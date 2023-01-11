<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Histories extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','date','name','symbol','open','high','low','close'
    ];
}