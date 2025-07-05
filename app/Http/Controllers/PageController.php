<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Page;

class PageController extends Controller
{
    public function info()
    {
        $page = Page::first();
        return view('info', ['page' => $page]);
    }
}
