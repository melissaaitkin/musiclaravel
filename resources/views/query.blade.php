@extends('layouts.app')

@section('content')

    <div class="panel-body">

        <h2 class="col-sm-3">Queries</h2>

        @include('common.errors')

        @if (isset($msg))
            <div>{{ $msg }}</div>
        @endif

        <form action="/query" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="border border-dark pt-3 ml-3 mb-4">

                <div class="form-group">
                    <label for="directory" class="col-sm-3 control-label">Query</label>

                    <div class="pb-1">
                        <input type="text" name="myquery" id="myquery" class="form-control" @if (!empty($myquery)) value="{{ $myquery }}" @endif>
                    </div>
                    <div>
                        <textarea name="results" id="results" class="form-control" rows="25" cols="50">@if (!empty($results)){{ $results }}@endif</textarea>
                    </div>
                </div>

            <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-primary">Run</button>
                    </div>
                </div>

            </div>


        </form>

    </div>
@endsection
