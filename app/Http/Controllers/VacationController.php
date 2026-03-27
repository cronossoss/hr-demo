<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacation;
use App\Models\EmployeeVacation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class VacationController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $from = Carbon::parse($request->date_from)->startOfDay();
        $to = Carbon::parse($request->date_to)->startOfDay();

        $days = 0;

        $holidays = [
            '2026-01-01',
            '2026-01-02',
            '2026-05-01',
            '2026-05-02',
            // dodaj po potrebi praznike Obavezno!
        ];

        $period = CarbonPeriod::create($from, $to);

        foreach ($period as $date) {

            if ($date->isWeekend()) {
                continue;
            }

            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }

            $days++;
        }

        $year = $from->year;

        // koliko je već iskorišćeno
        $totalUsed = \App\Models\Vacation::where('employee_id', $request->employee_id)
            ->whereYear('date_from', $year)
            ->sum('days');

        // uzmi ili napravi balans
        $balance = EmployeeVacation::firstOrCreate(
            [
                'employee_id' => $request->employee_id,
                'year' => $year
            ],
            [
                'total_days' => 20
            ]
        );

        $remaining = $balance->total_days - $totalUsed;

        // ❗ VALIDACIJA
        if ($days > $remaining) {
            return response()->json([
                'error' => 'Nema dovoljno dana godišnjeg'
            ], 422);
        }

        $vacation = Vacation::create([
            'employee_id' => $request->employee_id,
            'date_from' => $from,
            'date_to' => $to,
            'days' => $days,
            'note' => $request->note,
        ]);

        $year = $from->year;

        $balance = EmployeeVacation::firstOrCreate(
            [
                'employee_id' => $request->employee_id,
                'year' => $year
            ],
            [
                'total_days' => 20
            ]
        );

        $totalUsed = \App\Models\Vacation::where('employee_id', $request->employee_id)
            ->whereYear('date_from', $year)
            ->sum('days');

        $balance->used_days = $totalUsed;
        $balance->save();

        return response()->json(['success' => true]);
    }
}
