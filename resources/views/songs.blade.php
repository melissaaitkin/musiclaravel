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
            <h3>Current Songs</h3>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">
                <form action="/songs/search" method="POST" role="search">
                    {{ csrf_field() }}
                    <div class="input-group col-sm-6">
                        <input type="text" class="form-control" name="q"
                            placeholder="Search songs"> <span class="input-group-btn">
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
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Album</th>
                        <th>Year</th>
                        <th>Genre</th>
                        <th>Track No</th>
                        <th>Playtime</th>
                        <th>Composer</th>
                        <th>&nbsp;</th>
                    </thead>

                    <tbody>
                        @foreach ($songs as $song)
                            <tr>
                                <td class="table-text">
                                    <div>{{ $song->title }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->artist ?? '' }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->album }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->year }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->genre }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->track_no }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->playtime }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->composer }}</div>
                                </td>                                                                
                                <td>
                                    {{ csrf_field() }}
                                    <a href="/song/{{ $song->id }}">edit</a>
                                </td>
                                <td>
                                    <form action="/song/{{ $song->id }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                         <a href="javascript:;" onclick="parentNode.submit();">delete</a>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $songs->links() }}
            </div>
        </div>

        <div>
            @if (Route::has('login'))
                <div class="col-sm-3">
                    @auth
                        <a href="{{ url('/song') }}">Add</a>
                    @endauth
                </div>
            @endif
        </div>


@endsection