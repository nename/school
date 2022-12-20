CREATE DATABASE Sports_tournaments_db;

CREATE TABLE Teams (
	id_team INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	abbreviation VARCHAR(255),
	logo VARCHAR(255)
);

CREATE TABLE Users (
	id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	surname VARCHAR(255),
	login VARCHAR(255),
	email VARCHAR(255),
	password VARCHAR(255),
	birthdate DATE,
	role_user VARCHAR(255),
	id_team INT UNSIGNED,
	role_team VARCHAR(255),
	FOREIGN KEY (id_team) REFERENCES Teams(id_team)
);

CREATE TABLE Tournaments (
	id_tournament INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	start_date DATETIME,
	end_date DATETIME,
	registration_fee INT,
	description TEXT,
	number_of_players INT,
	reward TEXT
);

CREATE TABLE Matches (
	id_match INT UNSIGNED AUTO_INCREMENT,
	round_number INT,
	id_tournament INT UNSIGNED,
	PRIMARY KEY(id_match, id_tournament),
	FOREIGN KEY (id_tournament) REFERENCES Tournaments(id_tournament)
);

CREATE TABLE Sponsors (
	id_sponsor INT UNSIGNED AUTO_INCREMENT,
	name VARCHAR(255),
	id_tournament INT UNSIGNED,
	PRIMARY KEY(id_sponsor, id_tournament),
	FOREIGN KEY (id_tournament) REFERENCES Tournaments(id_tournament)
);

CREATE TABLE Users_Tournaments (
	id_user INT UNSIGNED,
	id_tournament INT UNSIGNED,
	role_tournamemnt VARCHAR(255),
	PRIMARY KEY(id_user, id_tournament),
	FOREIGN KEY (id_user) REFERENCES Users(id_user),
	FOREIGN KEY (id_tournament) REFERENCES Tournaments(id_tournament)
);

CREATE TABLE Teams_Tournaments (
	id_team INT UNSIGNED,
	id_tournament INT UNSIGNED,
	PRIMARY KEY(id_team, id_tournament),
	FOREIGN KEY (id_team) REFERENCES Teams(id_team),
	FOREIGN KEY (id_tournament) REFERENCES Tournaments(id_tournament)
);

CREATE TABLE Teams_Matches (
	id_team INT UNSIGNED,
	id_match INT UNSIGNED,
	score INT,
	PRIMARY KEY(id_team, id_match),
	FOREIGN KEY (id_team) REFERENCES Teams(id_team),
	FOREIGN KEY (id_match) REFERENCES Matches(id_match)
);