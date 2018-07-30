@extends('layouts.app')

@section('content')

    <!-- Bootstrap Boilerplate... -->

    <div class="panel-body">
        <!-- Display Validation Errors -->

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                Please fix the following errors
            </div>
        @endif

        <!-- New song Form -->
        <form action="/song" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <!-- song Name -->
            <div class="form-group">
                <label for="song" class="col-sm-3 control-label">Song</label>

                <div class="col-sm-6">
                    <input type="text" name="title" id="song-title" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="album" class="col-sm-3 control-label">Album</label>

                <div class="col-sm-6">
                    <input type="text" name="album" id="song-album" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="year" class="col-sm-3 control-label">Year</label>

                <div class="col-sm-3">
                    <input type="text" name="year" id="song-year" class="form-control">
                </div>
            </div>

            <!-- Add song Button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-default">
                        <i class="fa fa-plus"></i> Add song
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection