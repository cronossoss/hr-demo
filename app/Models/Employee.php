<?php
// app/Models/Employee.php -->
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'position',
        'employee_number',
        'jmbg',
        'organizational_unit_id',
        'contract_type_id',
        'contract_end_date',
        'employment_date',
        'birth_date',
        'email',
        'phone_work',
        'phone_private',
        'gender'
    ];

    public function organizationalUnit()
    {
        return $this->belongsTo(\App\Models\OrganizationalUnit::class);
    }

    
}
