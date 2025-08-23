@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="container mt-5">
        <h2 class="mb-4">Welcome to Dashboard</h2>

        <div class="row" id="dashboard-counts">
            @foreach (['Employees' => 'employeeCount', 'Projects' => 'projectCount', 'Designations' => 'designationCount'] as $label => $id)
                <div class="col-md-4">
                    <div class="card text-white bg-secondary mb-3">
                        <div class="card-header">{{ $label }}</div>
                        <div class="card-body">
                            <h5 class="card-title" id="{{ $id }}">Loading...</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <h4>Recent Employees</h4>
                <ul id="employeeList" class="list-group"></ul>
            </div>
            <div class="col-md-4">
                <h4>Recent Projects</h4>
                <ul id="projectList" class="list-group"></ul>
            </div>
            <div class="col-md-4">
                <h4>Recent Designations</h4>
                <ul id="designationList" class="list-group"></ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/dashboard/counts',
                method: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.data) {
                        const { employeeCount, projectCount, designationCount, employees, projects, designations } = res.data;

                        $('#employeeCount').text(employeeCount);
                        $('#projectCount').text(projectCount);
                        $('#designationCount').text(designationCount);

                        const $empList = $('#employeeList');
                        $empList.empty();
                        employees.forEach(e => {
                            $empList.append(`<li class="list-group-item">${e.name} (${e.role})</li>`);
                        });

                        const $projList = $('#projectList');
                        $projList.empty();
                        projects.forEach(p => {
                            $projList.append(`<li class="list-group-item">${p.title} (${p.duration})</li>`);
                        });

                        const $desList = $('#designationList');
                        $desList.empty();
                        designations.forEach(d => {
                            $desList.append(`<li class="list-group-item">${d.title}</li>`);
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error || status);
                }
            });
        });
    </script>
@endpush