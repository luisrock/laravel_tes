@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <h1>Create Permission</h1>

            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
        
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control">
                </div>
        
                <button type="submit" class="btn btn-primary">Create Permission</button>
            </form>

        </div>
    </div>
</div>
@endsection