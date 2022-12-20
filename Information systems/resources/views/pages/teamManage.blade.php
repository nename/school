@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{-- LOGO  --}}
                        <img src="../svg/{{ $team->logo }}" style="width:32px; height:32px; border-radius:10%">
                        {{-- Heading --}}
                        <b style="font-size: 20px"> {{ $team->name }} [{{ $team->abbreviation }}]</b>
                        {{-- Manage team  --}}
                    </div>

                    <div class="panel-body">
                        <h4> LOGO: </h4>
                        <form method="POST" action="{{url('/changeLogo')}}" style="display: inline">
                            {{ csrf_field() }}
                            <div class="form-group" style="display: inline">
                                <input type="hidden" name="id_team" value="{{$team->id_team}}">
                                <div class="col-md-4">
                                    <select id="select" class="form-control" name="select">
                                        <option value="none" selected hidden disabled> Select new logo </option>
                                        <option value="team_icon_1.svg">hippo</option>
                                        <option value="team_icon_2.svg">batman</option>
                                        <option value="team_icon_3.svg">flex</option>
                                        <option value="team_icon_4.svg">eye</option>
                                        <option value="team_icon_5.svg">arrows</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary">
                                        Set
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Member list --}}
                    <div class="panel-body">
                        

                        <h4> Members: </h4>
                        @foreach ($members as $member)
                            <ul style="font-size: 15px"> 
                                <a href="{{ url('/') }}/profile/{{$member->nickname}}" style="color:inherit; text-decoration: none; display: inline">
                                    {{$member->name}} &nbsp;"{{$member->nickname}}"  &nbsp; {{$member->surname}}
                                    @if($member->role_team == 'Owner')
                                        &nbsp; ({{$member->role_team}}) 
                                    @endif
                                </a>
                                &nbsp;
                                @if( $member->role_team == 'Owner' && count($members) == 1 && Auth::user()->role_user != 'Admin')
                                    <form method="post" action="{{url('/leaveTeam')}}" style="display: inline; float: right">
                                        {{ csrf_field() }}
                                        <input type='hidden' name='id_user' value={{$member->id_user}}>
                                        <input type='hidden' name='id_team' value={{$team->id_team}}>
                                        <button type="submit" style="border:none; background-color: Transparent; color: red">
                                            Leave
                                        </button>
                                    </form>
                                @elseif( $member->role_team != 'Owner' || Auth::user()->role_user == 'Admin')
                                    <form method="post" action="{{url('/kickMember')}}" style="display: inline; float: right">
                                        {{ csrf_field() }}
                                        <input type='hidden' name='id_user' value={{$member->id_user}}>
                                        <input type='hidden' name='id_team' value={{$team->id_team}}>
                                        <button type="submit" style="border:none; background-color: Transparent; color: red">
                                            Kick
                                        </button>
                                    </form>
                                    <form method="post" action="{{url('/promoteMember')}}" style="display: inline; float: right">
                                        {{ csrf_field() }}
                                        <input type='hidden' name='id_user' value={{$member->id_user}}>
                                        <input type='hidden' name='id_team' value={{$team->id_team}}>
                                        <button type="submit" style="border: none; background-color: Transparent; color: green">
                                            Promote
                                        </button>
                                    </form>
                                @endif
                            </ul>
                        @endforeach
                    </div>

                    {{-- Inviting new members --}}
                    <div class="panel-body">
                        <h4> Add members: </h4>
                        <form method="POST" action="{{url('/addMember')}}">
                                {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('nickname') ? ' has-error' : '' }}">
                                <label for="nickname" class="col-md-3 control-label">User Nickname:</label>
                                <div class="col-md-6">
                                    <input id="nickname" type="text" class="form-control" name="nickname" value="{{ old('nickname') }}" required>
                                    @if ($errors->has('nickname'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('nickname') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <input type='hidden' name='id_team' value={{$team->id_team}}>

                            <div class="form-group">
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        Add
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