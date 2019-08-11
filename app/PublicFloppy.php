<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicFloppy extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = "name";
}
