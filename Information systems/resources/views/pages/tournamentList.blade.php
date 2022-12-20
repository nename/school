@extends('layouts.app')
@section('content')
<link href="{{ asset('css/table.css') }}" rel="stylesheet">
<div class="container">
        <div class="row">
            <div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <b style="font-size: 20px"> Tournaments </b>
                        @if (Auth::check())    
                            <a href="{{ url('/tournament_create') }}" style="float: right;font-size: 15px">
                                <span style="font-size: 20px"> <b> Create </b> </span> 
                            </a>
                        @endif
                    </div>
                    {{-- Filter bar --}}
                    <div class="panel-heading">
                        <form style="width: 100%;" name="Filter" class="form-horizontal" method="get" action="{{ url('/tournaments') }}">
                            <span style="font-size: 15px; margin-right: 2%"> <b> Filters: </b> </span> 
                            @if(Auth::check())
                                <select name="selection">
                                    <option value="All">All</option>
                                    <option value="CreatedByMe"> Created by me </option>
                                    <option value="Registered"> Registered </option>
                                </select>
                            @endif
                            <input type="checkbox" name="New" value="true"> New
                            <input type="checkbox" name="InProgress" value="true"> In Progress
                            <input type="checkbox" name="Finished" value="true"> Finished                        
                            <button style="float: right"type="submit">Apply filters!</button>
                        </form>
                    </div>
                    {{-- Tournament List  --}}
                    <div class="panel-body">
                        <div>
                            <table style="width: 100%">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Registration fee</th>
                                    <th>Players in team</th>
                                    <th>Reward</th>
                                    <th></th>
                                </tr>
                                @foreach ($tournamentList as $tournament)
                                    <tr>
                                        <td>{{$tournament->id_tournament}}</td>
                                        <td>{{$tournament->name}}</td>
                                        <td>{{$tournament->start_date}}</td>
                                        <td>{{$tournament->end_date}}</td>
                                        <td>{{$tournament->registration_fee}}</td>
                                        <td>{{$tournament->number_of_players}}</td>
                                        <td>{{$tournament->reward}}</td>
                                        <td> <a href="{{ url('/tournament') }}/{{$tournament->id_tournament}}"> GO! </a></td>
                                    </tr>
                                @endforeach
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection