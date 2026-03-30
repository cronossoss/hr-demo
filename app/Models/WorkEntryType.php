<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkEntryType extends Model
{
    protected $table = 'work_entry_types';

    protected $fillable = [
        'name',
        'code',
        'input_type',
        'is_paid',
        'counts_as_work',
        'affects_vacation',
        'pay_multiplier'
    ];
}