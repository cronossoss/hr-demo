<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkEntryType;

class WorkEntryTypeController extends Controller
{
    // LISTA
    public function index()
    {
        $types = WorkEntryType::orderBy('name')->get();

        return view('work_types.index', compact('types'));
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:work_entry_types,code',
            'input_type' => 'required|in:time,range',
        ]);

        WorkEntryType::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'input_type' => $request->input_type,
            'is_paid' => $request->has('is_paid'),
            'counts_as_work' => $request->has('counts_as_work'),
            'affects_vacation' => $request->has('affects_vacation'),
            'pay_multiplier' => $request->pay_multiplier ?? 1,
        ]);

        return back();
    }
}