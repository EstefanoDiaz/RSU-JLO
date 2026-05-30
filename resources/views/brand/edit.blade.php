@extends('adminlte::page')

@section('title', 'Edit Brand')

@section('content')

<form action="{{ route('brand.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ $brand->name }}" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control">{{ $brand->description }}</textarea>
    </div>

    <div class="form-group">
        <label>Logo</label>
        <input type="file" name="logo" class="form-control">

        @if($brand->logo)
            <img src="{{ asset('storage/'.$brand->logo) }}" width="80" class="mt-2">
        @endif
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('brand.index') }}" class="btn btn-secondary">Back</a>

</form>

@endsection