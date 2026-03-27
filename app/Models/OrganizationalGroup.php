<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\OrganizationalUnit;

class OrganizationalGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function units()
    {
        return $this->hasMany(OrganizationalUnit::class, 'group_id');
    }

    public function overview()
    {
        $groups = OrganizationalGroup::with([
            'units' => function ($q) {
                $q->withCount('employees');
            }
        ])
            ->withCount('units')
            ->get();

        return view('organizational-units.overview', compact('groups'));
    }
}
