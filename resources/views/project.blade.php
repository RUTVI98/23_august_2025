@extends('layouts.master')
@section('title', 'Projects')

@section('content')
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h4>Projects</h4>
                <button id="addProjectBtn" class="btn btn-success btn-sm">Add Project</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="projectTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Quotation</th>
                            <th>Employees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr data-id="{{ $project->id }}">
                                <td>{{ $project->title }}</td>
                                <td>{{ $project->description }}</td>
                                <td>{{ $project->duration }}</td>
                                <td>{{ $project->quotation_price }}</td>
                                <td>
                                    @foreach($project->employees as $emp)
                                        <span class="badge bg-info">{{ $emp->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning editBtn">Edit</button>
                                    <button class="btn btn-sm btn-danger deleteBtn">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="projectModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="projectForm" method="POST" action="{{ route('project_store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Project Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="project_id" name="id">
                        <input type="text" name="title" id="title" class="form-control mb-2" placeholder="Project Name"
                            required>
                        <input type="text" name="description" id="description" class="form-control mb-2"
                            placeholder="Project Description" required>
                        <input type="number" name="duration" id="duration" class="form-control mb-2"
                            placeholder="Duration (days)" required>
                        <input type="number" name="quotation_price" id="quotation_price" class="form-control mb-2"
                            placeholder="Quotation Price" required>
                        <select name="employee_ids[]" id="employee_ids" class="form-select mb-2 select2" multiple>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
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
                placeholder: "Select employees",
                dropdownParent: $('#projectModal')
            });
        });

        $(function () {
            let table = $('#projectTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('project_datatable') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                },
                columns: [
                    { data: 'title' }, { data: 'description' },
                    { data: 'duration' }, { data: 'quotation_price' },
                    { data: 'employees', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false }
                ]
            });

            $('#addProjectBtn').click(() => {
                $('#projectForm')[0].reset();
                $('#project_id').val('');
                $('#employee_ids').val(null).trigger('change');
                $('#projectForm').validate().resetForm();
                $('#projectModal').modal('show');
            });

            $(document).on('click', '.editBtn', function () {
                $.get(`/projects/${$(this).data('id')}`, function (res) {
                    const data = res.data;
                    $('#projectForm')[0].reset();
                    $('#projectForm').validate().resetForm();
                    $('#project_id').val(data.id);
                    $('#title').val(data.title);
                    $('#description').val(data.description);
                    $('#duration').val(data.duration);
                    $('#quotation_price').val(data.quotation_price);
                    $('#employee_ids').val(data.employees.map(e => e.id)).trigger('change');
                    $('#projectModal').modal('show');
                });
            });

            $('#projectForm').validate({
                rules: {
                    title: { required: true, maxlength: 255 },
                    description: { required: true },
                    duration: { required: true, number: true, min: 1 },
                    quotation_price: { required: true, number: true, min: 0 },
                    'employee_ids[]': { required: true }
                },
                messages: {
                    title: { required: "Project name is required", maxlength: "Max 255 characters" },
                    description: { required: "Description is required" },
                    duration: { required: "Duration is required", number: "Enter a valid number", min: "Duration must be at least 1" },
                    quotation_price: { required: "Quotation price is required", number: "Enter a valid number", min: "Price must be at least 0" },
                    'employee_ids[]': { required: "Select at least one employee" }
                },
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    let formData = new FormData(form);
                    let url = $('#project_id').val() ? "{{ route('project_update') }}" : "{{ route('project_store') }}";
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: () => {
                            $('#projectModal').modal('hide');
                            table.ajax.reload();
                        },
                        error: xhr => alert('Error: ' + xhr.responseJSON.message)
                    });
                }
            });

            $(document).on('click', '.deleteBtn', function () {
                if (confirm('Delete this project?')) {
                    $.post("{{ route('project_delete') }}", {
                        id: $(this).data('id'),
                        _token: '{{ csrf_token() }}'
                    }, () => table.ajax.reload());
                }
            });
        });
    </script>
@endpush