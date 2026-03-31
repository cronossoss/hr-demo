<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function test($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $service = new AttendanceService();

        $result = $service->processDay($employee, now()->toDateString());

        return response()->json($result);
    }
}
