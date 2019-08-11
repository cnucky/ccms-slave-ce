<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicISO extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = "name";

    protected $table = "public_isos";
}
