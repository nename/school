<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MatchController extends Controller
{
    public function matchPage($id_match){
        $match = DB::table('Matches')->where('id_match', $id_match)->first();
        $teamsMatches = DB::table('Teams_Matches')->where('id_match', $id_match)->get();
        
        $editEnabled = false;
        if(count($teamsMatches) == 2){
            if(empty($teamsMatches[0]->score) && empty($teamsMatches[1]->score)){
                $editEnabled = true;
            }
        }
        if(!Auth::guest()){
            $usersTournaments = DB::table('Users_Tournaments')->where([
                ['id_tournament', '=', $match->id_tournament],
                ['id_user', '=', Auth::user()->id_user]
            ])->first();
        }
        else{
            $usersTournaments = NULL;
        }
        
        if(count($teamsMatches) == 1){
            $teamsMatches1 = $teamsMatches[0];
            $team1 = DB::table('Teams')->where('id_team', $teamsMatches1->id_team)->first();
            $teamsMatches2 = NULL;
            $team2 = NULL;
        }
        elseif(count($teamsMatches) == 2){
            $teamsMatches1 = $teamsMatches[0];
            $teamsMatches2 = $teamsMatches[1];

            $team1 = DB::table('Teams')->where('id_team', $teamsMatches1->id_team)->first();
            $team2 = DB::table('Teams')->where('id_team', $teamsMatches2->id_team)->first();
        }
        else{
            $teamsMatches1 = NULL;
            $teamsMatches2 = NULL;
            $team1 = NULL;
            $team2 = NULL;
        }

        $tournament =  DB::table('Tournaments')->where('id_tournament', $match->id_tournament)->first();
        
        $data = [
            'match' => $match,
            'team1' => $team1,
            'team2' => $team2,
            'teamsMatches1' => $teamsMatches1,
            'teamsMatches2' => $teamsMatches2,
            'tournament' => $tournament,
            'usersTournaments' => $usersTournaments,
            'editEnabled' => $editEnabled
        ];

        return view('pages/match')->with('data', $data);
    }

    public function matchCreate($id_tournament){
        if(!Auth::guest()){
            $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();
            $usersTournaments = $usersTournaments = DB::table('Users_Tournaments')->where([
                ['id_tournament', '=', $id_tournament],
                ['id_user', '=', $user->id_user]
            ])->first();
            if($usersTournaments == NULL){
                return redirect('/pages/tournament/'.$id_tournament);
            }
            if(($usersTournaments->role_tournament == 'referee') || ($usersTournaments->role_tournament == 'organizer') || (Auth::user()->role_user == 'Admin')){
                if(!is_null($id_tournament)){
                    $id_match_last = DB::table('Matches')->max('id_match');
                    if(is_null($id_match_last)){
                        $id_match_last = 1;
                    }
                    else{
                        $id_match_last++;
                    }
        
                    DB::table('Matches')->insert(
                        [
                            'id_match' => $id_match_last,
                            'id_tournament' => $id_tournament
                        ]
                    );
                    return redirect('/match/'.$id_match_last);
                }
                else{
                    return view('pages/home');
                }
            }
        }
        else{
            return redirect('/tournament/'.$id_tournament);
        }        
    }

    public function matchUpdate(Request $request, $id_match){
        if(!Auth::guest()){
            
            $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();
            $match = DB::table('Matches')->where('id_match', $id_match)->first();
            $usersTournaments = $usersTournaments = DB::table('Users_Tournaments')->where([
                ['id_tournament', '=', $match->id_tournament],
                ['id_user', '=', $user->id_user]
            ])->first();

            $canEdit = false;
            if($usersTournaments == NULL){
                if(Auth::user()->role_user == 'Admin'){
                    $canEdit = true;
                }
                else{
                    return redirect('/tournament/'.$match->id_tournament);
                }
            }
            elseif(($usersTournaments->role_tournament == 'referee') || ($usersTournaments->role_tournament == 'organizer') || (Auth::user()->role_user == 'Admin')){
                $canEdit = true;
            }

            if($canEdit){
                if(!is_null($id_match)){
                    $score1 = $request->input('score1');
                    $score2 = $request->input('score2');

                    $match = DB::table('Matches')->where('id_match', $id_match)->first();

                    $round = $match->round_number;
                    $teams = DB::table('Teams')->join('Teams_Matches', 'Teams_Matches.id_team', '=', 'Teams.id_team')
                                                ->where('id_match', $match->id_match)
                                                ->select('Teams.id_team', 'Teams_Matches.score')
                                                ->get();
                    $team1 = NULL;
                    $team2 = NULL;
                    if(count($teams) == 1){
                        $team1 = $teams[0];
                    }
                    elseif(count($teams) == 2){
                        $team1 = $teams[0];
                        $team2 = $teams[1];
                    }

                    if($team1 != NULL){
                        DB::table('Teams_Matches')->where([
                            ['id_match', '=', $id_match],
                            ['id_team', '=', $team1->id_team]
                        ])->updateOrInsert(
                            [
                                'id_match' => $id_match,
                            ],
                            [
                                'id_match' => $id_match,
                                'id_team' => $team1->id_team,
                                'score' => $score1
                            ]
                        );
                    }
                    
                    if($team2 != NULL){
                        DB::table('Teams_Matches')->where([
                            ['id_match', '=', $id_match],
                            ['id_team', '=', $team2->id_team]
                        ])->updateOrInsert(
                            [
                                'id_match' => $id_match,
                            ],
                            [
                                'id_team' => $team2->id_team,
                                'score' => $score2
                            ]
                        );
                    }
                    //push winner to next round 
                    if(!is_null($match->id_next_match)){
                        $id_winner = $score1 > $score2 ? $team1->id_team: $team2->id_team;
                        DB::table('Teams_Matches')->where([
                                ['id_match', '=', $match->id_next_match],
                                ['id_team', '=', $id_winner]
                            ])->updateOrInsert(
                            [
                                'id_match' => $match->id_next_match,
                            ],
                            [
                                'id_team' => $id_winner,
                            ]
                        );
                    }

                    return redirect('/tournament/'.$match->id_tournament);
                }
                else{
                    return view('pages/home');
                }
            }
        }

        return redirect('/tournament/'.$match->id_tournament);
    }

    public function matchDelete($id_match)
    {
        if(!Auth::guest()){
            $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();
            $match = DB::table('Matches')->where('id_match', $id_match)->first();
            $usersTournaments = $usersTournaments = DB::table('Users_Tournaments')->where([
                ['id_tournament', '=', $match->id_tournament],
                ['id_user', '=', $user->id_user]
            ])->first();
            if($usersTournaments == NULL){
                return redirect('/tournament/'.$id_tournament);
            }
            if(($usersTournaments->role_tournament == 'referee') || ($usersTournaments->role_tournament == 'organizer' || (Auth::user()->role_user == 'Admin'))){
                DB::table('Matches')->where('id_match', $id_match)->delete();
                return redirect('/tournament/'.$match->id_tournament);
            }
        }
        
        return redirect('/match/'.$id_match);
    }
}
