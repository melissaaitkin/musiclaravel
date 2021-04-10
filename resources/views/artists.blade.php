@extends('layouts.app')

@section('content')

    <div class="panel-body">

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                Please fix the following errors
            </div>
        @endif

        @if (isset($message))
            <p>{{ $message }}</p>
        @endif

        <div class="col-sm-3">
            <h5>Current Artists</h5>
        </div>

        <div class="panel panel-default">

            <div class="panel-body">
                <form action="/artists/search" method="POST" role="search">
                    {{ csrf_field() }}
                    <div class="input-group col-sm-6 pb-2">
                        <input type="text" class="form-control" name="q"
                            placeholder="Search artists" @if (!empty($q)) value="{{ $q }}" @endif> <span class="input-group-btn">
                            <button type="submit" class="btn btn-default">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </form>
            </div> 

            <div class="panel-body">
                <table class="table table-striped mysounds-table">

                    <thead>
                        <th>Artist</th>
                        <th>Country</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </thead>

                    <tbody>
                        @foreach ($artists as $artist)
                            <tr class="mysounds-tr">
                                <td class="table-text">
                                    <div name="artist_name">{{ $artist->artist }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $artist->country }}</div>
                                </td>
                                <td>
                                    {{ csrf_field() }}
                                    <a href="/artist/{{ $artist->id }}">edit</a>
                                </td>
                                <td>
                                   <input type="button" class="btn btn-link btn-mysounds" name="play_songs" id="play-songs-{{ $artist->id }}" value="play songs">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $artists->links() }} 
            </div>
        </div>

        <div>
            @if (Route::has('login'))
                <div class="col-sm-3">
                    @auth
                        <a href="{{ url('/artist') }}">Add</a>
                    @endauth
                </div>
            @endif
        </div>

@endsection

@section('scripts')
    <script src="{{ asset('js/artist.js') }}"></script>
@endsection
