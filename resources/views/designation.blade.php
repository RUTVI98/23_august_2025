@extends('layouts.master')
@section('title', 'Designation Page')

@section('content')
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Designation List</h4>
                <button class="btn btn-success btn-sm" id="addDesignationBtn">Add Designation</button>
            </div>
            <div class="card-body">
                <table id="designationTable" class="table table-bordered table-striped">
                    <thead class="table-dark"></thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="designationModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="designationForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Designation Info</h5>
                    </div>
                    <div class="modal-body">
                        <label for="designationName">Designation Name</label>
                        <input type="hidden" name="id" id="designation_id">
                        <input type="text" name="title" id="designationName" class="form-control" required>
                        <div id="nameError" class="text-danger small mt-1"></div>
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
        $(document).ready(function () {
            var table = $('#designationTable').DataTable({
                processing: true,
                serverSide: true,
                retrieve: true,
                ajax: {
                    url: "{{ route('designation_datatable') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                },
                columns: [
                    { title: 'ID', data: 'id' },
                    { title: 'Designation Title', data: 'title' },
                    { title: 'Actions', data: 'actions', orderable: false, searchable: false }
                ]
            });

            $('#addDesignationBtn').click(function () {
                $('#designationForm')[0].reset();
                $('#designation_id').val('');
                $('#nameError').text('');
                $('#designationModal').modal('show');
            });

            $(document).on('click', '.editBtn', function () {
                const id = $(this).data('id');
                $('#nameError').text('');
                $.get(`/designations/${id}`, function (response) {
                    $('#designation_id').val(response.data.id);
                    $('#designationName').val(response.data.title);
                    $('#designationModal').modal('show');
                }).fail(function () {
                    alert("Failed to fetch designation details.");
                });
            });

            $('#designationForm').validate({
                rules: {
                    title: {
                        required: true,
                        minlength: 3
                    }
                },
                messages: {
                    title: {
                        required: "Please enter a designation name.",
                        minlength: "Title must be at least 3 characters long."
                    }
                },
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    const id = $('#designation_id').val();
                    const url = id ? `{{ route('designation_update') }}` : `{{ route('designation_store') }}`;

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: $(form).serialize(),
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function () {
                            $('#designationModal').modal('hide');
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            if (xhr.status === 422) {
                                $('#nameError').text("Please enter a valid name.");
                            } else if (xhr.status === 400) {
                                $('#nameError').text("Designation name already exists.");
                            } else {
                                $('#nameError').text("Something went wrong.");
                            }
                        }
                    });
                }
            });

            $(document).on('click', '.deleteBtn', function () {
                if (confirm('Are you sure?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        url: "{{ route('designation_delete') }}",
                        method: 'POST',
                        data: {
                            id: id,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function () {
                            table.ajax.reload();
                        },
                        error: function (xhr) {
                            console.error(xhr.responseJSON);
                            alert('Delete failed: ' + (xhr.responseJSON?.error || 'Unknown error'));
                        }
                    });
                }
            });
        });
    </script>
@endpush