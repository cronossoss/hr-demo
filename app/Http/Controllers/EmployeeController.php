<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeVacation;
use Illuminate\Http\Request;


class EmployeeController extends Controller
{
    // =====================================================
    // LISTA ZAPOSLENIH
    // =====================================================
    public function index()
    {
        $employees = Employee::with('organizationalUnit')->get();

        return view('employees.index', compact('employees'));
    }

    // =====================================================
    // SHOW (DETALJI ZAPOSLENOG)
    // =====================================================
    public function show($id)
    {
        $employee = Employee::with('organizationalUnit')
            ->findOrFail($id);

        // godišnji
        $vacation = \App\Models\EmployeeVacation::where('employee_id', $id)
            ->where('year', now()->year)
            ->first();

        return view('employees.show', [
            'employee' => $employee,
            'vacation' => $vacation
        ]);
    }

    // =====================================================
    // CREATE FORM
    // =====================================================
    public function create()
    {
        $units = \App\Models\OrganizationalUnit::all();
        $contractTypes = \App\Models\ContractType::all();

        return view('employees.create', compact('units', 'contractTypes'));
    }

    // =====================================================
    // STORE (SNIMANJE ZAPOSLENOG)
    // =====================================================
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        \App\Models\Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'position' => $request->position,
            'organizational_unit_id' => $request->organizational_unit_id,
        ]);

        return redirect('/employees');
    }
}