@extends('layouts.master')
@section('title', 'Employee Management')

@section('content')
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h4>Employee List</h4>
                <button class="btn btn-success btn-sm" id="addEmployeeBtn">Add Employee</button>
            </div>
            <div class="card-body">
                <table id="employeeTable" class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Salary</th>
                            <th>Role</th>
                            <th>Designation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="employeeForm">
                @csrf
                <input type="hidden" name="id" id="employee_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Employee Info</h5>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="name" id="name" class="form-control mb-2" placeholder="Name" required>
                        <input type="email" name="email" id="email" class="form-control mb-2" placeholder="Email" required>
                        <input type="number" name="salary" id="salary" class="form-control mb-2" placeholder="Salary"
                            required>

                        <select name="role" id="role" class="form-control mb-2" required>
                            <option value="">Select Role</option>
                            <option value="Developer">Developer</option>
                            <option value="TeamLead">TeamLead</option>
                            <option value="Manager">Manager</option>
                        </select>

                        <select name="designation_id" id="designation_id" class="form-control mb-2" required>
                            <option value="">Select Designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('styles')
    <style>
        label.error {
            color: red;
            font-weight: bold;
            display: block;
            margin-top: 5px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            let table = $('#employeeTable').DataTable({
                processing: true,
                serverSide: true,
                retrieve: true,
                ajax: {
                    url: "{{ route('employee_datatable') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: function (d) {
                        d.search_value = d.search.value;
                    }
                },
                columns: [
                    { data: 'id' }, { data: 'name' ,name:'employees.name'}, { data: 'email' ,name:'employees.email'},
                    { data: 'salary' }, { data: 'role',name:'employees.role' }, { data: 'designation',name:'designations.title'},
                    { data: 'actions', orderable: false, searchable: false }
                ]
            });

            $('#addEmployeeBtn').click(() => {
                $('#employeeForm')[0].reset();
                $('#employee_id').val('');
                $('#preview').hide();
                $('#employeeModal').modal('show');
            });

            $(document).on('click', '.editBtn', function () {
                let id = $(this).data('id');
                $.get(`/employees/${id}`, function (response) {
                    if (response.data) {
                        $('#employee_id').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#email').val(response.data.email);
                        $('#salary').val(response.data.salary);
                        $('#role').val(response.data.role);
                        $('#designation_id').val(response.data.designation_id);
                        $('#employeeModal').modal('show');
                    } else {
                        alert("No employee data found.");
                    }
                }).fail(function () {
                    alert("Failed to fetch employee details.");
                });
            });


            $('#employeeForm').validate({
                rules: {
                    name: { required: true, minlength: 3 },
                    email: { required: true, email: true },
                    salary: { required: true, number: true, min: 1 },
                    role: { required: true },
                    designation_id: { required: true }
                },
                messages: {
                    name: { required: "Enter employee name", minlength: "At least 3 characters" },
                    email: { required: "Enter email", email: "Enter a valid email" },
                    salary: { required: "Enter salary", number: "Only numbers allowed", min: "Must be greater than 0" },
                    role: { required: "Select a role" },
                    designation_id: { required: "Select a designation" }
                },
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    let formData = new FormData(form);
                    let url = $('#employee_id').val() ? "{{ route('employee_update') }}" : "{{ route('employee_store') }}";

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: () => {
                            $('#employeeModal').modal('hide');
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            let message = "An error occurred.";

                            if (xhr.status === 400 || xhr.status === 422) {
                                const errors = xhr.responseJSON?.error || xhr.responseJSON?.errors;
                                if (typeof errors === 'object') {
                                    message = Object.values(errors).flat().join("\n");
                                } else {
                                    message = errors || "Invalid input.";
                                }
                            } else if (xhr.status === 401) {
                                message = "Unauthorized action.";
                            } else if (xhr.status === 500) {
                                message = "Internal server error. Please try again later.";
                            } else {
                                message = xhr.responseJSON?.error || "Unexpected error.";
                            }
                        }
                    });
                }
            });

            $(document).on('click', '.deleteBtn', function () {
                if (confirm('Delete this employee?')) {
                    $.post("{{ route('employee_delete') }}", {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    }, () => table.ajax.reload());
                }
            });
        });
    </script>
@endpush