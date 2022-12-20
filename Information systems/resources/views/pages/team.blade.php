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
                        <b style="font-size: 20px;"> {{ $team->name }} [{{ $team->abbreviation }}]</b>
                        {{-- Manage team  --}}
                        
                        @if ( Auth::check() && (Auth::user()->id_user == $owner_id || Auth::user()->role_user == 'Admin') )    
                            <a href="{{ url('/manageTeam') }}/{{ $team->id_team }}" style="float: right;font-size: 15px">
                                <span style="font-size: 20px"> <b> Manage </b> </span> 
                            </a>
                        @elseif( Auth::check() && Auth::user()->id_team == $team->id_team )
                            <form method="post" action="{{url('/leaveTeam')}}" style="display: inline; float: right">
                                {{ csrf_field() }}
                                <input type='hidden' name='id_user' value={{Auth::user()->id_user}}>
                                <input type='hidden' name='id_team' value={{$team->id_team}}>
                                <button type="submit" style="border:none; background-color: Transparent; color: red">
                                    Leave
                                </button>
                            </form>
                        @endif
                    </div>

                        
                    {{-- Member list --}}
                    <div class="panel-body">
                        <h4> Members: </h4>
                        @foreach ($members as $member)
                            <ul style="font-size: 15px"> 
                                <a href="{{ url('/') }}/profile/{{$member->nickname}}" style="color:inherit; text-decoration:none">
                                    {{$member->name}} &nbsp;"{{$member->nickname}}"  &nbsp; {{$member->surname}}
                                    @if($member->role_team == 'Owner')
                                        &nbsp; ({{$member->role_team}}) 
                                    @endif
                                </a>
                            </ul>
                        @endforeach
                    </div>

                    {{-- Tournament list --}}
                    <div class="panel-body">
                        <h5> Tournaments: </h5>
                        @foreach ($tournaments as $tournament)
                            <ul style="font-size: 15px"> 
                                <a href="{{ url('/') }}/tournament/{{$tournament->id_tournament}}" style="color:inherit; text-decoration:none">
                                    {{$tournament->name}} &nbsp;
                                </a>
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection