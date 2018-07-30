@extends('layouts.app')

@section('content')

    <div class="panel-body">
        <!-- Display Validation Errors -->

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                Please fix the following errors
            </div>
        @endif


    <!-- Current songs -->
    @if (count($songs) > 0)
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3>Current Songs</h3>
            </div>

            <div class="panel-body">
                <table class="table table-striped song-table">

                    <!-- Table Headings -->
                    <thead>
                        <th>Title</th>
                        <th>Album</th>
                        <th>Year</th>
                        <th>&nbsp;</th>
                    </thead>

                    <!-- Table Body -->
                    <tbody>
                        @foreach ($songs as $song)
                            <tr>
                                <td class="table-text">
                                    <div>{{ $song->title }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->album }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $song->year }}</div>
                                </td>                 
                                <td>
                                    <!-- TODO: Delete Button -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif


        <div>
            @if (Route::has('login'))
                <div>
                    @auth
                        <a href="{{ url('/song') }}">Add</a>
                    @endauth
                </div>
            @endif
        </div>


@endsection