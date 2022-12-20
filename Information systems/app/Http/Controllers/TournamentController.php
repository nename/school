<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use DateTime;

function getTournamentOwner($id_tournament){
    return DB::table('Users_Tournaments')->where('id_tournament', $id_tournament)->first();
}

function canManageTournament($id_tournament){
    $owner_id = getTournamentOwner($id_tournament)->id_user;
    return Auth::check() && ($owner_id == Auth::user()->id_user || Auth::user()->role_user == 'Admin');
}

function getTournamentSize($id_tournament){
    $numberOfTeams = DB::table('Teams_Tournaments')->where('id_tournament', $id_tournament)->count();
    if($numberOfTeams <= 4){
        return 4;
    }elseif($numberOfTeams <= 8){
        return 8;
    }elseif($numberOfTeams <= 16){
        return 16;
    }elseif($numberOfTeams <= 32){
        return 32;
    }elseif($numberOFTeams <= 64){
        return 64;
    }
    return 128;
}

function generateMatch($parentNodeId, $round, $id_tournament){
    if($round == 0){
        return;
    }
    $match_id = DB::table('Matches')->insertGetId(['round_number' => $round, 'id_tournament' => $id_tournament, 'id_next_match' => $parentNodeId]);
    $round--;
    generateMatch($match_id, $round, $id_tournament);
    generateMatch($match_id, $round, $id_tournament);
    return;
}

class TournamentController extends Controller
{
    public function tournamentListPage(){
        $filters = array();
        $tournaments = new Collection();
        $currentDateTime = (new DateTime())->format('Y-m-d H:i:s');

        if(isset($_GET['selection']) && $_GET['selection'] != 'All'){
            if($_GET['selection'] == 'CreatedByMe') {
                array_push($filters, ['role_tournament', '=', 'organizer']);
                array_push($filters, ['id_user', '=', Auth::user()->id_user]);
            }elseif($_GET['selection'] == 'Registered') {
                array_push($filters, ['id_team', '=', Auth::user()->id_team]);
            }
        }
        if(isset($_GET['New']) && $_GET['New'] == True){
            array_push($filters, ['start_date', '>', $currentDateTime]);
            $results = DB::table('Tournaments')
            ->join('Users_Tournaments' ,'Tournaments.id_tournament', '=', 'Users_Tournaments.id_tournament')
            ->leftJoin('Teams_Tournaments' ,'Tournaments.id_tournament', '=', 'Teams_Tournaments.id_tournament')
            ->where($filters)->orderBy('start_date', 'desc')
            ->select('Tournaments.id_tournament as id_tournament', 'name', 'start_date', 'end_date', 'registration_fee', 'reward', 'number_of_players')
            ->get();
            array_pop($filters);
            $tournaments = $tournaments->merge($results);
        }
        if(isset($_GET['InProgress']) && $_GET['InProgress'] == True){
            array_push($filters, ['start_date', '<', $currentDateTime]);
            array_push($filters, ['end_date', '>', $currentDateTime]);
            $results = DB::table('Tournaments')
            ->join('Users_Tournaments' ,'Tournaments.id_tournament', '=', 'Users_Tournaments.id_tournament')
            ->leftJoin('Teams_Tournaments' ,'Tournaments.id_tournament', '=', 'Teams_Tournaments.id_tournament')
            ->where($filters)->orderBy('start_date', 'desc')
            ->select('Tournaments.id_tournament as id_tournament', 'name', 'start_date', 'end_date', 'registration_fee', 'reward', 'number_of_players')
            ->get();
            $tournaments = $tournaments->merge($results);
            array_pop($filters);
            array_pop($filters);
        }
        if(isset($_GET['Finished']) && $_GET['Finished'] == True){
            array_push($filters, ['end_date', '<', $currentDateTime]);
            $results = DB::table('Tournaments')
            ->join('Users_Tournaments' ,'Tournaments.id_tournament', '=', 'Users_Tournaments.id_tournament')
            ->leftJoin('Teams_Tournaments' ,'Tournaments.id_tournament', '=', 'Teams_Tournaments.id_tournament')
            ->where($filters)->orderBy('start_date', 'desc')
            ->select('Tournaments.id_tournament as id_tournament', 'name', 'start_date', 'end_date', 'registration_fee', 'reward', 'number_of_players')
            ->get();
            $tournaments = $tournaments->merge($results);
            array_pop($filters);
        }
        if(!isset($_GET['New']) && !isset($_GET['InProgress']) && !isset($_GET['Finished'])){
            $tournaments = DB::table('Tournaments')
            ->join('Users_Tournaments' ,'Tournaments.id_tournament', '=', 'Users_Tournaments.id_tournament')
            ->leftJoin('Teams_Tournaments' ,'Tournaments.id_tournament', '=', 'Teams_Tournaments.id_tournament')
            ->where($filters)->orderBy('start_date', 'desc')
            ->select('Tournaments.id_tournament as id_tournament', 'name', 'start_date', 'end_date', 'registration_fee', 'reward', 'number_of_players')
            ->get();
        }

        return view('pages/tournamentList')->with('tournamentList', $tournaments->unique('id_tournament'));
    }

    public function creationPage(){
        return view('pages/tournamentCreate');
    }

    public function tournamentPage($id_tournament){
        $tournament = DB::table('Tournaments')->where('id_tournament', $id_tournament)->first();
        $matchesWithTeamsTmp = [];
        if(!Auth::Guest()){
            $userTournaments = DB::table('Users_Tournaments')->where([
                ['id_user', '=', Auth::user()->id_user],
                ['id_tournament', '=', $id_tournament]
            ])->first();
        }
        else{
            $userTournaments = NULL;
        }

        $organizer = DB::table('Users')->join('Users_Tournaments', 'Users_Tournaments.id_user', '=', 'Users.id_user')
                                    ->where([
                                        ['Users_Tournaments.role_tournament', '=', 'organizer'],
                                        ['Users_Tournaments.id_tournament', '=', $id_tournament]
                                    ])
                                    ->first();
        
        $teams = DB::table('Teams')->join('Teams_Tournaments', 'Teams_Tournaments.id_team', '=', 'Teams.id_team')
                                ->where('Teams_Tournaments.id_tournament', '=', $id_tournament)
                                ->select('Teams.*')
                                ->get();
        $roundMax = DB::table('Matches')->where('id_tournament', $id_tournament)->max('round_number');

        $matchesWithTeams = [];
        $matches = DB::table('Matches')->where([
            ['round_number', '=', 1],
            ['id_tournament', '=', $id_tournament]
            ])
            ->get();
        $matchIds = [];
        for($round = 1; $round <= $roundMax; $round++){
            $matches2 = [];
            
            foreach($matches as $dbMatch){
                $teamsMatch = DB::table('Teams_Matches')->join('Teams', 'Teams.id_team', '=', 'Teams_Matches.id_team')
                                                ->where('Teams_Matches.id_match', $dbMatch->id_match)
                                                ->select('Teams.name', 'Teams_Matches.score', 'Teams.id_team')
                                                ->get();
                
                if(count($teamsMatch) == 1){
                    $match['team_id1'] = $teamsMatch[0]->id_team;
                    $match['team_id2'] = NULL;
                    $match['team1'] = $teamsMatch[0]->name;
                    $match['score1'] = $teamsMatch[0]->score;
                    $match['team2'] = NULL;
                    $match['score2'] = NULL;
                    $match['round'] = $dbMatch->round_number;
                    $match['id_match'] = $dbMatch->id_match;
                    $matchesWithTeamsTmp[] = $match;
                }
                elseif(count($teamsMatch) == 2){
                    $match['team_id1'] = $teamsMatch[0]->id_team;
                    $match['team_id2'] = $teamsMatch[1]->id_team;
                    $match['team1'] = $teamsMatch[0]->name;
                    $match['score1'] = $teamsMatch[0]->score;
                    $match['team2'] = $teamsMatch[1]->name;
                    $match['score2'] = $teamsMatch[1]->score;
                    $match['round'] = $dbMatch->round_number;
                    $match['id_match'] = $dbMatch->id_match;
                    $matchesWithTeamsTmp[] = $match;
                }
                else{
                    $match['team_id1'] = NULL;
                    $match['team_id2'] = NULL;
                    $match['team1'] = NULL;
                    $match['score1'] = NULL;
                    $match['team2'] = NULL;
                    $match['score2'] = NULL;
                    $match['round'] = $dbMatch->round_number;
                    $match['id_match'] = $dbMatch->id_match;
                    $matchesWithTeamsTmp[] = $match;
                }
                $isInMatches = true;
                foreach($matchIds as $matchId){
                    if($dbMatch->id_next_match == $matchId){
                        $isInMatches = false;
                    }
                }
                if($isInMatches){
                    $match2 = DB::table('Matches')->where([
                                                            ['id_tournament', '=', $id_tournament],
                                                            ['id_match', '=', $dbMatch->id_next_match]
                                                        ])
                                                        ->first();
                    if($match2 != NULL){
                        $matches2[] = $match2;
                    }
                    $matchIds[] = $dbMatch->id_next_match;
                }
            }
            $matches = $matches2;
        }

        $sponsors = DB::table('Sponsors')->where('id_tournament', $id_tournament)->get();

        $referees = DB::table('Users_Tournaments')->join('Users', 'Users.id_user', '=', 'Users_Tournaments.id_user')->
                                                    where('Users_Tournaments.id_tournament', $id_tournament)->
                                                    where('Users_Tournaments.role_tournament', '=', 'Referee')->
                                                    select('Users.nickname')->get();
        $reg = 1;
        if (DB::table('Tournaments')->where('id_tournament', $id_tournament)->where('registration_ended', 'True')->first()){
            $reg = 0;
        }
        $isOwner = 1;
        $isRef = 1;
        $isPlaying = 1;
        if (!Auth::guest()) {
            if (DB::table('Users_Tournaments')->where([['id_tournament', '=', $id_tournament], ['id_user', '=', Auth::user()->id_user], ['role_tournament', '=', 'organizer']])->first())
                $isOwner = 0;

            if (DB::table('Users_Tournaments')->where([['id_tournament', '=', $id_tournament], ['id_user', '=', Auth::user()->id_user], ['role_tournament', '=', 'referee']])->first())
                $isRef = 0;

            if (DB::table('Teams_Tournaments')->where([['id_tournament', '=', $id_tournament], ['id_team', '=', Auth::user()->id_team]])->first())
                $isPlaying = 0;
        } else {
            $isOwner = 0;
        }

        $data = [
            'tournament' => $tournament,
            'teams' => $teams,
            'matches' => $matchesWithTeamsTmp,
            'user_tournaments' => $userTournaments,
            'round' => $roundMax,
            'referees' => $referees,
            'registration' => $reg,
            'sponsors' => $sponsors,
            'isPlaying' => $isPlaying,
            'isRef' => $isRef,
            'isOwner' => $isOwner,
            'organizer' => $organizer
        ];

        return view('pages/tournament')->with('data',$data);
    }

    public function createTournament(){
        $newTournament = [
            'name' => $_POST['name'],
            'number_of_players' => $_POST['number_of_players'],
            'registration_fee' => $_POST['registration_fee'],
            'reward' => $_POST['reward'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'description' => $_POST['description'],
            'max_number_of_teams' => $_POST['max_number_of_teams']
        ];
        $newTournamentId = DB::table('Tournaments')->insertGetId($newTournament);
        $newUser_Tournament = [
            'id_user' => $_POST['id_user'],
            'id_tournament' => $newTournamentId,
            'role_tournament' => 'organizer'
        ];
        DB::table('Users_Tournaments')->insert($newUser_Tournament);
        return redirect('/tournament/'.$newTournamentId);
    }

    public function deleteTournament(){
        $id_tournament = $_POST['id_tournament'];
        DB::table('Sponsors')->where('id_tournament', $id_tournament)->delete();
        DB::table('Users_Tournaments')->where('id_tournament', $id_tournament)->delete();
        DB::table('Teams_Tournaments')->where('id_tournament', $id_tournament)->delete();
        $matches = DB::table('Matches')->where('id_tournament', $id_tournament)->get();
        foreach ($matches as $match) {
            DB::table('Teams_Matches')->where('id_match', $match->id_match)->delete();
        }
        DB::table('Matches')->where('id_tournament', $id_tournament)->delete();
        DB::table('Tournaments')->where('id_tournament', $id_tournament)->delete();
        return redirect('/home');
    }

    public function joinTournament(){
        if (Auth::guest()) return redirect('/login'); // guest

        $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();

        if (DB::table('Users_Tournaments')->where([['id_tournament','=', $_POST['id_tournament']],['id_user','=', $user->id_user]])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'Referee and owner cannot be in tournament.');
        if (is_null($user->id_team) || $user->role_team != 'Owner')
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'User is not Owner of the team.');

        $tournament = DB::table('Tournaments')->where('id_tournament', $_POST['id_tournament'])->first();

        if (DB::table('Teams_Tournaments')->where([['id_team','=', $user->id_team],['id_tournament','=', $_POST['id_tournament']]])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You are already in this tournament.');
        if ((count(DB::table('Users')->where('id_team', $user->id_team)->get()) < $tournament->number_of_players))
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'Your team does not have enough players.');
        if (count(DB::table('Teams_Tournaments')->where('id_tournament', $_POST['id_tournament'])->get()) == $tournament->max_number_of_teams)
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'Tournament is full.');

        $newTeams_Tournaments = [
            'id_team' => $user->id_team,
            'id_tournament' => $tournament->id_tournament
        ];

        DB::table('Teams_Tournaments')->insert($newTeams_Tournaments);
        return redirect('/tournament/'.$tournament->id_tournament)->with('message', 'Tournament joined.');
    }

    public function leaveTournament(){
        if (Auth::guest()) return redirect('/login'); // guest

        $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();

        if (!DB::table('Teams_Tournaments')->where([['id_tournament','=', $_POST['id_tournament']],['id_team','=', $user->id_team]])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You are not part of this tournament.');

        if (!is_null($user->id_team) && DB::table('Teams_Tournaments')->where([['id_tournament','=', $_POST['id_tournament']],['id_team','=', $user->id_team]])->first() && $user->role_team == 'Owner'){
            DB::table('Teams_Tournaments')->where('id_tournament', $_POST['id_tournament'])->where('id_team', $user->id_team)->delete();
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You left tournament.');
        }
        return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You can not leave the tournament.');
    }

    public function refereeTournament(){
        if (Auth::guest()) return redirect('/login'); // guest

        $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();

        if (!is_null($user->id_team) && DB::table('Teams_Tournaments')->where([['id_tournament','=', $_POST['id_tournament']],['id_team','=', $user->id_team]])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'Player can not be a referee.');
        if (DB::table('Users_Tournaments')->where([['id_user','=', $user->id_user],['id_tournament','=', $_POST['id_tournament']],['role_tournament','=', 'referee']])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'User is already referee.');
        if (DB::table('Users_Tournaments')->where([['id_user','=', $user->id_user],['id_tournament','=', $_POST['id_tournament']],['role_tournament','=', 'organizer']])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'Tournament owner cant be referee.');

        $newUsers_Tournaments = [
            'id_user' => $user->id_user,
            'id_tournament' => $_POST['id_tournament'],
            'role_tournament' => 'referee'
        ];

        DB::table('Users_Tournaments')->insert($newUsers_Tournaments);
        return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You joined tournament as referee.');
    }

    public function refereeLeave(){
        if (Auth::guest()) return redirect('/login'); // guest

        $user = DB::table('Users')->where('id_user', Auth::user()->id_user)->first();

        if (!DB::table('Users_Tournaments')->where([['id_tournament','=', $_POST['id_tournament']],['id_user','=', $user->id_user]])->first())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You are not part of this tournament.');

        if (DB::table('Users_Tournaments')->where([['id_tournament','=', $_POST['id_tournament']],['id_user','=', $user->id_user],['role_tournament','=', 'Referee']])->delete())
            return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You left tournament as referee.');

        return redirect('/tournament/'.$_POST['id_tournament'])->with('message', 'You are not part of this tournament.');
    }

    public function manageTournamentPage($id_tournament){
        $owner_id = getTournamentOwner($id_tournament)->id_user;
        if ( !Auth::check() || ($owner_id != Auth::user()->id_user && Auth::user()->role_user != 'Admin')){
            return redirect('/tournament/'.$id_tournament);
        }
        $tournament = DB::table('Tournaments')->where('id_tournament', $id_tournament)->first();
        $teams = DB::table('Teams')->join('Teams_Tournaments', 'Teams_Tournaments.id_team', '=', 'Teams.id_team')
        ->where('Teams_Tournaments.id_tournament', '=', $id_tournament)
        ->select('Teams.*')
        ->get();
        $referees = DB::table('Users_Tournaments')->join('Users', 'Users.id_user', '=', 'Users_Tournaments.id_user')
        ->where('Users_Tournaments.id_tournament', $id_tournament)
        ->where('Users_Tournaments.role_tournament', '=', 'Referee')
        ->get();
        $sponsors = DB::table('Sponsors')->where('id_tournament', $id_tournament)->get();

        return view('pages/tournamentManage')->with('tournament', $tournament)->with('teams', $teams)->with('referees', $referees)->with('sponsors', $sponsors);
    }

    public function addSponsor(){
        if (!canManageTournament($_POST['id_tournament'])){
            return redirect('/tournament/'.$_POST['id_tournament']);
        }
        
        DB::table('Sponsors')->updateOrInsert(['id_tournament' => $_POST['id_tournament'], 'name' => $_POST['name']]);

        return redirect('/tournamentManage/'.$_POST['id_tournament']);
    }

    public function removeSponsor(){
        if (!canManageTournament($_POST['id_tournament'])){
            return redirect('/tournament/'.$_POST['id_tournament']);
        }

        DB::table('Sponsors')->where('id_tournament', $_POST['id_tournament'])->where('id_sponsor', $_POST['id_sponsor'])->delete();

        return redirect('/tournamentManage/'.$_POST['id_tournament']);
    }

    public function kickReferee(){
        if (!canManageTournament($_POST['id_tournament'])){
            return redirect('/tournament/'.$_POST['id_tournament']);
        }
        DB::table('Users_Tournaments')->where('id_user', $_POST['id_user'])->where('id_tournament', $_POST['id_tournament'])->delete();
        return redirect('/tournamentManage/'.$_POST['id_tournament']);
    }

    public function kickTeam(){
        if (!canManageTournament($_POST['id_tournament'])){
            return redirect('/tournament/'.$_POST['id_tournament']);
        }
        DB::table('Teams_Tournaments')->where('id_team', $_POST['id_team'])->where('id_tournament', $_POST['id_tournament'])->delete();
        return redirect('/tournamentManage/'.$_POST['id_tournament']);
    }

    public function changeProperties(){
        if (!canManageTournament($_POST['id_tournament'])){
            return redirect('/tournament/'.$_POST['id_tournament']);
        }
        $newTournamentProperties = [
            'name' => $_POST['name'],
            'registration_fee' => $_POST['registration_fee'],
            'description' => $_POST['description'],
            'number_of_players' => $_POST['number_of_players'],
            'reward' => $_POST['reward'],
            'max_number_of_teams' => $_POST['max_number_of_teams']
        ];
        DB::table('Tournaments')->where('id_tournament', $_POST['id_tournament'])->update($newTournamentProperties);
        return redirect('/tournamentManage/'.$_POST['id_tournament']);
    }

    public function generateMatches(){
        if (!canManageTournament($_POST['id_tournament'])){
            return redirect('/tournament/'.$_POST['id_tournament']);
        }
        $tournament = DB::table('Tournaments')->where('id_tournament', $_POST['id_tournament'])->first();
        $tournamentSize = getTournamentSize($tournament->id_tournament);
        $numberOfRounds = log($tournamentSize, $base = 2);

        generateMatch(null, $numberOfRounds, $tournament->id_tournament);

        // fill first round with teams
        $registeredTeams = DB::table('Teams_Tournaments')->where('id_tournament', $tournament->id_tournament)->get();
        $firstRoundMatches = DB::table('Matches')->where('id_tournament', $tournament->id_tournament)->where('round_number', '1')->get();
        $newTeamMatches = [];
        for($i = 0; $i < count($registeredTeams); $i++){
            if($i < $tournamentSize / 2){
                array_push($newTeamMatches, ['id_team' => $registeredTeams[$i]->id_team, 'id_match' => $firstRoundMatches[$i]->id_match ]);
            }else{
                $index = $i - $tournamentSize / 2;
                array_push($newTeamMatches, ['id_team' => $registeredTeams[$i]->id_team, 'id_match' => $firstRoundMatches[$index]->id_match ]);
            }
        }
        DB::table('Teams_Matches')->insert($newTeamMatches);
        
        // push free wins to next round

        $matches = DB::table('Matches')->where('id_tournament', $tournament->id_tournament)->where('round_number', 1)->get();
        foreach ($matches as $match) {
            // if only one team in match
            if(DB::table('Teams_Matches')->where('id_match', $match->id_match)->count() == 1){
                // push to next round
                $team_match = DB::table('Teams_Matches')->where('id_match', $match->id_match)->first();
                $team_match->id_match = $match->id_next_match;
                DB::table('Teams_Matches')->insert(['id_team' => $team_match->id_team, 'id_match' => $match->id_next_match]);
            }
        }

        // end registration
        DB::table('Tournaments')->where('id_tournament', $tournament->id_tournament)->update(['registration_ended' => "True"]);
        
        return redirect('/tournament/'.$tournament->id_tournament);
    }
}
