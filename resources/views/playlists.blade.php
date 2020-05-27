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
            <h5>Playlists</h5>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-striped mysounds-table">

                    <thead>
                        <th>Title</th>
                        <th>&nbsp;</th>
                    </thead>

                    <tbody>
                        @foreach ($playlists as $playlist)
                            <tr class="mysounds-tr">
                                <td class="table-text">
                                    <div name="playlist-title">{{ $playlist }}</div>
                                </td>                                                             
                                <td>
                                    {{ csrf_field() }}
                                    <a href="#" name="play">play</a>
                                </td>
                                <td>
                                    <form action="/playlists/{{ $playlist }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                         <a href="javascript:;" onclick="parentNode.submit();">delete</a>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

@endsection

@section('scripts')
    <script src="{{ asset('js/playlist.js') }}"></script>
@endsection