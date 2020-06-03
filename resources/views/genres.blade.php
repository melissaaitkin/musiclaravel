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
            <h5>Genres</h5>
        </div>

        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-striped mysounds-table">

                    <thead>
                        <th>Genre</th>
                        <th>&nbsp;</th>
                    </thead>

                    <tbody>
                        @foreach ($genres as $genre)
                            <tr class="mysounds-tr">
                                <td class="table-text">
                                    <div name="genre-title">{{ $genre->genre }}</div>
                                </td>                                                             
                                <td>
                                    {{ csrf_field() }}
                                    <a href="#" name="play">play</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

@endsection

@section('scripts')
    <script src="{{ asset('js/genre.js') }}"></script>
@endsection