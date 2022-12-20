<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index(){
        return view('pages/index');
    }

    public function profile($nickname){
        $user = DB::table('Users')->where('nickname', $nickname)->first();
        $team = DB::table('Teams')->where('id_team', $user->id_team)->first();
        return view('pages/profile')->with('user', $user)->with('team', $team);
    }

    public function deleteUser(){
        if(Auth::check() && Auth::user()->role_user == 'Admin'){
            DB::table('Users_Tournaments')->where('id_user', $_POST['id_user'])->delete();
            DB::table('Users')->where('id_user', $_POST['id_user'])->delete();
            return redirect('/home');
        }else{
            return 'Unauthorized access!';
        }
    }

    public function editProfile($nickname){
        if(!Auth::check() || (Auth::user()->nickname != $nickname && Auth::user()->role_user != 'Admin')){
            return redirect('profile/'.$nickname);
        }
        $user = DB::table('Users')->where('nickname', $nickname)->first();

        return view('pages/profileEdit')->with('user', $user);

    }

    public function editUser(){
        if(!Auth::check() || (Auth::user()->id_user != $_POST['id_user'] && Auth::user()->role_user != 'Admin')){
            return redirect('profile/'.$_POST['nickname']);
        }
        $user = DB::table('Users')->where('id_user', $_POST['id_user'])->first();
        
        if(strpos($_POST['nickname'], '/') !== false){
            return redirect('profile/edit/'.$user->nickname);
        }

        $foundUser = DB::table('Users')->where('nickname', $_POST['nickname'])->first();
        if(!is_null($foundUser) && $foundUser->id_user != $user->id_user){
            return redirect('profile/edit/'.$user->nickname);
        }

        DB::table('Users')->where('id_user', $user->id_user)
            ->update(['name' => $_POST['name'], 'surname' => $_POST['surname'], 'nickname' => $_POST['nickname'], 'birthdate' => $_POST['birthdate']]);
        
        $updatedUser = DB::table('Users')->where('id_user', $user->id_user)->first();
        return redirect('profile/'.$updatedUser->nickname);
    }
}
