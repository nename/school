<?php

use Illuminate\App;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TeamsTableSeeder::class);
        $this->command->info('Teams table seeded!');

        $this->call(UsersTableSeeder::class);
        $this->command->info('Users table seeded!');

        $this->call(TournamentsTableSeeder::class);
        $this->command->info('Tournaments table seeded!');

        $this->call(MatchesTableSeeder::class);
        $this->command->info('Matches table seeded!');

        $this->call(SponsorsTableSeeder::class);
        $this->command->info('Sponsors table seeded!');

        $this->call(UsersTournamentsTableSeeder::class);
        $this->command->info('Users_Tournaments table seeded!');

        $this->call(TeamsTournamentsSeeder::class);
        $this->command->info('Teams_Tournaments table seeded!');

        $this->call(TeamsMatchesSeeder::class);
        $this->command->info('Teams_Matches table seeded!');
    }
}

class TeamsTableSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['name' => 'Team of Titans','abbreviation' => 'ToT','logo' => 'team_icon_3.svg'],
            ['name' => 'Cosmos Warriors','abbreviation' => 'CW','logo' => 'team_icon_5.svg'],
            ['name' => 'Reidie Riders','abbreviation' => 'RRs','logo' => 'team_icon_4.svg'],
            ['name' => 'Optimistic Puppies','abbreviation' => 'OP','logo' => 'team_icon_2.svg'],
            ['name' => 'Black Hippo','abbreviation' => 'BHT','logo' => 'team_icon_1.svg'],
            ['name' => 'Admin Team','abbreviation' => 'AT','logo' => 'team_icon_4.svg']
        ];
        DB::table('Teams')->insert($data);
    }
}

class UsersTableSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['name' => 'Frank','surname' => 'Spicy','nickname' => 'Admin','email' => 'admin@mail.com','password' => '$2y$10$e0ChRP./B6cr6Bcr7fCt9O0nCRPssqb34WVtg9SWZGqOETjl6UiK2','birthdate' => '1990-10-10','role_user' => 'Admin','id_team' => '6','role_team' => 'Owner'],  
            ['name' => 'Gordon','surname' => 'Burke','nickname' => 'Titan','email' => 'gordon.burke@mail.com','password' => bcrypt('Titan123'),'birthdate' => '1989-10-25','role_user' => 'User','id_team' => '1','role_team' => 'Owner'],
            ['name' => 'Cosma','surname' => 'Cook','nickname' => 'Cosmo','email' => 'cosma.cook@mail.com','password' => bcrypt('Cosmo123'),'birthdate' => '2000-12-08','role_user' => 'User','id_team' => '2','role_team' => 'Owner'],
            ['name' => 'Carlene','surname' => 'Reid','nickname' => 'Reidie','email' => 'carlene.reid@mail.com','password' => bcrypt('Reidie123'),'birthdate' => '1995-08-06','role_user' => 'User','id_team' => '3','role_team' => 'Owner'],
            ['name' => 'Mark','surname' => 'Matthews','nickname' => 'Bad Mark','email' => 'mark.matthews@mail.com','password' => bcrypt('BadMark123'),'birthdate' => '1991-11-11','role_user' => 'User','id_team' => '4','role_team' => 'Owner'],
            ['name' => 'Tam','surname' => 'Reyes','nickname' => 'Tamster','email' => 'tam.reyes@mail.com','password' => bcrypt('Tamster123'),'birthdate' => '1994-04-04','role_user' => 'User','id_team' => '5','role_team' => 'Owner'],
            ['name' => 'Donald','surname' => 'Ortiz','nickname' => 'Cheese','email' => 'donald.ortiz@mail.com','password' => bcrypt('Cheese123'),'birthdate' => '2000-10-10','role_user' => 'User','id_team' => '1','role_team' => 'Member'],
            ['name' => 'Anil','surname' => 'Mills','nickname' => 'Anvil','email' => 'anil.mills@mail.com','password' => bcrypt('Anvil123'),'birthdate' => '1999-09-19','role_user' => 'User','id_team' => '1','role_team' => 'Member'],
            ['name' => 'Stephanie','surname' => 'Barnes','nickname' => 'Barn','email' => 'stephanie.barnes@mail.com','password' => bcrypt('Barn123'),'birthdate' => '1985-05-04','role_user' => 'User','id_team' => '1','role_team' => 'Member'],
            ['name' => 'Jude','surname' => 'Edwards','nickname' => 'Jay','email' => 'jude.edwards@mail.com','password' => bcrypt('Jay123'),'birthdate' => '1995-06-12','role_user' => 'User','id_team' => '2','role_team' => 'Member'],
            ['name' => 'Marvin','surname' => 'Alvarez','nickname' => 'Neilit','email' => 'marvin.alavez@mail.com','password' => bcrypt('Neilit123'),'birthdate' => '1997-09-09','role_user' => 'User','id_team' => '3','role_team' => 'Member'],
            ['name' => 'Jakob','surname' => 'Thompson','nickname' => 'Chilly','email' => 'jakob.thompson@mail.com','password' => bcrypt('Chilly123'),'birthdate' => '1976-12-19','role_user' => 'User','id_team' => '4','role_team' => 'Member'],
            ['name' => 'Marcello','surname' => 'Walker','nickname' => 'Marc','email' => 'marcello.walker@mail.com','password' => bcrypt('Marc123'),'birthdate' => '1974-02-16','role_user' => 'User','id_team' => '5','role_team' => 'Member'],
            ['name' => 'Sandra','surname' => 'Ramirez','nickname' => 'Ram','email' => 'sandra.ramirez@mail.com','password' => bcrypt('Ram123'),'birthdate' => '1996-06-16','role_user' => 'User','id_team' => '5','role_team' => 'Member'],
            ['name' => 'Anil','surname' => 'Young','nickname' => 'Tail','email' => 'anil.young@mail.com','password' => bcrypt('Tail123'),'birthdate' => '2001-11-11','role_user' => 'User','id_team' => '3','role_team' => 'Member'],
            ['name' => 'Jenkins','surname' => 'Wright','nickname' => 'Joy','email' => 'jenkins.wright@mail.com','password' => bcrypt('Joy123'),'birthdate' => '1950-11-11','role_user' => 'User','id_team' => NULL,'role_team' => NULL]
        ];

        DB::table('Users')->insert($data);
    }
}

class TournamentsTableSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['name' => 'Clash of Titans', 'start_date' => '2019-01-01 07:00:00', 'end_date' => '2021-12-31 20:00:00' ,'registration_ended' => 'True', 'registration_fee' => '50 kč', 'description' => '2 years long tournament for doubles', 'number_of_players' => '2', 'reward' => '150 kč', 'max_number_of_teams' => '16'],
            ['name' => 'Sunday Solo Cup', 'start_date' => '2020-03-01 07:00:00', 'end_date' => '2020-03-01 20:00:00' ,'registration_ended' => NULL, 'registration_fee' => '15 kč', 'description' => 'Sunday Cup. Big Reward for winner Come join us.', 'number_of_players' => '1' ,'reward' => '150 kč', 'max_number_of_teams' => '32'],
            ['name' => 'Spring cup', 'start_date' => '2020-02-01 07:00:00', 'end_date' => '2020-04-01 20:00:00' ,'registration_ended' => NULL, 'registration_fee' => '5 kč', 'description' => 'Big spring cup for duos.', 'number_of_players' => '2' ,'reward' => '200 kč', 'max_number_of_teams' => '64'],
            ['name' => 'Autumn cup','start_date' => '2019-08-01 07:00:00','end_date' => '2019-08-20 20:00:00','registration_ended' => 'True','registration_fee' => '10 kč','description' => 'Autumn solo cup for evryone who like to play solo.','number_of_players' => '1','reward' => '500 kč','max_number_of_teams' => '64']
        ];

        DB::table('Tournaments')->insert($data);
    }
}

class MatchesTableSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['id_match' => '1','round_number' => '2','id_tournament' => '1','id_next_match' => NULL],
            ['id_match' => '2','round_number' => '1','id_tournament' => '1','id_next_match' => '1'],
            ['id_match' => '3','round_number' => '1','id_tournament' => '1','id_next_match' => '1'],
            ['id_match' => '4','round_number' => '2','id_tournament' => '4','id_next_match' => NULL],
            ['id_match' => '5','round_number' => '1','id_tournament' => '4','id_next_match' => '4'],
            ['id_match' => '6','round_number' => '1','id_tournament' => '4','id_next_match' => '4']
        ];

        DB::table('Matches')->insert($data);
    }
}

class SponsorsTableSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['id_sponsor' => '1','name' => 'ColaCoka','id_tournament' => '1'],
            ['id_sponsor' => '2','name' => 'McDuck','id_tournament' => '1'],
            ['id_sponsor' => '3','name' => 'Admin Team','id_tournament' => '4'],
            ['id_sponsor' => '4','name' => 'Wans','id_tournament' => '4']
        ];

        DB::table('Sponsors')->insert($data);
    }
}

class UsersTournamentsTableSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['id_user' => '1','id_tournament' => '4','role_tournament' => 'organizer'],
            ['id_user' => '2', 'id_tournament' => '1','role_tournament' => 'organizer'],
            ['id_user' => '13', 'id_tournament' => '1','role_tournament' => 'referee'],
            ['id_user' => '16', 'id_tournament' => '2','role_tournament' => 'organizer'],
            ['id_user' => '16', 'id_tournament' => '3','role_tournament' => 'organizer'],
            
        ];

        DB::table('Users_Tournaments')->insert($data);
    }
}

class TeamsTournamentsSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['id_team' => '2','id_tournament' => '1'],
            ['id_team' => '3','id_tournament' => '1'],
            ['id_team' => '4','id_tournament' => '1'],
            ['id_team' => '1','id_tournament' => '2'],
            ['id_team' => '2','id_tournament' => '2'],
            ['id_team' => '3','id_tournament' => '2'],
            ['id_team' => '4','id_tournament' => '2'],
            ['id_team' => '5','id_tournament' => '2'],
            ['id_team' => '2','id_tournament' => '3'],
            ['id_team' => '3','id_tournament' => '3'],
            ['id_team' => '4','id_tournament' => '3'],
            ['id_team' => '5','id_tournament' => '3'],
            ['id_team' => '1','id_tournament' => '4'],
            ['id_team' => '2','id_tournament' => '4'],
            ['id_team' => '4','id_tournament' => '4'],
            ['id_team' => '5','id_tournament' => '4']

        ];

        DB::table('Teams_Tournaments')->insert($data);
    }
}

class TeamsMatchesSeeder extends Seeder {

    public function run()
    {
        $data = [
            ['id_team' => '1','id_match' => '5','score' => '1'],
            ['id_team' => '2','id_match' => '2','score' => NULL],
            ['id_team' => '2','id_match' => '4','score' => '1'],
            ['id_team' => '2','id_match' => '6','score' => '3'],
            ['id_team' => '3','id_match' => '1','score' => NULL],
            ['id_team' => '3','id_match' => '3','score' => NULL],
            ['id_team' => '4','id_match' => '2','score' => NULL],
            ['id_team' => '4','id_match' => '4','score' => '0'],
            ['id_team' => '4','id_match' => '5','score' => '2'],
            ['id_team' => '5','id_match' => '6','score' => '1']
        ];

        DB::table('Teams_Matches')->insert($data);
    }
}