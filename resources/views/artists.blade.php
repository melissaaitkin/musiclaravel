@extends('layouts.app')

@section('content')

    <div class="panel-body">

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                Please fix the following errors
            </div>
        @endif

        <div class="col-sm-3">
            <h3>Current Artists</h3>
        </div>

    @if (count($artists) > 0)
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-striped mysounds-table">

                    <!-- Table Headings -->
                    <thead>
                        <th>Artist</th>
                        <th>Country</th>
                        <th>&nbsp;</th>
                    </thead>

                    <!-- Table Body -->
                    <tbody>
                        @foreach ($artists as $artist)
                            <tr>
                                <td class="table-text">
                                    <div>{{ $artist->artist }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $artist->country }}</div>
                                </td>             
                                <td>
                                    <form action="/artist/{{ $artist->id }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button>Delete Artist</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $artists->links() }} 
            </div>
        </div>
    @endif


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