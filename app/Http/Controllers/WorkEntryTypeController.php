<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkEntryType;
use App\Models\WorkEntry;

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
            'employee_id' => 'required',
            'date' => 'required|date',
            'time_from' => 'required',
            'time_to' => 'required',
            'work_entry_type_id' => 'required',
        ]);

        WorkEntry::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
            'work_entry_type_id' => $request->work_entry_type_id,
        ]);

        return response()->json(['success' => true]);
    }
}