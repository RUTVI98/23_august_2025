<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Employee;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use App\response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectEmployeeController extends Controller
{
    use response;
    public function index()
    {
        $projects = Project::all();
        $employees = Employee::all();
        return view('employee_project', compact('projects', 'employees'));
    }
    
    public function datatable(Request $request)
    {
        try {
            $query = DB::table('project_employee')
                ->select(
                    'project_employee.id',
                    'projects.title as project',
                    'employees.name as employee',
                    'employees.email'
                )
                ->join('projects', function ($join) {
                    $join->on('project_employee.project_id', '=', 'projects.id')
                        ->whereNull('projects.deleted_at');
                })
                ->join('employees', function ($join) {
                    $join->on('project_employee.employee_id', '=', 'employees.id')
                        ->whereNull('employees.deleted_at');
                })
                ->when($request->filled('search_value'), function ($q) use ($request) {
                    $search = strtolower($request->search_value);
                    $q->where(function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(projects.title) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(employees.name) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(employees.email) like ?', ["%{$search}%"]);
                    });
                });

            return DataTables::of($query)
                ->addColumn('actions', function ($row) {
                    return "<button class='btn btn-danger btn-sm deleteBtn' data-id='{$row->id}'>Remove</button>";
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_id' => 'required|exists:projects,id',
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'exists:employees,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors(), 422);
            }

            DB::beginTransaction();
            $project = Project::find($request->project_id);
            $project->employees()->syncWithoutDetaching($request->employee_ids);
            DB::commit();

            return $this->success('Employees assigned successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:project_employee,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors(), 422);
            }

            DB::beginTransaction();
            DB::table('project_employee')->where('id', $request->id)->delete();
            DB::commit();

            return $this->success('Assignment removed successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }
}



