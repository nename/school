<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

function isEmptyTeam($id_team){
    return DB::table('Users')->where('id_team', $id_team)->first() === null;
}

function deleteTeam($id_team){
    DB::table('Teams_Matches')->where('id_team', $id_team)->delete();
    DB::table('Teams_Tournaments')->where('id_team', $id_team)->delete();
    DB::table('Users')->where('id_team', $id_team)->update(['id_team' => null, 'role_team' => null]);
    DB::table('Teams')->where('id_team', $id_team)->delete();
}

function getOwnerId($id_team){
    $owner = DB::table('Users')->where([['id_team', '=', $id_team],['role_team', '=', 'organizer']])->first();
    if(!is_null( $owner )){
        $owner_id = $owner->id_user;
    }else{
        $owner_id = null;
    }
    return $owner_id;
}

class TeamController extends Controller
{
    public function createTeam(){
        // create new team
        $newTeam = [
                'name' => $_POST['name'],
                'abbreviation' => $_POST['shortcut'],
                'logo' => $_POST['select']
        ];
        $newTeamId = DB::table('Teams')->insertGetId($newTeam);
        // update user with his team
        DB::table('Users')
        ->where('id_user', $_POST['id_user'])
        ->update(['id_team' => $newTeamId, 'role_team' => 'Owner']);
        // redirect to newly created team page
        return redirect('/team/'.$newTeamId);
    }

    public function deleteTeam(){
        if(Auth::check() && Auth::user()->role_user == 'Admin'){
            deleteTeam($_POST['id_team']);
            return redirect('/home');
        }else{
            return 'Unauthorized access!';
        }
    }

    public function createTeamPage(){
        if(Auth::user()->id_team != null){
            return redirect('/team/'.Auth::user()->id_team);
        }
        return view('pages/teamCreate');
    }

    public function teamPage($id_team){
        $team = DB::table('Teams')->where('id_team', $id_team)->first();
        if(is_null($team)){
            return redirect('/home');
        }
        $members = DB::table('Users')->where('id_team', $id_team)->get();
        if(count($members) == 0){
            deleteTeam($id_team);
            return redirect('/home');
        }
        $tournaments = DB::table('Tournaments')
        ->join('Teams_Tournaments', 'Tournaments.id_tournament', '=', 'Teams_Tournaments.id_tournament')
        ->where('Teams_Tournaments.id_team', $id_team)
        ->get();
        return view('pages/team')->with('team', $team)->with('members', $members)->with('tournaments', $tournaments)->with('owner_id', getOwnerId($id_team));
    }

    public function manageTeamPage($id_team){
        if(!Auth::check() || (!is_null(getOwnerId($id_team)) && Auth::user()->id_user != getOwnerId($id_team)) && Auth::user()->role_user != 'Admin'){
            return redirect('/team/'.$id_team);
        }
        $team = DB::table('Teams')->where('id_team', $id_team)->first();
        if(is_null($team)){
            return redirect('/home');
        }
        $members = DB::table('Users')->where('id_team', $id_team)->get();
        
        return view('pages/teamManage')->with('team', $team)->with('members', $members)->with('owner_id', getOwnerId($id_team));
    }

    public function promoteMember(){
        try{
            DB::table('Users')->where([['id_team', '=', $_POST['id_team']],['role_team', '=', 'organizer']])->update(['role_team' => 'Member']);
        }catch (Exception $e){}
        DB::table('Users')->where('id_user', $_POST['id_user'])->update(['role_team' => 'organizer']);
        if(Auth::user()->role_user == 'Admin'){
            return redirect('/manageTeam/'.$_POST['id_team']);
        }
        return redirect('/team/'.$_POST['id_team']);
    }

    public function kickMember(){
        DB::table('Users')->where('id_user', $_POST['id_user'])->update(['role_team' => null, 'id_team' => null]);
        
        if( isEmptyTeam($_POST['id_team'])){
            deleteTeam($_POST['id_team']);
        }

        return redirect('/manageTeam/'.$_POST['id_team']);
    }

    public function addMember(){
        DB::table('Users')->where('nickname', $_POST['nickname'])->where('id_team', null)->update(['role_team' => 'Member', 'id_team' => $_POST['id_team']]);
        return redirect('/manageTeam/'.$_POST['id_team']);
    }

    public function leaveTeam(){
        $role = Auth::user()->role_team;
        $id_team = Auth::user()->id_team;

        DB::table('Users')->where('id_user', $_POST['id_user'])->update(['role_team' => null, 'id_team' => null]);

        if(isEmptyTeam($_POST['id_team'])){
            deleteTeam($_POST['id_team']);
        }
        return redirect('/team');
    }

    public function changeLogo(){
        if(isset($_POST['select'])){
            $newLogo = $_POST['select'];
            DB::table("Teams")->where('id_team', $_POST['id_team'])->update(['logo' => $newLogo]);
            return redirect('/manageTeam/'.$_POST['id_team']);
        }
        return redirect('/manageTeam/'.$_POST['id_team']);
    }

}
