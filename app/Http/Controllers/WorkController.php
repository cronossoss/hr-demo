<?php

namespace App\Http\Controllers;

use App\Models\WorkEntry;
use App\Models\WorkEntryType;

class WorkController extends Controller
{
    // =====================================================
    // WORK PREGLED
    // =====================================================
    public function index($id)
    {
        $query = \App\Models\WorkEntry::with('type')
            ->where('employee_id', $id);

        if (request('year') && request('month')) {
            $query->whereYear('date', request('year'))
                ->whereMonth('date', request('month'));
        }

        $entries = $query->orderBy('date', 'asc')->get();
        $types = WorkEntryType::orderBy('code')->get();

        // =========================
        // RAZDVAJANJE
        // =========================

        $timeEntries = $entries->filter(function ($e) {
            return $e->type && $e->type->input_type === 'time';
        });

        $rangeEntries = $entries->filter(function ($e) {
            return $e->type && $e->type->input_type === 'range';
        });

        $regularMinutes = 0;
        $extraMinutes = 0;
        $unpaidMinutes = 0;

        foreach ($timeEntries as $e) {

            if (!$e->time_from || !$e->time_to) continue;

            $from = \Carbon\Carbon::parse($e->time_from);
            $to = \Carbon\Carbon::parse($e->time_to);

            $minutes = max(0, $from->diffInMinutes($to));

            $multiplier = $e->type->pay_multiplier ?? 1;

            if ($multiplier == 1) {
                $regularMinutes += $minutes;
            } elseif ($multiplier > 1) {
                $extraMinutes += $minutes;
            } elseif ($multiplier == 0) {
                $unpaidMinutes += $minutes;
            }
        }

        return view('work.index', compact(
            'timeEntries',
            'rangeEntries',
            'types',
            'regularMinutes',
            'extraMinutes',
            'unpaidMinutes'
        ));
    }

    // =====================================================
    // STORE WORK ENTRY
    // =====================================================
    public function store(\Illuminate\Http\Request $request)
{
    // =========================
    // OSNOVNA VALIDACIJA
    // =========================
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'work_entry_type_id' => 'required|exists:work_entry_types,id',
    ]);

    $type = \App\Models\WorkEntryType::find($request->work_entry_type_id);

    // =========================
    // RANGE (godišnji, bolovanje...)
    // =========================
    if ($type->input_type === 'range') {

        $request->validate([
            'date_from' => 'required',
            'date_to' => 'required',
        ]);

        // zabrana vremena
        if ($request->time_from || $request->time_to) {
            return back()->withErrors([
                'error' => 'Za ovu vrstu unosa ne unosi se vreme.'
            ]);
        }

        $from = \Carbon\Carbon::createFromFormat('d.m.Y', $request->date_from);
        $to = \Carbon\Carbon::createFromFormat('d.m.Y', $request->date_to);

        while ($from <= $to) {

            // duplikat check
            $exists = \App\Models\WorkEntry::where('employee_id', $request->employee_id)
                ->where('date', $from->format('Y-m-d'))
                ->where('work_entry_type_id', $request->work_entry_type_id)
                ->exists();

            if (!$exists) {
                \App\Models\WorkEntry::create([
                    'employee_id' => $request->employee_id,
                    'work_entry_type_id' => $request->work_entry_type_id,
                    'date' => $from->format('Y-m-d'),
                    'time_from' => null,
                    'time_to' => null,
                    'note' => $request->note,
                ]);
            }

            $from->addDay();
        }
    }

    // =========================
    // TIME (rad, pauza...)
    // =========================
    else {

        $request->validate([
            'date' => 'required',
            'time_from' => 'required',
            'time_to' => 'required',
        ]);

        $date = \Carbon\Carbon::createFromFormat('d.m.Y', $request->date)->format('Y-m-d');

        // duplikat check
        $exists = \App\Models\WorkEntry::where('employee_id', $request->employee_id)
            ->where('date', $date)
            ->where('work_entry_type_id', $request->work_entry_type_id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'error' => 'Već postoji unos za ovaj dan.'
            ]);
        }

        $timeFrom = $date . ' ' . $request->time_from;
        $timeTo = $date . ' ' . $request->time_to;

        \App\Models\WorkEntry::create([
            'employee_id' => $request->employee_id,
            'work_entry_type_id' => $request->work_entry_type_id,
            'date' => $date,
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'note' => $request->note,
        ]);
    }

    return redirect()->back()->with('success', 'Unos uspešno sačuvan');
}
}