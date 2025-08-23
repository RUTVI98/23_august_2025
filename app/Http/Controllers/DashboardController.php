<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Exception;
use App\response;
class DashboardController extends Controller
{
    use response;
    public function index()
    {
        return view('dashboard');
    }

    public function getCounts()
    {
        try {
            $data = [
                'employeeCount' => DB::table('employees')->whereNull('deleted_at')->count(),
                'projectCount' => DB::table('projects')->whereNull('deleted_at')->count(),
                'designationCount' => DB::table('designations')->whereNull('deleted_at')->count(),
                'employees' => DB::table('employees')
                    ->whereNull('deleted_at')
                    ->select('name', 'role')
                    ->get(),
                'projects' => DB::table('projects')
                    ->whereNull('deleted_at')
                    ->select('title', 'duration')
                    ->get(),
                'designations' => DB::table('designations')
                    ->whereNull('deleted_at')
                    ->select('title')
                    ->get(),
            ];

            return $this->successData($data);

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
