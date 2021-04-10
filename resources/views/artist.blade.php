@extends('layouts.app')

@section('content')

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-6">{{$title}}</h2>

        @include('common.errors')

        <form action="/artist" enctype="multipart/form-data" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div>
                 <input type="hidden" name="redirects_to" value="{{ URL::previous() }}"/>
            </div>

            <div class="float-right">
               @if(isset($artist->photo))
                    <img src="{{ asset("storage/artists/$artist->photo") }}" class="img-thumbnail img-fluid" alt="artist photo">
                @endif
            </div>

            <div class="form-group">
                <label for="artist" class="col-sm-3 control-label">Artist</label>

                <div class="col-sm-6">
                    <input type="text" name="artist" id="artist-artist" class="form-control" @if( ! empty($artist->artist)) value="{{$artist->artist}}" @endif>
                </div>
            </div>

            @if(isset($albums))
                <div class="form-group">
                    <label for="album" class="col-sm-6 control-label">Albums</label>
                    <div class="col-sm-6">
                        <select class="form-control" name="album" id="album">
                            @foreach($albums as $album)
                                <option value="{{$album['album']}}">{{$album['album']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="country" class="col-sm-6 control-label">Country</label>
                <div class="col-sm-6">
                    <select class="form-control" name="country">

                        @foreach($countries as $country)
                            <option value="{{$country}}" @if( ! empty($artist->country) && ($artist->country == $country)) selected @endif>{{$country}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="group_members" class="col-sm-3 control-label">Group Members</label>

                <div class="col-sm-6">
                    <textarea name="group_members" id="artist-group_members" class="form-control">@if(!empty($artist->group_members)){{$artist->group_members}}@endif</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="location" class="col-sm-3 control-label">Location</label>

                <div class="col-sm-6">
                    <input name="location" id="location" class="form-control" @if(!empty($artist->location)) value="{{$artist->location}}"@endif/>
                </div>
            </div>

            <div class="form-group">
                <label for="founded" class="col-sm-3 control-label">Founded</label>

                <div class="col-sm-3">
                    <input type="text" name="founded" id="founded" class="form-control" value=@if (old('founded')) {{ old('founded') }} @elseif (!empty($artist->founded)) {{ $artist->founded }} @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="disbanded" class="col-sm-3 control-label">Disbanded</label>

                <div class="col-sm-3">
                    <input type="text" name="disbanded" id="disbanded" class="form-control" value=@if (old('disbanded')) {{ old('disbanded') }} @elseif (!empty($artist->disbanded)) {{ $artist->disbanded }} @endif>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="col-sm-3 control-label">Notes</label>

                <div class="col-sm-6">
                    <textarea name="notes" id="artist-notes" class="form-control">@if(!empty($artist->notes)){{$artist->notes}}@endif</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="photo" class="col-sm-6 control-label">Photo</label>

                <div class="col-sm-6">
                    <input type="file" name="photo" id="photo" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="is_group" class="col-sm-3 control-label">Is Group</label>
                <div class="col-sm-3">
                <input type="checkbox" name="is_group" id="song-is_group" @if(!empty($artist->is_group) && ($artist->is_group)) checked @endif>
                </div>
            </div>

            <div class="form-group">
                @if(!empty($artist->id))
                    <div class="col-sm-offset-3 col-sm-6">
                        <input type="hidden" name="id" id="artist-id" value="{{$artist->id}}">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                    </div>
                @else
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-primary">Add Artist</button>
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if(isset($songs) && count($songs) > 0)
    <div class="mysound-information-div">
        <h5 class="col-sm-3">Songs</h5>
        <ol id="artist-songs">
            @foreach($songs as $song)
                <li><a href="{{ url('/song') }}/{{ $song->id }}">{{ $song->title }}</a></li>
            @endforeach
        </ol>
    </div>
    @endif

@endsection

@section('scripts')
    <script src="{{ asset('js/artist.js') }}"></script>
@endsection