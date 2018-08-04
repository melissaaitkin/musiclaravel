@extends('layouts.app')

@section('content')

    <!-- Bootstrap Boilerplate... -->

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-3">Add New Artist</h2>

        @include('common.errors')


        <!-- New song Form -->
        <form action="/artist" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <!-- song Name -->
            <div class="form-group">
                <label for="artist" class="col-sm-3 control-label">Artist</label>

                <div class="col-sm-6">
                    <input type="text" name="artist" id="artist-artist" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="is_group" class="col-sm-3 control-label">Is Group</label>
                <div class="col-sm-3">
                <input type="checkbox" name="is_group" id="song-is_group" checked>
                </div>
            </div>

            <div class="form-group">
                <label for="country" class="col-sm-3 control-label">Country</label>
                <div class="col-sm-3">
                    <select class="form-control" name="country">
                        @foreach($countries as $country)
                            <option value="{{$country}}">{{$country}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Add song Button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary">Add Artist</button>
                </div>
            </div>
        </form>
    </div>
@endsection
