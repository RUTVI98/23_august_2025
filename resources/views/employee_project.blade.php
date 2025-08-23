@extends('layouts.master')

@section('title', 'Employee - Project Assignments')

@section('content')
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h4>Employee - Project Assignments</h4>
                <button class="btn btn-success btn-sm" id="addAssignmentBtn">Assign Employees</button>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped table-hover w-100" id="employeeProjectTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Project</th>
                            <th>Employee</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="assignmentForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Employees</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" id="project_id" class="form-select" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="employee_ids" class="form-label">Employees</label>
                            <select name="employee_ids[]" id="employee_ids" class="form-select" multiple required>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#employee_ids').select2({
                placeholder: "Select Employees",
                dropdownParent: $('#assignmentModal')
            });

            const table = $('#employeeProjectTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee_project_datatable') }}",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: function (d) {
                        d.search_value = d.search.value;
                    }
                },
                columns: [
                    { data: 'project', name: 'projects.title' },
                    { data: 'employee', name: 'employees.name' },
                    { data: 'email', name: 'employees.email' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });

            $('#addAssignmentBtn').click(() => {
                $('#assignmentForm')[0].reset();
                $('#employee_ids').val(null).trigger('change');
                $('#assignmentForm').validate().resetForm();
                $('#assignmentModal').modal('show');
            });

            $('#assignmentForm').validate({
                rules: {
                    project_id: { required: true },
                    'employee_ids[]': { required: true }
                },
                messages: {
                    project_id: { required: "Please select a project" },
                    'employee_ids[]': { required: "Please select at least one employee" }
                },
                errorClass: 'text-danger small',
                submitHandler: function (form) {
                    $.ajax({
                        url: "{{ route('employee_project_store') }}",
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function (res) {
                            if (res.status) {
                                $('#assignmentModal').modal('hide');
                                table.ajax.reload();
                            } else {
                                alert(res.error || 'Failed to assign employees.');
                            }
                        },
                        error: function (xhr) {
                            alert(xhr.responseJSON?.error || 'Something went wrong.');
                        }
                    });
                }
            });

            $(document).on('click', '.deleteBtn', function () {
                let id = $(this).data('id');
                if (!confirm('Are you sure to remove this assignment?')) return;

                $.post("{{ route('employee_project_delete') }}", { id: id, _token: '{{ csrf_token() }}' }, function (res) {
                    if (res.status) table.ajax.reload();
                    else alert(res.error || 'Failed to remove assignment.');
                });
            });
        });
    </script>
@endpush