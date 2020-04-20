@extends('layouts.app')

@section('content')

    <!-- Bootstrap Boilerplate... -->

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-12">{{$title}}</h2>

        @include('common.errors')


        <!-- New song Form -->
        <form action="/song" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            @if($song_exists)
            <div class="form-group">
                <div class="col-sm-6">
                    <audio controls>
                        <source src="{{ route('song.play', array('slug' => $song->id)) }}" type="audio/mpeg">
                    </audio>
                </div>
            </div>
            @endif

            <!-- song Name -->
            <div class="form-group">
                <label for="title" class="col-sm-3 control-label">Title</label>

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
                            <option value="{{$artist->id}}" @if( ! empty($song->artist_id) && ($song->artist_id == $artist->id)) selected @endif>{{$artist->artist}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="file_type" class="col-sm-3 control-label">File Type</label>

                <div class="col-sm-3">
                    <select class="form-control" name="file_type">
                        @foreach($file_types as $file_type)
                            <option value="{{$file_type}}" @if( ! empty($song->file_type) && ($song->file_type == $file_type)) selected @endif>{{$file_type}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="playtime" class="col-sm-3 control-label">Playtime</label>

                <div class="col-sm-3">
                    <input type="text" name="playtime" id="song-playtime" class="form-control" @if( ! empty($song->playtime)) value="{{$song->playtime}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="filesize" class="col-sm-3 control-label">Filesize</label>

                <div class="col-sm-3">
                    <input type="text" name="filesize" id="song-filesize" class="form-control" @if( ! empty($song->filesize)) value="{{$song->filesize}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="composer" class="col-sm-3 control-label">Composer</label>

                <div class="col-sm-3">
                    <input type="text" name="composer" id="song-composer" class="form-control" @if( ! empty($song->composer)) value="{{$song->composer}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="location" class="col-sm-3 control-label">Location</label>

                <div class="col-sm-6">
                    <input type="text" name="location" id="song-location" class="form-control" @if( ! empty($song->location)) value="{{$song->location}}" @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="col-sm-3 control-label">Notes</label>

                <div class="col-sm-6">
                    <textarea name="notes" id="song-notes" class="form-control">@if(!empty($song->notes)){{$song->notes}}@endif</textarea>
                </div>
            </div>

            <div class="form-group">
                @if( ! empty($song->id))
                    <div class="col-sm-offset-3 col-sm-6">
                        <input type="hidden" name="id" id="song-id" value="{{$song->id}}">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
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
