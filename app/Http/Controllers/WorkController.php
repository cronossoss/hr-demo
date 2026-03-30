<?php

namespace App\Http\Controllers;

use App\Models\WorkEntry;
use App\Models\WorkEntryType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkController extends Controller
{
    // =====================================================
    // 📅 PREGLED RADA ZA JEDNOG ZAPOSLENOG
    // =====================================================
    public function index($id)
    {
        // -------------------------------------------------
        // 📌 1. PERIOD (mesec/godina)
        // -------------------------------------------------
        \Carbon\Carbon::setLocale('sr');
        $month = request('month') ?? now()->month;
        $year = request('year') ?? now()->year;
        $holidays = [
            "$year-01-01",
            "$year-01-02",
            "$year-02-15",
            "$year-02-16",
            "$year-04-10",
            "$year-04-13",
            "$year-05-01",
            "$year-05-02",
            "$year-11-11"
        ];

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // -------------------------------------------------
        // 📌 2. OSNOVNI QUERY
        // -------------------------------------------------
        $query = WorkEntry::with('type')
            ->where('employee_id', $id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        $entries = $query->orderBy('date', 'asc')->get();
        $employee = \App\Models\Employee::findOrFail($id);

        // -------------------------------------------------
        // 📌 3. TIPOVI (za formu)
        // -------------------------------------------------
        $types = WorkEntryType::orderBy('code')->get();

        // -------------------------------------------------
        // 📌 4. RAZDVAJANJE (time vs range)
        // -------------------------------------------------
        $timeEntries = $entries->filter(
            fn($e) =>
            $e->type && $e->type->input_type === 'time'
        );

        $rangeEntries = $entries->filter(
            fn($e) =>
            $e->type && $e->type->input_type === 'range'
        );

        // -------------------------------------------------
        // 📌 5. STATISTIKA (minute)
        // -------------------------------------------------
        $regularMinutes = 0;
        $extraMinutes = 0;
        $unpaidMinutes = 0;

        foreach ($timeEntries as $e) {

            if (!$e->time_from || !$e->time_to) continue;

            $from = Carbon::parse($e->time_from);
            $to = Carbon::parse($e->time_to);

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

        // -------------------------------------------------
        // 📌 6. GRID (po danima za UI)
        // -------------------------------------------------
        $entriesByDay = $entries->map(function ($e) {
            $e->day = Carbon::parse($e->date)->day;
            return $e;
        });

        $employees = \App\Models\Employee::with(['entries' => function ($q) use ($month, $year) {
            $q->with('type')
                ->whereYear('date', $year)
                ->whereMonth('date', $month);
        }])->get()->map(function ($emp) {

            $emp->entries = $emp->entries->map(function ($e) {
                $e->day = \Carbon\Carbon::parse($e->date)->day;
                return $e;
            });

            return $emp;
        });

        // -------------------------------------------------
        // 📌 7. RETURN VIEW
        // -------------------------------------------------
        return view('work.index', compact(
            'timeEntries',
            'rangeEntries',
            'types',
            'regularMinutes',
            'extraMinutes',
            'unpaidMinutes',
            'entriesByDay',
            'daysInMonth',
            'month',
            'year',
            'employee',
            'employees',
            'holidays'
        ));
    }

    // =====================================================
    // 💾 STORE WORK ENTRY (unos rada)
    // =====================================================
    public function store(Request $request)
    {
        // -------------------------------------------------
        // 📌 1. OSNOVNA VALIDACIJA
        // -------------------------------------------------
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_entry_type_id' => 'required|exists:work_entry_types,id',
        ]);

        $type = WorkEntryType::findOrFail($request->work_entry_type_id);

        // -------------------------------------------------
        // 📌 2. RANGE UNOS (godišnji, bolovanje...)
        // -------------------------------------------------
        if ($type->input_type === 'range') {

            $request->validate([
                'date_from' => 'required',
                'date_to' => 'required',
            ]);

            $from = Carbon::createFromFormat('d.m.Y', $request->date_from);
            $to = Carbon::createFromFormat('d.m.Y', $request->date_to);

            while ($from <= $to) {

                // 👉 spreči duplikate
                $exists = WorkEntry::where('employee_id', $request->employee_id)
                    ->where('date', $from->format('Y-m-d'))
                    ->where('work_entry_type_id', $request->work_entry_type_id)
                    ->exists();

                if (!$exists) {
                    WorkEntry::create([
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

        // -------------------------------------------------
        // 📌 3. TIME UNOS (rad po satima)
        // -------------------------------------------------
        else {

            $request->validate([
                'date' => 'required',
                'time_from' => 'required',
                'time_to' => 'required',
            ]);

            $date = Carbon::createFromFormat('d.m.Y', $request->date)
                ->format('Y-m-d');

            // 👉 spreči duplikate
            $exists = WorkEntry::where('employee_id', $request->employee_id)
                ->where('date', $date)
                ->where('work_entry_type_id', $request->work_entry_type_id)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'error' => 'Već postoji unos za ovaj dan.'
                ]);
            }

            WorkEntry::create([
                'employee_id' => $request->employee_id,
                'work_entry_type_id' => $request->work_entry_type_id,
                'date' => $date,
                'time_from' => $date . ' ' . $request->time_from,
                'time_to' => $date . ' ' . $request->time_to,
                'note' => $request->note,
            ]);
        }

        return back()->with('success', 'Unos uspešno sačuvan');
    }

    public function updateOrCreate(Request $request)
    {
        try {

            $request->validate([
                'employee_id' => 'required',
                'date' => 'required|date',
                'type_id' => 'required'
            ]);

            $entry = \App\Models\WorkEntry::where('employee_id', $request->employee_id)
                ->where('date', $request->date)
                ->first();

            if ($entry) {
                $entry->work_entry_type_id = $request->type_id;
                $entry->save();
            } else {
                \App\Models\WorkEntry::create([
                    'employee_id' => $request->employee_id,
                    'work_entry_type_id' => $request->type_id,
                    'date' => $request->date
                ]);
            }

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
