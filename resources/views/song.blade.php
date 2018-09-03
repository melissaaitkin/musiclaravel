@extends('layouts.app')

@section('content')

    <!-- Bootstrap Boilerplate... -->

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-3">Add New Song</h2>

        @include('common.errors')


        <!-- New song Form -->
        <form action="/song" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <!-- song Name -->
            <div class="form-group">
                <label for="song" class="col-sm-3 control-label">Song</label>

                <div class="col-sm-6">
                    <input type="text" name="title" id="song-title" class="form-control" @if( ! empty($song->title)) value="{{$song->title}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="album" class="col-sm-3 control-label">Album</label>

                <div class="col-sm-6">
                    <input type="text" name="album" id="song-album" class="form-control" @if( ! empty($song->album)) value="{{$song->album}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="year" class="col-sm-3 control-label">Year</label>

                <div class="col-sm-3">
                    <input type="text" name="year" id="song-year" class="form-control" @if( ! empty($song->year)) value="{{$song->year}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="genre" class="col-sm-3 control-label">Genre</label>

                <div class="col-sm-3">
                    <input type="text" name="genre" id="song-genre" class="form-control" @if( ! empty($song->genre)) value="{{$song->genre}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="track_no" class="col-sm-3 control-label">Track No</label>

                <div class="col-sm-3">
                    <input type="text" name="track_no" id="song-track_no" class="form-control" @if( ! empty($song->track_no)) value="{{$song->track_no}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="artist" class="col-sm-3 control-label">Artist</label>
                <div class="col-sm-3">
                    <select class="form-control" name="artist_id">
                        @foreach($artists as $artist)
                            <option value="{{$artist->id}}">{{$artist->artist}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="file_type" class="col-sm-3 control-label">File Type</label>

                <div class="col-sm-3">
                    <select class="form-control" name="song-file_type">
                        @foreach($file_types as $file_type)
                            <option value="{{$file_type}}">{{$file_type}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Add song Button -->
            <div class="form-group">
                @if( ! empty($song->id))
                    <div class="col-sm-offset-3 col-sm-6">
                        <input type="hidden" name="id" id="song-id" value="{{$song->id}}">
                        <button type="submit" class="btn btn-primary">Edit Song</button>
                    </div>
                @else
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-primary">Add Song</button>
                    </div>
                @endif
            </div>
        </form>

    </div>
@endsection
