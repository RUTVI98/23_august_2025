<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'role', 'salary', 'designation_id'];

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_employee');
    }
}
