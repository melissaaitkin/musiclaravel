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

            <div class="ml-3">

                <div class="form-group">


                    <div class="pb-1">
                        <label for="myquery" class="col-sm-3 control-label">Query</label>
                        <input type="text" name="myquery" id="myquery" class="form-control" @if (!empty($myquery)) value="{{ $myquery }}" @endif>
                    </div>
                    <div class="row pb-1">
                        <div class="col-sm-1"><label for="show_cols" class="control-label">Show columns</label></div>
                        <div class="col-sm-1"><input type="checkbox" name="show_cols" id="show_cols" class="form-control" @if ($show_cols) checked="{{ $show_cols }}" @endif></div>
                    </div>
                    <div>
                        <textarea name="results" id="results" class="form-control" rows="20" cols="50">@if (!empty($results)){{ $results }}@endif</textarea>
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
