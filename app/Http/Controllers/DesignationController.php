<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\response;
use Exception;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;


class DesignationController extends Controller
{
    use response;

    public function index()
    {
        return view('designation');
    }

    public function list()
    {
        try {
            return $this->success(['data' => Designation::all()], 200);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $designation = Designation::onlyTrashed()->where('title', $request->title)->first();

            if ($designation) {
                if ($designation->trashed()) {
                    $designation->restore();
                    return $this->success('Designation restored successfully');
                }
            }
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255|unique:designations,title',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            $designation = Designation::create((['title' => $request->title]));
            return $this->successData($designation);

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->get('id');

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:designations,id',
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('designations', 'title')->ignore($id),
                ],
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            $designation = Designation::find($id);
            if (!$designation) {
                return $this->error('Designation not found.', 404);
            }

            $update = $designation->update(['title' => $request->get('title')]);

            if ($update) {
                return $this->success('Designation Updated Successfully.', 200);
            } else {
                return $this->error('No changes detected or invalid ID.', 400);
            }

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $designation = Designation::find($id);

            if (!$designation) {
                return $this->error('Designation not found.', 404);
            }

            return $this->successData($designation);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:designations,id',
            ]);

            if ($validator->fails()) {
                return $this->validatorerror($validator->errors());
            }

            $designation = Designation::find($request->id);

            if (!$designation) {
                return $this->error('Designation not found.', 404);
            }

            $designation->delete();
            return $this->success('Deleted successfully');

        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
    public function datatable(Request $request)
    {
        try {
                $query = Designation::select('id', 'title')
                    ->whereNull('deleted_at');

                return DataTables::of($query)
                    ->addColumn('actions', function ($designation) {
                        return '
                        <button class="btn btn-sm btn-warning editBtn" data-id="' . $designation->id . '" data-title="' . e($designation->title) . '">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="' . $designation->id . '">Delete</button>
                    ';
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            
        } catch (Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

}
