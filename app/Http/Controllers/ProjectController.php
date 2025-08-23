<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use App\response;

class ProjectController extends Controller
{
    use response;

    public function index()
    {
        $projects = Project::all();
        $employees = Employee::all();
        return view('project', compact('projects', 'employees'));
    }

    public function datatable(Request $request)
    {
        try {
            $query = Project::query()
                ->select('projects.*')
                ->with([
                    'employees' => fn($q) => $q->select('employees.id', 'employees.name')
                ])
                ->whereNull('projects.deleted_at');

            return DataTables::eloquent($query)
                ->addColumn(
                    'employees',
                    fn($project) =>
                    $project->employees->pluck('name')
                        ->map(fn($name) => "<span class='badge bg-info'>$name</span>")
                        ->implode(' ')
                )
                ->addColumn('actions', fn($project) => "
                    <button class='btn btn-sm btn-warning editBtn' data-id='{$project->id}'>Edit</button>
                    <button class='btn btn-sm btn-danger deleteBtn' data-id='{$project->id}'>Delete</button>
                ")
                ->rawColumns(['employees', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'required|integer|min:1',
                'quotation_price' => 'required|numeric|min:0',
                'employee_ids' => 'array|nullable',
                'employee_ids.*' => 'exists:employees,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors(), 422);
            }

            DB::beginTransaction();
            $project = Project::create($request->only(['title', 'description', 'duration', 'quotation_price']));
            $project->employees()->sync($request->employee_ids ?? []);
            DB::commit();

            return $this->success('Project created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $project = Project::with('employees')->where('id', $id)->first();

            if (!$project) {
                return $this->error('Project not found.', 404);
            }

            return $this->successData($project);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'required|integer|min:1',
                'quotation_price' => 'required|numeric|min:0',
                'employee_ids' => 'array|nullable',
                'employee_ids.*' => 'exists:employees,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            DB::beginTransaction();
            $project = Project::where('id', $request->id)->first();
            if (!$project) {
                return $this->error('Project not found.', 404);
            }

            $updated = $request->only(['title', 'description', 'duration', 'quotation_price']);
            $project->update($updated);
            $project->employees()->sync($request->employee_ids ?? []);
            DB::commit();

            return $this->success('Project updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:projects,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            DB::beginTransaction();
            $project = Project::where('id', $request->id)->first();
            if (!$project) {
                return $this->error('Project not found.', 404);
            }

            $project->employees()->detach();
            $project->delete();
            DB::commit();

            return $this->success('Project deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 500);
        }
    }
}
