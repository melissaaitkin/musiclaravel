@extends('layouts.app')

@section('content')

    <!-- Bootstrap Boilerplate... -->

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-3">{{$title}}</h2>

        @include('common.errors')


        <!-- New song Form -->
        <form action="/artist" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div>
                 <input type="hidden" name="redirects_to" value="{{ URL::previous() }}"/>
            </div>

            <!-- song Name -->
            <div class="form-group">
                <label for="artist" class="col-sm-3 control-label">Artist</label>

                <div class="col-sm-6">
                    <input type="text" name="artist" id="artist-artist" class="form-control" @if( ! empty($artist->artist)) value="{{$artist->artist}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="is_group" class="col-sm-3 control-label">Is Group</label>
                <div class="col-sm-3">
                <input type="checkbox" name="is_group" id="song-is_group" @if(!empty($artist->is_group) && ($artist->is_group)) checked @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="country" class="col-sm-3 control-label">Country</label>
                <div class="col-sm-3">
                    <select class="form-control" name="country">
                        @foreach($countries as $country)
                            <option value="{{$country}}" @if( ! empty($artist->country) && ($artist->country == $country)) selected @endif>{{$country}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Add song Button -->
            <div class="form-group">
                @if( ! empty($artist->id))
                    <div class="col-sm-offset-3 col-sm-6">
                        <input type="hidden" name="id" id="artist-id" value="{{$artist->id}}">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                @else
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-primary">Add Artist</button>
                    </div>
                @endif
            </div>
        </form>
    </div>
@endsection
