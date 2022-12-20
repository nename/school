@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        &#x1F50E; &nbsp; &nbsp;  &nbsp; {{$searched_string}}
                    </div>
                        
                    <div class="panel-body">
                        @if(count($users) > 0)
                            <b> Users: </b>
                            @foreach ($users as $user)
                            <ul style="font-size: 15px"> 
                                <a href="{{ url('/') }}/profile/{{$user->nickname}}" style="color:inherit; text-decoration:none">
                                    {{$user->name}} &nbsp;"{{$user->nickname}}"  &nbsp; {{$user->surname}} &nbsp; &nbsp; &nbsp; {{$user->email}}
                                </a>
                                @if( Auth::check() && Auth::user()->role_user == 'Admin' )
                                    <form method="post" action="{{url('/deleteUser')}}" style="display: inline; float: right">
                                        {{ csrf_field() }}
                                        <input type='hidden' name='id_user' value={{$user->id_user}}>
                                        <button type="submit" style="border:none; background-color: Transparent; color: red">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                                
                            </ul>
                            @endforeach
                        @endif

                        @if(count($teams) > 0)
                            <b> Teams: </b>
                            @foreach ($teams as $team)
                            <ul style="font-size: 15px"> 
                                <a href="{{ url('/') }}/team/{{$team->id_team}}" style="color:inherit; text-decoration:none">
                                    {{$team->name}} &nbsp;"{{$team->abbreviation}}"
                                </a>
                                @if( Auth::check() && Auth::user()->role_user == 'Admin' )
                                    <form method="post" action="{{url('/deleteTeam')}}" style="display: inline; float: right">
                                        {{ csrf_field() }}
                                        <input type='hidden' name='id_team' value={{$team->id_team}}>
                                        <button type="submit" style="border:none; background-color: Transparent; color: red">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </ul>
                            @endforeach
                        @endif

                        @if(count($tournaments) > 0)
                            <b> Tournaments: </b>
                            @foreach ($tournaments as $tournament)
                            <ul style="font-size: 15px"> 
                                <a href="{{ url('/') }}/tournament/{{$tournament->id_tournament}}" style="color:inherit; text-decoration:none">
                                    {{$tournament->name}}
                                </a>
                                @if( Auth::check() && Auth::user()->role_user == 'Admin' )
                                    <form method="post" action="{{url('/deleteTournament')}}" style="display: inline; float: right">
                                        {{ csrf_field() }}
                                        <input type='hidden' name='id_tournament' value={{$tournament->id_tournament}}>
                                        <button type="submit" style="border:none; background-color: Transparent; color: red">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </ul>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
