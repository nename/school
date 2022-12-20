<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .a {
            position: absolute;
            top: 50%;
            left: 50%;
            margin-right: -50%;
            transform: translate(-50%, -50%)
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a style="padding-top: 25px" class="navbar-brand" href="{{ url('/home') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-left" style="padding-top: 3px">

                        <li>
                            @if (Auth::guest())
                                <a title="profile" href="{{ route('login') }}">
                                    <div align="center"><img src={{url('/svg/user.svg')}} height="25px"/></div>
                                    <div style="font-size: 3mm">Profile</div>
                                </a>
                            @else
                                <a title="profile" href="{{ url('/') }}/profile/{{ Auth::user()->nickname}}">
                                    <div align="center"><img src={{url('/svg/user.svg')}} height="25px"/></div>
                                    <div style="font-size: 3mm">Profile</div>
                                </a>
                            @endif
                        </li>

                        <li>
                        @if (Auth::guest())
                            <a title="team" href="{{ route('login') }}">
                                <div align="center"><img src={{url('/svg/team.svg')}} height="25px"/></div>
                                <div align="center" style="font-size: 3mm">Team</div>
                            </a>
                        @elseif (Auth::user()->id_team != null)
                            <a title="team" href="{{ url('/') }}/team/{{ Auth::user()->id_team}}">
                                <div align="center"><img src={{url('/svg/team.svg')}} height="25px"/></div>
                                <div align="center" style="font-size: 3mm">Team</div>
                             </a>
                        @else
                             <a title="team" href="{{ url('/') }}/team">
                                 <div align="center"><img src={{url('/svg/team.svg')}} height="25px"/></div>
                                 <div align="center" style="font-size: 3mm">Team</div>
                             </a>
                        @endif
                        </li>

                        <li>
                            <a title="tournaments" href="{{ url('/tournaments') }}">
                                <div align="center"><img src={{url('/svg/tournament.svg')}} height="25px"/></div>
                                <div style="font-size: 3mm">Tournaments</div>
                            </a>
                        </li>
                    </ul>

                    <!-- SEARCH -->
                    <ul class="nav navbar-nav" style="margin-left: 15%; margin-right: auto;padding-top: 7px">
                        <li>
                        <a>
                            <form action="{{ url('/search') }}" method="GET" role="search">
                                <input type="search" name="search" placeholder="Search..." class="btn btn-default">
                                <button title="search"type="submit" class="btn btn-primary" style="background-color: white;border-color: white">
                                        &#x1f50d;
                                </button>
                            </form>
                        </a>
                        </li>
                    </ul>
                    
                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right" style="padding-top: 15px">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} {{ Auth::user()->nickname }} {{ Auth::user()->surname }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">.

                                    <li><a href="{{ url('/') }}/profile/{{ Auth::user()->nickname}}">Profile</a></li>
                                    @if (Auth::user()->id_team != null)
                                        <li><a href="{{ url('/') }}/team/{{ Auth::user()->id_team}}">Team</a></li>
                                    @else
                                        <li><a href="{{ url('/') }}/team">Team</a></li>
                                    @endif
                                    
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
