<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');

Auth::routes();

Route::get('/search', 'SearchController@search');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/profile/{nickname}', 'ProfileController@profile');

Route::get('/profile/edit/{nickname}', 'ProfileController@editProfile');

Route::post('/editUser', 'ProfileController@editUser');

Route::post('/deleteUser', 'ProfileController@deleteUser');

Route::get('/team', 'TeamController@createTeamPage');

Route::get('/team/{id_team}', 'TeamController@teamPage');

Route::get('/manageTeam/{id_team}', 'TeamController@manageTeamPage');

Route::post('/createTeam', 'TeamController@createTeam');

Route::post('/deleteTeam', 'TeamController@deleteTeam');

Route::post('/changeLogo', 'TeamController@changeLogo');

Route::post('/kickMember', 'TeamController@kickMember');

Route::post('/promoteMember', 'TeamController@promoteMember');

Route::post('/addMember', 'TeamController@addMember');

Route::post('/leaveTeam', 'TeamController@leaveTeam');

Route::get('/tournaments', 'TournamentController@tournamentListPage');

Route::get('/tournament_create', 'TournamentController@creationPage');

Route::post('/createTournament', 'TournamentController@createTournament');

Route::post('/deleteTournament', 'TournamentController@deleteTournament');

Route::get('/tournament/{id_tournament}', 'TournamentController@tournamentPage');

Route::get('/tournamentManage/{id_tournament}', 'TournamentController@manageTournamentPage');

Route::post('/addSponsor', 'TournamentController@addSponsor');

Route::post('/removeSponsor', 'TournamentController@removeSponsor');

Route::post('/kickTeam', 'TournamentController@kickTeam');

Route::post('/kickReferee', 'TournamentController@kickReferee');

Route::post('/setMaxTeams', 'TournamentController@setMaxTeams');

Route::post('/changeTournamentProperties', 'TournamentController@changeProperties');

Route::post('/generateMatches', 'TournamentController@generateMatches');

Route::get('/match/{id_match}', 'MatchController@matchPage');

Route::get('/match/create/{id_tournament}', 'MatchController@matchCreate');

Route::post('/match/save/{id_match}', 'MatchController@matchUpdate');

Route::get('/match/delete/{id_match}', 'MatchController@matchDelete');

Route::post('/joinTournament', 'TournamentController@joinTournament');

Route::post('/refereeTournament', 'TournamentController@refereeTournament');

Route::post('/refereeLeave', 'TournamentController@refereeLeave');

Route::post('/leaveTournament', 'TournamentController@leaveTournament');