@extends('layouts.app')

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/playlist.js') }}"></script>

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
                        @foreach ($playlists as $title => $playlist)
                            <tr class="tr-playlist">
                                <td class="table-text">
                                    <div id="playlist-title">{{ $title }}</div>
                                    <input type="hidden" name="playlist" id="playlist" value="{{ $playlist }}">
                                </td>                                                             
                                <td>
                                    {{ csrf_field() }}
                                    <a href="#" id="play">play</a>
                                </td>
                                <td>
                                    <form action="/playlist/{{ $title }}" method="POST">
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

        <div>
            @if (Route::has('login'))
                <div class="col-sm-3">
                    @auth
                        <a href="{{ url('/playlist') }}">Add</a>
                    @endauth
                </div>
            @endif
        </div>


@endsection

@push('scripts')
<script>
// your code
</script>
@endpush