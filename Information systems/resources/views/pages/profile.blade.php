@extends('layouts.app')
@section('content')
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">

                        {{$user->name}} "{{$user->nickname}}" {{$user->surname}}
                        @if( Auth::check() && Auth::user()->role_user == 'Admin' )
                            <form method="post" action="{{url('/deleteUser')}}" style="display: inline; float: right">
                                {{ csrf_field() }}
                                <input type='hidden' name='id_user' value={{$user->id_user}}>
                                <button type="submit" style="border:none; background-color: Transparent; color: red">
                                    Delete
                                </button>
                            </form>
                        @endif
                        @if( Auth::check() && (Auth::user()->role_user == 'Admin' || Auth::user()->id_user == $user->id_user))
                            <a href="{{ url('/profile/edit/'.$user->nickname) }}" style="color:blue; text-decoration:none; float: right"> Edit </a>
                        @endif
                    </div>
    
                    <div class="panel-body">
                        <p> <b> Team:  </b>
                        @if ($team)
                                <a href="{{ url('/team/'.$team->id_team) }}" style="color:inherit; text-decoration:none"> &nbsp; {{$team->name}} </a>
                        @endif
                        </p>
                        <p> <b> Email: </b>  &nbsp; {{$user->email}} </p>
                        <p> <b> Age:   </b>  &nbsp; {{\Carbon\Carbon::parse($user->birthdate)->age}} </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
