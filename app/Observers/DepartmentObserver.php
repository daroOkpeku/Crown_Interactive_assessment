<?php

namespace App\Observers;

use App\Models\Department;
use Illuminate\Support\Facades\Cache;

class DepartmentObserver
{
    public function created(Department $department)
    {
        Cache::forget('departments_all');
    }

    public function updated(Department $department)
    {
        Cache::forget('departments_all');
    }

    public function deleted(Department $department)
    {
        Cache::forget('departments_all');
    }
}
