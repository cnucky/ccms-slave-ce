<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use YunInternet\CCMSCommon\Model\CompositeKey;

class PublicImage extends Model
{
    use CompositeKey;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ["name", "version"];

    protected $guarded = [];
}
