@extends('layouts.app')

@section('content')

    <div class="panel-body mysound-submit-form-div">

        <h2 class="col-sm-12">{{$song->title}}</h2>

        @include('common.errors')

        <form action="/lyrics" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group">
                <div>
                    <textarea name="lyrics" id="lyrics" class="form-control" cols="30" rows="60">{{$song->lyrics}}</textarea>
                </div>
            </div>

            <div class="form-group">
                @if( ! empty($song->id))
                    <div class="col-sm-offset-3 col-sm-6">
                        <input type="hidden" name="id" id="id" value="{{ $song->id }}">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                    </div>
                @endif
            </div>
        </form>

    </div>
@endsection