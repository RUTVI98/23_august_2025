<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
   use SoftDeletes;

    protected $fillable = ['title', 'description', 'duration', 'quotation_price'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_employee');
    } 
}
