<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function edit()
    {
        $page = Page::first();
        return view('admin.page.edit', ['page' => $page]);
    }

    public function update(Request $request)
    {
        $page = Page::first();
        $page->update($request->only('title', 'content', 'global_tax_enabled', 'german_tax_enabled', 'church_tax_enabled', 'badges'));

        return redirect()->route('admin.page.edit');
    }
}
