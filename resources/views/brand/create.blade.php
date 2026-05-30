@extends('adminlte::page')

@section('title', 'Create Brand')

@section('content')

<form action="{{ route('brand.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label>Logo</label>
        <input type="file" name="logo" class="form-control">
    </div>

    <button class="btn btn-success">Save</button>
    <a href="{{ route('brand.index') }}" class="btn btn-secondary">Back</a>

</form>

@endsection