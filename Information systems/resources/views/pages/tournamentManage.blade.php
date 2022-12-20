@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                            Manage tournament: 
                            <a href="{{ url('/tournament') }}/{{ $tournament->id_tournament }}">
                                {{$tournament->name}}
                            </a>
                    </div>
                    <div class="panel-body">
                    {{-- Change Tournament properties --}}
                    <div class="col-md-10">
                        <b>Properties:</b>
                        <form class="form-horizontal" method="post" action="{{url('/changeTournamentProperties')}}" >
                            {{ csrf_field() }}
                            <input type="hidden" name="id_tournament" value="{{$tournament->id_tournament}}">
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Tournament Name</label>
    
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{$tournament->name}}" required autofocus>
    
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
                                <input id="number_of_players" type="number" min="0" class="form-control" name="number_of_players" value="{{$tournament->number_of_players}}" required>
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
                                            <option value="{{$tournament->max_number_of_teams}}" selected hidden> {{$tournament->max_number_of_teams}} </option>
                                            @if(count($teams) <= 4)
                                                <option value="4">4</option>
                                            @endif
                                            @if(count($teams) <= 8)
                                                <option value="8">8</option>
                                            @endif
                                            @if(count($teams) <= 16)
                                                <option value="16">16</option>
                                            @endif
                                            @if(count($teams) <= 32)
                                                <option value="32">32</option>
                                            @endif        
                                            @if(count($teams) <= 64)
                                                <option value="64">64</option>
                                            @endif                                
                                        </select>
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
                                    <input id="registration_fee" type="text" min="0" class="form-control" name="registration_fee" value="{{$tournament->registration_fee}}" required>
    
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
                                    <input id="reward" type="text" min="0" class="form-control" name="reward" value="{{$tournament->reward}}" required>
    
                                    @if ($errors->has('reward'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('reward') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
    
                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                <label for="description" class="col-md-4 control-label">Description</label>
    
                                <div class="col-md-6">
                                    <textarea id="description" type="text" rows="5" class="form-control" name="description" required>{{$tournament->description}}</textarea>
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
                                        Change
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- Sponsors --}}
                    <div class="col-md-10">
                        <b style="display: inline; float: left">Sponsors:</b>
                        <div class="col-md-10">
                            <table class="col-md-6">
                                @foreach ($sponsors as $sponsor)
                                    <tr>
                                        <td> {{$sponsor->name}} </td> 
                                        <td> 
                                            <form method="post" action="{{url('/removeSponsor')}}" style="display: inline; float: right">
                                                {{ csrf_field() }}
                                                <input type='hidden' name='id_tournament' value={{$tournament->id_tournament}}>
                                                <input type='hidden' name='id_sponsor' value={{$sponsor->id_sponsor}}>
                                                <button type="submit" style="border:none; background-color: Transparent; color: red">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            
                        </div> 
                        <form class="form-horizontal" style="display: inline;float: left" method="post" action="{{url('/addSponsor')}}" >
                            {{ csrf_field() }}
                            <input type="hidden" name="id_tournament" style="display: inline;float: left" value="{{$tournament->id_tournament}}">
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">New sponsor:</label>
    
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" required autofocus>
    
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif

                                </div>
                                <div style="display: inline;float: left">
                                        <button type="submit" class="btn btn-primary">
                                                Add
                                        </button>
                                </div>


                            </div>

                            


                        </form>  
                    </div>

                    {{-- Kicking referees --}}
                    <div class="col-md-10">
                        <b style="display: inline; float: left">Referees:</b>
                        <div class="col-md-10">
                            <table class="col-md-6">
                                @foreach ($referees as $referee)
                                    <tr>
                                        <td><a href="{{ url('/profile/'.$referee->nickname) }}"> {{$referee->name}} "{{$referee->nickname}}" {{$referee->surname}} </a> </td> 
                                        <td> 
                                            @if(is_null($tournament->registration_ended)) 
                                                <form method="post" action="{{url('/kickReferee')}}" style="display: inline; float: right">
                                                    {{ csrf_field() }}
                                                    <input type='hidden' name='id_tournament' value={{$tournament->id_tournament}}>
                                                    <input type='hidden' name='id_user' value={{$referee->id_user}}>
                                                    <button type="submit" style="border:none; background-color: Transparent; color: red">
                                                        Kick
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>   
                    </div>
                    
                    {{-- Kicking teams --}}
                    <div class="col-md-10">
                        <b>Registered teams:</b>
                        <div class="col-md-10">
                            <table class="col-md-6">
                                @foreach ($teams as $team)
                                    <tr>
                                        <td><a href="{{ url('/team/'.$team->id_team) }}"> {{$team->name}} </a> </td> 
                                        <td> 
                                            @if(is_null($tournament->registration_ended)) 
                                                <form method="post" action="{{url('/kickTeam')}}" style="display: inline; float: right">
                                                    {{ csrf_field() }}
                                                    <input type='hidden' name='id_tournament' value={{$tournament->id_tournament}}>
                                                    <input type='hidden' name='id_team' value={{$team->id_team}}>
                                                    <button type="submit" style="border:none; background-color: Transparent; color: red">
                                                        Kick
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>   
                    </div>
                    <div class="col-md-10">
                        @if(count($teams) < 3)
                            Need at least 3 teams to generate matches.
                        @elseif(is_null($tournament->registration_ended))
                            <form method="post" action="{{url('/generateMatches')}}">
                                {{ csrf_field() }}
                                <input type="hidden" name='id_tournament' value="{{$tournament->id_tournament}}">
                                <button type="submit" class="btn btn-primary">
                                    Generate Matches
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection