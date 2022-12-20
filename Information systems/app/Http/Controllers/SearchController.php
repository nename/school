<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(){
        $searchedString = '%'.$_GET['search'].'%';
        $userResults = DB::table('Users')->where('name', 'like', $searchedString)->orWhere('surname', 'like', $searchedString)->orWhere('nickname', 'like', $searchedString)->orWhere('email', 'like', $searchedString)->get();
        $teamResults = DB::table('Teams')->where('name', 'like', $searchedString)->orWhere('abbreviation', 'like', $searchedString)->get();
        $tournamentsResults = DB::table('Tournaments')->where('name', 'like', $searchedString)->get();
        return view('pages/search')->with('searched_string', $_GET['search'])->with('users', $userResults)->with('tournaments', $tournamentsResults)->with('teams', $teamResults);
    }
}
