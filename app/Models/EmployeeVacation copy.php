<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeVacation extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'total_days',
        'used_days',
    ];
}
