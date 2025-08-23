<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Designation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\response;


class EmployeeController extends Controller
{
    use response;
    public function index()
    {
        $designations = Designation::all();
        return view('employee', compact('designations'));
    }

    public function datatable(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Employee::select('employees.*', 'designations.title as designation_title')
                    ->join('designations', 'employees.designation_id', '=', 'designations.id')
                    ->when($request->filled('search_value'), function ($q) use ($request) {
                        $search = strtolower($request->search_value);
                        $q->where(function ($sub) use ($search) {
                            $sub->whereRaw('LOWER(employees.name) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(employees.email) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(employees.role) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(designations.title) LIKE ?', ["%{$search}%"]);
                        });
                    });

                return DataTables::of($query)
                    ->addColumn('designation', function ($e) {
                        return $e->designation_title ?? '-';
                    })
                    ->addColumn('actions', function ($e) {
                        return '
                    <button class="btn btn-warning btn-sm editBtn" data-id="' . $e->id . '">Edit</button>
                    <button class="btn btn-danger btn-sm deleteBtn" data-id="' . $e->id . '">Delete</button>';
                    })

                    ->rawColumns(['designation', 'actions'])
                    ->make(true);
            }

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $employee = Employee::onlyTrashed()
                ->where([
                    'name' => $request->name,
                    'email' => $request->email,
                    'salary' => $request->salary,
                    'role' => $request->role,
                    'designation_id' => $request->designation_id,
                ])->first();

            if ($employee) {
                $employee->restore();
                return $this->success('Employee restored successfully.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:employees,email',
                'salary' => 'required|numeric|min:0',
                'role' => 'required|in:Developer,TeamLead,Manager',
                'designation_id' => 'required|exists:designations,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            $data = $request->only(['name', 'email', 'salary', 'role', 'designation_id']);
            Employee::create($data);

            return $this->success('Employee Added Successfully.');

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return $this->error('Employee not found.', 404);
            }

            return $this->successData($employee);

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $employee = Employee::find($request->id);

            if (!$employee) {
                return $this->error('Employee not found.', 404);
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:employees,id',
                'name' => 'required|string|max:100',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('employees', 'email')->ignore($employee->id),
                ],
                'salary' => 'required|numeric|min:0',
                'role' => 'required|in:Developer,TeamLead,Manager',
                'designation_id' => 'required|exists:designations,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors(), 422);
            }

            $data = $request->only(['name', 'email', 'salary', 'role', 'designation_id']);
            $employee->update($data);

            return $this->success('Employee Updated Successfully.', 200);

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:employees,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            $employee = Employee::find($request->id);

            if (!$employee) {
                return $this->error('Employee not found.', 404);
            }

            $employee->delete();
            return $this->success('Employee deleted successfully');
        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
