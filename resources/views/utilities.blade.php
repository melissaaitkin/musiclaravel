@extends('layouts.app')

@section('content')

    <div class="panel-body">

        <h2 class="col-sm-3">Utilities</h2>

        @include('common.errors')

        @if(isset($msg))
            <div>{{ $msg }}</div>
        @endif

        <form action="/load" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <h4 class="col-sm-3">Load Songs</h4>

            <div class="form-group">
                <label for="directory" class="col-sm-3 control-label">Directory</label>

                <div class="col-sm-6">
                    <input type="text" name="directory" id="directory" class="form-control">
                </div>          
            </div>


            <div class="form-group">
                <label for="entire_library" class="col-sm-4 control-label">Entire Media Library</label>

                <div class="col-sm-1">
                    <input type="checkbox" name="entire_library" id="entire_library" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary">Load</button>
                </div>              
            </div>            

        </form>

    </div>
@endsection
