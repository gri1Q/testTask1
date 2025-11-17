<?php

namespace App\Http\Controllers\Api;

ini_set('memory_limit', '2048M');

use App\Http\Controllers\Controller;
use App\Models\Post;

class TestController extends Controller
{
    public function get()
    {
//        dd(
        return Post::orderByDesc('created_at')->cursorPaginate(50);

//        );
    }
}
