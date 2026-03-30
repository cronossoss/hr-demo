<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WorkEntry extends Model
{
    protected $table = 'work_entries';

    protected $fillable = [
        'employee_id',
        'work_entry_type_id',
        'date',
        'time_from',
        'time_to',
        'note'
    ];

    // RELACIJA KA ZAPOSLENOM
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function type()
{
    return $this->belongsTo(\App\Models\WorkEntryType::class, 'work_entry_type_id');
}
}