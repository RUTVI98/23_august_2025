@extends('layouts.master')
@section('title', ' Login Page')

@section('content')
    @if(session('error'))
        <div class="alert alert-warning">
            {{ session('error') }}
        </div>
    @endif

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="mt-4 card card-body shadow">

                    <h4>Login Page</h4>

                    <form id="loginForm">
                        @csrf
                        <div class="mb-3">
                            Email Id:
                            <input type="email" name="email" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            Password:
                            <input type="password" name="password" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100">Login Now</button>
                        </div>
                    </form>
                </div>
            </div>
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
            $("#loginForm").validate({
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                rules: {
                    password: { minlength: 8 }
                },
                messages: {
                    password: { minlength: "Your Password must be at least 8 characters long." }
                }
            });

            $("#loginForm").on("submit", function (e) {
                e.preventDefault();
                $(".alert").remove();

                if (!$(this).valid()) return;

                $.ajax({
                    url: "{{ route('login_error') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        window.location.href = response.redirect || "{{ route('dashboard') }}";
                    },
                    error: function (xhr) {
                        let message = "An error occurred.";
                        if (xhr.status === 400 || xhr.status === 422 ) {
                            const errors = xhr.responseJSON?.error || xhr.responseJSON?.errors;

                            if (typeof errors === 'object') {
                                message = Object.values(errors)
                                    .flat()
                                    .join("<br>");
                            } else {
                                message = errors || "Invalid input.";
                            }
                        } else if (xhr.status === 401) {
                            message = "Invalid email or password.";
                        } else if (xhr.status === 500) {
                            message = "Internal server error. Please try again later.";
                        } else {
                            message = xhr.responseJSON?.error || "Unexpected error.";
                        }
                        $(".alert-danger").remove();
                        $(".content main").prepend('<div class="alert alert-danger">' + message + '</div>');
                    }
                });
            });
        });
    </script>
@endpush
