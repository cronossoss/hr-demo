<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    protected $fillable = [
        'employee_id',
        'date_from',
        'date_to',
        'days',
        'note'
    ];
}
