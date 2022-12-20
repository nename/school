@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Tournament: {{$data['tournament']->name}}
                        @if(Auth::check() && (Auth::user()->role_user == 'Admin' || (!is_null($data['organizer']) && Auth::user()->id_user == $data['organizer']->id_user)))
                            <a href="{{ url('/tournamentManage') }}/{{ $data['tournament']->id_tournament }}" style="float: right;font-size: 15px">
                                <span style="font-size: 20px"> <b> Manage </b> </span> 
                            </a>
                        @endif
                    </div>
                    <div class="panel-body">
                    @if ($data['registration'])
                        @if ($data['isOwner'])
                        <div style="padding-left: 30px; padding-bottom: 10px">
                        @if ($data['isRef'])
                            @if ($data['isPlaying'])
                            <form method="POST" action="{{url('/joinTournament')}}" style="display: inline">
                                {{ csrf_field() }}
                                <input name="id_tournament" type="hidden" value="{{$data['tournament']->id_tournament}}">
                                <button type="submit" class="btn btn-primary">Join tournament</button>
                            </form>
                            @else
                            <form method="POST" action="{{url('/leaveTournament')}}" style="display: inline">
                                {{ csrf_field() }}
                                <input name="id_tournament" type="hidden" value="{{$data['tournament']->id_tournament}}">
                                <button type="submit" class="btn btn-primary">Leave tournament</button>
                            </form>
                            @endif
                        @endif
                        @if ($data['isPlaying'])
                            @if ($data['isRef'])
                            <form method="POST" action="{{url('/refereeTournament')}}" style="display: inline">
                                {{ csrf_field() }}
                                <input name="id_tournament" type="hidden" value="{{$data['tournament']->id_tournament}}">
                                <button type="submit" class="btn btn-primary">Join as referee</button>
                            </form>
                            @else
                            <form method="POST" action="{{url('/refereeLeave')}}" style="display: inline">
                                {{ csrf_field() }}
                                <input name="id_tournament" type="hidden" value="{{$data['tournament']->id_tournament}}">
                                <button type="submit" class="btn btn-primary">Leave as referee</button>
                            </form>
                            @endif
                        @endif
                            @if(session('message'))
                                {{session('message')}}
                            @endif
                        </div>
                        @endif
                    @endif
                        <div>
                            <div class="col-md-10">
                                <p class="col-md-6"><b>Start:</b> {{$data['tournament']->start_date}} </p>
                            </div>
                            <div class="col-md-10">
                                <p class="col-md-6"><b>End:</b> {{$data['tournament']->end_date}} </p>
                            </div>
                            <div class="col-md-10">
                                <p class="col-md-6"><b>Fee:</b> {{$data['tournament']->registration_fee}} </p>
                            </div>
                            <div class="col-md-10">
                                <p class="col-md-6"><b>Number of players:</b> {{$data['tournament']->number_of_players}} </p>
                            </div>
                            <div class="col-md-10">
                                <p class="col-md-6"><b>Reward:</b> {{$data['tournament']->reward}} </p>
                            </div>
                            <div class="col-md-10">
                                <p class="col-md-6"><b>Description:</b> {{$data['tournament']->description}} </p>
                            </div>
                            <div class="col-md-10">
                            <p class="col-md-10"><b>Organizer: </b><a style="color:inherit; text-decoration:none" href="{{$data['organizer'] == NULL ? "" : url('/profile/'.$data['organizer']->nickname)}}">{{$data['organizer'] == NULL ? "" : $data['organizer']->name." \"".$data['organizer']->nickname."\" ".$data['organizer']->surname}}</a> </p>
                            </div>
                        </div>
                        <div>
                            <div>
                                <div class="col-md-10">
                                    <p class="col-md-10"><b>Referees:</b> </p>

                                    <div class="col-md-10">
                                        <table class="col-md-6">
                                            @foreach ($data['referees'] as $referee)
                                                <tr><td><a style="color:inherit; text-decoration:none" href="{{ url('/profile/'.$referee->nickname) }}"> {{$referee->nickname}} </a></tr></td>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <div>
                            <div class="col-md-10" style="padding-top: 12px">
                                <p class="col-md-10"><b>Teams:</b> </p>
                                
                                <div class="col-md-10">
                                    <table class="col-md-6">
                                    @foreach ($data['teams'] as $team)
                                        <tr><td><a style="color:inherit; text-decoration:none" href="{{ url('/team/'.$team->id_team) }}"> {{$team->name}} </a></tr></td>
                                    @endforeach
                                    </table>
                                </div>                
                            </div>
                        </div>
                        </div>
                        <div>
                            <div class="col-md-12" style="padding-top: 12px">
                                <p class="col-md-10"><b>Sponsors:</b> </p>
                                <div class="col-md-12">
                                <table>
                                @foreach ($data['sponsors'] as $sponsor)
                                    <tr>
                                        <td>{{$sponsor->name}}</td>
                                    </tr>
                                @endforeach
                                </table>
                                </div>
                            </div>
                            <div class="col-md-12" style="padding-top: 12px">
                                <p class="col-md-10"><b>Matches:</b> </p>
                            </div>
                            <div class="col-md-10" style="padding-left: 30px">
                                <table>
                                @php
                                    $rowsContent = [];
                                    $rowCount = 0;
                                    $emptyMatch = [
                                        'team1' => "",
                                        'team2' => "",
                                        'score1' => "",
                                        'score2' => "",
                                        'round' => ""
                                    ];

                                    foreach ($data['matches'] as $match) {
                                        if($match['round'] == 1){
                                            if(($match['team1'] == NULL) || ($match['team2'] == NULL)){
                                                $row = "<tr><td width=\"40\"></td width=\"40\"><td width=\"40\"></td>";
                                                $rowsContent[] = $row;
                                                $row = "<tr height=\"30\"><td style=\"border-bottom: 1px solid\"></td><td width=\"40\" style=\"border-bottom: 1px solid\"></td>";
                                                $rowsContent[] = $row;
                                                $row = "<tr><td width=\"40\"></td width=\"40\"><td width=\"40\"></td>";
                                                $rowsContent[] = $row;
                                                $row = "<tr height=\"30\"><td></td><td></td>";
                                                $rowsContent[] = $row;
                                            }
                                            else{
                                                $row = "<tr><td width=\"40\" style=\"border-bottom: 1px solid\"><a style=\"color:inherit; text-decoration:none\" href=\"".url('/team/'.$match['team_id1'])."\">".$match['team1']."</a></td><td></td>";
                                                $rowsContent[] = $row;
                                                $row = "<tr height=\"30\"><td style=\"border-right: 1px solid\"></td><td width=\"40\" style=\"border-bottom: 1px solid\"></td>";
                                                $rowsContent[] = $row;
                                                $row = "<tr><td style=\"border-bottom: 1px solid; border-right: 1px solid;\"><a style=\"color:inherit; text-decoration:none\" href=\"".url('/team/'.$match['team_id2'])."\">".$match['team2']."</a></td><td></td>";
                                                $rowsContent[] = $row;
                                                $row = "<tr height=\"30\"><td></td><td></td>";
                                                $rowsContent[] = $row;
                                                
                                            }
                                            $rowCount += 4;
                                        }
                                        else{
                                            break;  
                                        }
                                    }
                                    if(count($data['matches']) > 0){
                                        $matchInx = 0;
                                        $columnCount = ($data['round'] * 2) - 1;
                                        $rowOffsetTmp = $rowOffset = 1;
                                        $valueRowTmp = $valueRow = 3;
                                        $valueRowEndTmp = $valueRowEnd = 5;

                                        for($columnNum = 0; $columnNum < $columnCount; $columnNum++){
                                            if((($columnNum % 2) == 0) && ($columnNum > 1)){
                                                $rowOffsetTmp = $rowOffset = ($columnNum * 2) - 1;
                                                $valueRowEndTmp = $valueRowEnd = (($rowOffset + 1) * 3) - 1;
                                            }
                                            else{
                                                $valueRowTmp = $valueRow = (($columnNum + 1) * 2) - 1;
                                            }
                                            for($rowNum = 0; $rowNum < $rowCount; $rowNum++){
                                                if(($columnNum % 2) == 0){
                                                    if($rowNum < $rowOffset){
                                                        $rowsContent[$rowNum] .= "<td width=\"40\"></td>";
                                                    }
                                                    elseif($rowNum == $rowOffset){
                                                        if($matchInx >= count($data['matches'])){
                                                            $nextMatch = $emptyMatch;
                                                        }
                                                        else{
                                                            $nextMatch = $data['matches'][$matchInx];
                                                        }
                                                        $matchInx++;
                                                        if(empty($nextMatch['id_match'])){
                                                            $matchUrl = "";
                                                        }
                                                        else{
                                                            $matchUrl = url('/match/'.$nextMatch['id_match']);
                                                        }
                                                        if((($nextMatch['team1'] == NULL) || ($nextMatch['team2'] == NULL)) && ($columnNum == 0)){
                                                            $matchResult = "<a style=\"color:inherit; text-decoration:none\" href=\"".url('/team/'.$nextMatch['team_id1'])."\">".$nextMatch['team1']."</a>";
                                                        }
                                                        elseif(!is_null($nextMatch['score1']) && !is_null($nextMatch['score2'])){
                                                            $matchResult = ($nextMatch['score1'] > $nextMatch['score2']) ? $nextMatch['score1'].":".$nextMatch['score2']." ".$nextMatch['team1'] : $nextMatch['score2'].":".$nextMatch['score1']." ".$nextMatch['team2'];
                                                            $matchResult = "<a style=\"color:inherit; text-decoration:none\" href=\"".$matchUrl."\">".$matchResult."</a>";
                                                        }
                                                        else{
                                                            $matchResult = "<a style=\"color:inherit; text-decoration:none\" href=\"".$matchUrl."\">X</a>";
                                                        }
                                                        
                                                        $rowsContent[$rowNum] .= "<td style=\"border-bottom: 1px solid\">".$matchResult."</td>";
                                                    }
                                                    elseif(($rowNum > $rowOffset) && ($rowNum < $valueRowEnd) && ($columnNum <= ($columnCount - 2))){
                                                        $rowsContent[$rowNum] .= "<td style=\"border-right: 1px solid\"></td>";
                                                    }
                                                    elseif($rowNum == $valueRowEnd){
                                                        
                                                        $rowOffset += ((($valueRowEnd - $rowOffsetTmp) + 1) * 2) - 2;
                                                        $valueRowEnd += (2 * (($valueRowEnd - $rowOffsetTmp) + 1)) - 2;
                                                        
                                                        if($matchInx >= count($data['matches'])){
                                                            $nextMatch = $emptyMatch;
                                                        }
                                                        else{
                                                            $nextMatch = $data['matches'][$matchInx];
                                                        }
                                                        $matchInx++;
                                                        if($columnNum <= ($columnCount - 2)){
                                                            $rightSide = "; border-right: 1px solid;";
                                                        }
                                                        else{
                                                            $rightSide = "";
                                                        }
                                                        if(empty($nextMatch['id_match'])){
                                                            $matchUrl = "";
                                                        }
                                                        else{
                                                            $matchUrl = url('/match/'.$nextMatch['id_match']);
                                                        }
                                                        if((($nextMatch['team1'] == NULL) || ($nextMatch['team2'] == NULL)) && ($columnNum == 0)){
                                                            $matchResult = "<a style=\"color:inherit; text-decoration:none\" href=\"".url('/team/'.$nextMatch['team_id1'])."\">".$nextMatch['team1']."</a>";
                                                        }
                                                        elseif(!is_null($nextMatch['score1']) && !is_null($nextMatch['score2'])){
                                                            $matchResult = ($nextMatch['score1'] > $nextMatch['score2']) ? $nextMatch['score1'].":".$nextMatch['score2']." ".$nextMatch['team1'] : $nextMatch['score2'].":".$nextMatch['score1']." ".$nextMatch['team2'];
                                                            $matchResult = "<a style=\"color:inherit; text-decoration:none\" href=\"".$matchUrl."\">".$matchResult."</a>";
                                                        }
                                                        else{
                                                            $matchResult = "<a style=\"color:inherit; text-decoration:none\" href=\"".$matchUrl."\">X</a>";
                                                        }

                                                        $rowsContent[$rowNum] .= "<td style=\"border-bottom: 1px solid".$rightSide."\">".$matchResult."</td>";
                                                    }
                                                }
                                                else{
                                                    if($rowNum < $valueRow){
                                                        $rowsContent[$rowNum] .= "<td width=\"40\"></td>";
                                                    }
                                                    elseif($rowNum == $valueRow){
                                                        $rowsContent[$rowNum] .= "<td width=\"40\" style=\"border-bottom: 1px solid\"></td>";
                                                        $valueRow += 2 * ($valueRowTmp + 1);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    foreach ($rowsContent as $rowContent) {
                                        $rowContent .= "</tr>";
                                        echo($rowContent);
                                    }
                                @endphp
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection