<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Absence;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceService
{
    public function processDay(Employee $employee, $date)
    {


        $date = Carbon::parse($date)->toDateString();

        $entries = \App\Models\WorkEntry::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->orderBy('time_from')
            ->get();

        if ($entries->isEmpty()) {
            return ['error' => 'NO DATA'];
        }

        $first = $entries->first();
        $last  = $entries->last();

        $checkIn = $first->time_from;
        $checkOut = $last->time_to;

        // ==========================
        // PLAN RADA (za sada hardcode)
        // ==========================
        $start = Carbon::parse($date . ' 08:00:00');
        $end   = Carbon::parse($date . ' 16:00:00');

        $realStart = Carbon::parse($checkIn);
        $realEnd   = Carbon::parse($checkOut);

        // ==========================
        // KAŠNJENJE
        // ==========================
        $lateMinutes = 0;

        if ($realStart->gt($start)) {
            $lateMinutes = $start->diffInMinutes($realStart);
        }

        // ==========================
        // PREKOVREMENI
        // ==========================
        $overtimeMinutes = 0;

        if ($realEnd->gt($end)) {
            $overtimeMinutes = $end->diffInMinutes($realEnd);
        }

        $workMinutes = 0;

        foreach ($entries as $entry) {
            if ($entry->time_from && $entry->time_to) {
                $from = Carbon::parse($entry->time_from);
                $to   = Carbon::parse($entry->time_to);

                $workMinutes += abs($from->diffInMinutes($to));
            }
        }




        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->employee_id = $employee->id;
            $attendance->date = $date;
        }

        $attendance->check_in = $checkIn;
        $attendance->check_out = $checkOut;
        $attendance->worked_minutes = $workMinutes;
        $attendance->late_minutes = $lateMinutes ?? 0;
        $attendance->overtime_minutes = $overtimeMinutes ?? 0;

        $attendance->save();

        return $attendance;
    }
}
