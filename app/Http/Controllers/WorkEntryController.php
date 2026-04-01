<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkEntry;

class WorkEntryController extends Controller
{
    public function store(Request $request)
{
    dd($request->all());
}
}
