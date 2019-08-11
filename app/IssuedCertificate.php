<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IssuedCertificate extends Model
{
    const STATUS_NORMAL = 0;

    const STATUS_REVOKED = 1;

    protected $fillable = [
        "name",
        "description",
        "certificate",
        "serial_number",
        "status",
        "revoke_time",
    ];
}
