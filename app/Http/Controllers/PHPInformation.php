<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PHPInformation extends Controller
{
    public function __invoke()
    {
        phpinfo();
    }
}
