@extends('layouts.app')

@section('content')

    <div class="panel-body">

        <h2 class="col-sm-3">Settings</h2>

        @include('common.errors')

        @if(isset($msg))
            <div>{{ $msg }}</div>
        @endif

        <form action="/settings" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="border border-dark pt-3 ml-3 mb-4">

                <h6 class="col-sm-6">Settings</h6>

                <div class="form-group">
                    <label for="directory" class="col-sm-3 control-label">Media Directory</label>

                    <div class="col-sm-6">
                        <input type="text" name="media_directory" id="media_directory" class="form-control" @if (!empty($media_directory)) value="{{$media_directory}}" @endif>
                    </div>
                </div>

            <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                    </div>
                </div>

            </div>


        </form>

    </div>
@endsection
