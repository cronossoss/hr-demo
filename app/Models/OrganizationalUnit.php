<?php

// app/Http/Models/OrganizationalUnit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrganizationalGroup;

class OrganizationalUnit extends Model
{
    protected $fillable = [
        'name',
        'code',
        'parent_id'
    ];

    public function parent()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(OrganizationalUnit::class, 'parent_id')
            ->withCount('employees') // 👈 DODAJ
            ->orderBy('name');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'organizational_unit_id');
    }

    public function group()
    {
        return $this->belongsTo(OrganizationalGroup::class, 'group_id');
    }
}
