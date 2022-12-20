@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Edit: {{$user->name}} "{{$user->nickname}}" {{$user->surname}}
                        <div class="panel-body">
                        <form class="form-horizontal" method="POST" action="{{url('/editUser')}}">
                            {{ csrf_field() }}
                            <input id="name" type=hidden name="id_user" value="{{$user->id_user}}">
                
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">First name</label>
                
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{$user->name}}" required autofocus>
                
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                
                            <div class="form-group{{ $errors->has('surname') ? ' has-error' : '' }}">
                                <label for="surname" class="col-md-4 control-label">Last name</label>
                
                                <div class="col-md-6">
                                    <input id="surname" type="text" class="form-control" name="surname" value="{{$user->surname}}" required>
                
                                    @if ($errors->has('surname'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('surname') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                
                            <div class="form-group{{ $errors->has('nickname') ? ' has-error' : '' }}">
                                <label for="nickname" class="col-md-4 control-label">Nickname</label>
                
                                <div class="col-md-6">
                                    <input id="nickname" type="text" class="form-control" name="nickname" value="{{$user->nickname}}" required>
                
                                    @if ($errors->has('nickname'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('nickname') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                
                            <div class="form-group{{ $errors->has('birthdate') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">Birthdate</label>
                
                                <div class="col-md-6">
                                    <input id="birthdate" type="date" max="9999-12-31" class="form-control" name="birthdate" value="{{$user->birthdate}}" required>
                
                                    @if ($errors->has('birthdate'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('birthdate') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>
@endsection
