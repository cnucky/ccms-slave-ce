<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PingController extends Controller
{
    public function __invoke()
    {
        return ["result" => true, "type" => "pong", "message" => "pong"];
    }
}
