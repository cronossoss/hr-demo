<?php

namespace App\Http\Controllers;

use App\Models\WorkEntry;

class WorkController extends Controller
{
    // =====================================================
    // WORK PREGLED
    // =====================================================
    public function index($id)
    {
        $query = \App\Models\WorkEntry::where('employee_id', $id);

        if (request('year') && request('month')) {
            $query->whereYear('date', request('year'))
                ->whereMonth('date', request('month'));
        }

        $entries = $query->orderBy('date', 'desc')->get();

        return view('work.index', compact('entries'));
    }

    // =====================================================
    // STORE WORK ENTRY
    // =====================================================
    public function store(\Illuminate\Http\Request $request)
    {
        $date = $request->date;

        $timeFrom = $date . ' ' . $request->time_from;
        $timeTo = $date . ' ' . $request->time_to;

        \App\Models\WorkEntry::create([
            'employee_id' => $request->employee_id,
            'date' => $date,
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'note' => $request->note,
        ]);

        return redirect()->back();
    }
}