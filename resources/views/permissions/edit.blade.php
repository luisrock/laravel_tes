@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <h1>Edit Permission</h1>

            <form action="{{ route('permissions.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')
        
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $permission->name }}">
                </div>
        
                <button type="submit" class="btn btn-primary">Update Permission</button>
            </form>

        </div>
    </div>
</div>
@endsection