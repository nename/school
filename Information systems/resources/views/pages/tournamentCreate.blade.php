@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 style="text-align: center"> Create Tournament </h4>
                    </div>
    
                    <div class="panel-body">
                        
                        <form class="form-horizontal" method="post" action="{{url('/createTournament')}}" >
                            {{ csrf_field() }}
    
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Tournament Name</label>
    
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>
    
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('number_of_players') ? ' has-error' : '' }}">
                                <label for="number_of_players" class="col-md-4 control-label">Number of Players</label>
    
                                <div class="col-md-6">
                                    <input id="number_of_players" type="number" min="0" class="form-control" name="number_of_players" value="{{ old('number_of_players') }}" required>
    
                                    @if ($errors->has('number_of_players'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('number_of_players') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('max_number_of_teams') ? ' has-error' : '' }}">
                                <label for="number_of_players" class="col-md-4 control-label">Max number of teams</label>

                                <div class="col-md-6">
                                            <select id="max_number_of_teams" class="form-control" name="max_number_of_teams">
                                                <option value="4">4</option>
                                                <option value="8" selected>8</option>
                                                <option value="16">16</option>
                                                <option value="32">32</option>
                                                <option value="64">64</option>
                                            </select>
                                            @if ($errors->has('max_number_of_teams'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('max_number_of_teams') }}</strong>
                                                </span>
                                            @endif
                                    @if ($errors->has('max_number_of_teams'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('max_number_of_teams') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="form-group{{ $errors->has('registration_fee') ? ' has-error' : '' }}">
                                <label for="registration_fee" class="col-md-4 control-label">Registration Fee</label>
    
                                <div class="col-md-6">
                                    <input id="registration_fee" type="text" min="0" class="form-control" name="registration_fee" value="{{ old('registration_fee') }}" required>
    
                                    @if ($errors->has('registration_fee'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('registration_fee') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('reward') ? ' has-error' : '' }}">
                                <label for="reward" class="col-md-4 control-label">Reward</label>
    
                                <div class="col-md-6">
                                    <input id="reward" type="text" min="0" class="form-control" name="reward" value="{{ old('reward') }}" required>
    
                                    @if ($errors->has('reward'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('reward') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('start_date') ? ' has-error' : '' }}">
                                <label for="start_date" class="col-md-4 control-label">start date</label>
    
                                <div class="col-md-6">
                                    <input id="start_date" placeholder="YYYY-MM-DD hh:mm" type="datetime-local" max="9999-12-31T23:59"  min=
                                    <?php
                                        echo date("Y-m-d\TH:i");
                                    ?>  class="form-control"  name="start_date" value="{{ old('start_date') }}" required>
    
                                    @if ($errors->has('start_date'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('start_date') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('end_date') ? ' has-error' : '' }}">
                                <label for="end_date" class="col-md-4 control-label">End</label>
    
                                <div class="col-md-6">
                                    <input id="end_date" placeholder="YYYY-MM-DD hh:mm" type="datetime-local" max="9999-12-31T23:59" min=
                                    <?php
                                        echo date("Y-m-d\TH:i");
                                    ?> class="form-control" name="end_date" value="{{ old('end_date') }}" required>
    
                                    @if ($errors->has('end_date'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('end_date') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                <label for="description" class="col-md-4 control-label">Description</label>
    
                                <div class="col-md-6">
                                    <textarea id="description" type="text" rows="5" class="form-control" name="description" value="{{ old('description') }}" required></textarea>
                                    @if ($errors->has('description'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('description') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <input type="hidden" name="id_user" value="{{Auth::user()->id_user}}">

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