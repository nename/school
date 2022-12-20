@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <b>Match</b>
                    </div>
                    @php 
                        $empty_str = "" 
                    @endphp
                    @if (Auth::guest())
                        <div class="panel-body">
                            <p> <b>Tournament name:</b> {{ (isset($data['tournament']->name) ? $data['tournament']->name : $empty_str) }} </p>
                            <p> <b>Round:</b> {{ (isset($data['match']->round_number) ? $data['match']->round_number : $empty_str) }} </p>
                            <p> 
                                @if (isset($data['team1']->name))
                                <a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$data['team1']->id_team) }}">{{$data['team1']->name}}</a>
                                @endif
                                <b>VS</b>
                                @if (isset($data['team2']->name))
                                <a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$data['team2']->id_team) }}">{{$data['team2']->name}}</a>
                                @endif
                            </p>
                            <p> <b>Score: </b> 
                            @if (isset($data['teamsMatches1']->score) || isset($data['teamsMatches2']->score))
                            {{ (isset($data['teamsMatches1']->score) ? $data['teamsMatches1']->score : $empty_str) }}
                            <b>:</b> 
                            {{ (isset($data['teamsMatches2']->score) ? $data['teamsMatches2']->score : $empty_str) }}</p>
                            @endif
                            <div class="col-md-12"></div>
                            <button type="button" onclick="location.href='{{ url('/tournament/'.$data['tournament']->id_tournament) }}'" class="btn btn-primary">
                                Back
                            </button>
                        </div> 
                    @else
                        @php
                            $canEdit = false;
                            if($data['usersTournaments'] != NULL){
                                if(($data['usersTournaments']->role_tournament == 'referee') || ($data['usersTournaments']->role_tournament == 'organizer')){
                                    $canEdit = true;                        
                                }
                            }
                            if(Auth::user()->role_user == 'Admin'){
                                $canEdit = true;
                            }
                            
                        @endphp
                        @if ($canEdit)
                            <div class="panel-body">
                                <form class="form-horizontal" method="POST" action="{{ url('match/save/'.$data['match']->id_match) }}">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <p class="col-md-6"><b>Tournament name:</b> {{$data['tournament']->name}} </p>
                                            </div>
                                        </div>
            
                                        <div class="row">
                                            <div class="col-md-10">
                                                <p class="col-md-6"> <b>Round:</b> {{ (isset($data['match']->round_number) ? $data['match']->round_number : $empty_str) }} </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-10">
                                                <p class="col-md-6"> 
                                                    @if (isset($data['team1']->name))
                                                    <a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$data['team1']->id_team) }}">{{$data['team1']->name}}</a>
                                                    @endif
                                                    <b>VS</b>
                                                    @if (isset($data['team2']->name))
                                                    <a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$data['team2']->id_team) }}">{{$data['team2']->name}}</a>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                @if (!$data['editEnabled'])
                                                    <p class="col-md-6"> 
                                                        <b>Score: </b>
                                                        @if (isset($data['teamsMatches1']->score) || isset($data['teamsMatches2']->score))
                                                        {{ (isset($data['teamsMatches1']->score) ? $data['teamsMatches1']->score : $empty_str) }}
                                                        <b>:</b> 
                                                        {{ (isset($data['teamsMatches2']->score) ? $data['teamsMatches2']->score : $empty_str) }}
                                                        @endif
                                                    </p>
                                                @else
                                                    <label for="score1" class="col-md-1 control-label"><b>Score: </b></label>
                                                    <div class="col-md-2">
                                                        <input id="score1" type="number" min="0" class="form-control" name="score1" value="{{ (isset($data['teamsMatches1']->score) ? $data['teamsMatches1']->score : $empty_str) }}" required autofocus>
                                                    </div>
                                                    <label for="score2" class="col-md-1 control-label"><b>:</b></label>
                                                    <div class="col-md-2">
                                                        <input id="score2" type="number" min="0" class="form-control" name="score2" value="{{ (isset($data['teamsMatches2']->score) ? $data['teamsMatches2']->score : $empty_str) }}" required autofocus>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-8 ">
                                            @if ($data['editEnabled'])
                                            <button type="submit" class="btn btn-primary">
                                                Save
                                            </button>
                                            @endif
                                            <button type="button" onclick="location.href='{{ url('/tournament/'.$data['tournament']->id_tournament) }}'" class="btn btn-primary">
                                                Back
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="panel-body">
                                <p> <b>Tournament name:</b> {{ (isset($data['tournament']->name) ? $data['tournament']->name : $empty_str) }} </p>
                                <p> 
                                    @if (isset($data['team1']->name))
                                    <a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$data['team1']->id_team) }}">{{$data['team1']->name}}</a>
                                    @endif
                                    <b>VS</b>
                                    @if (isset($data['team2']->name))
                                    <a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$data['team2']->id_team) }}">{{$data['team2']->name}}</a>
                                    @endif
                                </p>
                                <p> 
                                    <b>Score: </b>
                                    @if (isset($data['teamsMatches1']->score) || isset($data['teamsMatches2']->score))
                                    {{ (isset($data['teamsMatches1']->score) ? $data['teamsMatches1']->score : $empty_str) }}
                                    <b>:</b> 
                                    {{ (isset($data['teamsMatches2']->score) ? $data['teamsMatches2']->score : $empty_str) }}
                                    @endif
                                </p>
                                <div class="col-md-12"></div>
                                <button type="button" onclick="location.href='{{ url('/tournament/'.$data['tournament']->id_tournament) }}'" class="btn btn-primary">
                                    Back
                                </button>
                            </div> 
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
