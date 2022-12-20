@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3> Create Team </h3>
                    </div>
    
                    <div class="panel-body">
                        <form class="form-horizontal" method="post" action="{{url('/createTeam')}}" >
                            {{ csrf_field() }}
    
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Team name</label>
    
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>
    
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('shortcut') ? ' has-error' : '' }}">
                                <label for="shortcut" class="col-md-4 control-label">Shortcut</label>
    
                                <div class="col-md-6">
                                    <input id="shortcut" type="text" class="form-control" name="shortcut" value="{{ old('shortcut') }}" required>
    
                                    @if ($errors->has('shortcut'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('shortcut') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <input type="hidden" name="id_user" value="{{Auth::user()->id_user}}">

                            <div class="form-group">
                                <label for="select" class="col-md-4 control-label">Logo</label>

                                <div class="col-md-6">
                                    <select id="select" class="form-control" name="select">
                                        <option value="team_icon_1.svg">hippo</option>
                                        <option value="team_icon_2.svg">batman</option>
                                        <option value="team_icon_3.svg">flex</option>
                                        <option value="team_icon_4.svg">eye</option>
                                        <option value="team_icon_5.svg">arrows</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Create
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection