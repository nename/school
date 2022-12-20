INSERT INTO Teams (name, abbreviation)
VALUES
("Team1", "T1"), 
("Team2", "T2"),
("Team3", "T3"),
("Team4", "T4");

INSERT INTO Users (name, surname, login, email, password, birthdate, role_user, id_team, role_team)
VALUES 
("Admin1", "Admin1_surname", "Admin1_login", "Admin1@mail.com", "Admin1_1234", "1990-10-10", "Admin", NULL, NULL),
("User1", "User1_surname", "User1_login", "User1@mail.com", "User1_1234", "1991-01-01", "User", 1, "Creator"), 
("User2", "User2_surname", "User2_login", "User2@mail.com", "User2_1234", "1992-02-02", "User", 1, "Player"), 
("User3", "User3_surname", "User3_login", "User3@mail.com", "User3_1234", "1993-03-03", "User", 2, "Creator"),
("User4", "User4_surname", "User4_login", "User4@mail.com", "User4_1234", "1994-04-04", "User", 2, "Player"),
("User5", "User5_surname", "User5_login", "User5@mail.com", "User5_1234", "1995-01-01", "User", 3, "Creator"), 
("User6", "User6_surname", "User6_login", "User6@mail.com", "User6_1234", "1996-02-02", "User", 3, "Player"), 
("User7", "User7_surname", "User7_login", "User7@mail.com", "User7_1234", "1997-03-03", "User", 4, "Creator"),
("User8", "User8_surname", "User8_login", "User8@mail.com", "User8_1234", "1998-04-04", "User", 4, "Player"),
("referee1", "referee1_surname", "referee1_login", "referee1@mail.com", "referee1_1234", "1981-04-04", "User", NULL, NULL),
("referee2", "referee2_surname", "referee2_login", "referee2@mail.com", "referee2_1234", "1982-04-04", "User", NULL, NULL),
("organizer1", "organizer1_surname", "organizer1_login", "organizer1@mail.com", "organizer1_1234", "1982-05-05", "User", NULL, NULL);

INSERT INTO Tournaments (name, start_date, end_date, registration_fee, description, number_of_players, reward)
VALUES 
("Tournament1", "2019-01-01", "2019-01-07", 500, "Tournament1 description", 4, "Tournament1 reward"),
("Tournament2", "2019-02-01", "2019-02-07", 600, "Tournament2 description", 4, "Tournament2 reward"), 
("Tournament3", "2019-03-01", "2019-03-07", 700, "Tournament3 description", 4, "Tournament3 reward");

INSERT INTO Matches (round_number, id_tournament)
VALUES 
(1, 1), 
(1, 1), 
(2, 1),
(1, 2), 
(1, 2), 
(2, 2),
(1, 3), 
(1, 3), 
(2, 3);

INSERT INTO Sponsors (name, id_tournament)
VALUES 
("Sponsor1", 1), 
("Sponsor2", 1),
("Sponsor3", 2),
("Sponsor3", 3);

INSERT INTO Users_Tournaments (id_user, id_tournament, role_tournamemnt)
VALUES 
(10, 1, "referee"), 
(11, 1, "referee"), 
(12, 1, "organizer"),
(10, 2, "referee"), 
(11, 2, "referee"), 
(12, 2, "organizer"),
(10, 3, "referee"), 
(11, 3, "referee"), 
(12, 3, "organizer");

INSERT INTO Teams_Tournaments (id_team, id_tournament)
VALUES 
(1, 1), 
(2, 1), 
(3, 1),
(4, 1),
(1, 2), 
(2, 2), 
(3, 2),
(4, 2),
(1, 3), 
(2, 3), 
(3, 3),
(4, 3);

INSERT INTO Teams_Matches (id_team, id_match, score)
VALUES 
(1, 1, 2), 
(2, 1, 3), 
(3, 2, 4),
(4, 2, 6),
(2, 3, 7), 
(4, 3, 9), 
(1, 4, 6), 
(2, 4, 3), 
(3, 5, 4),
(4, 5, 8),
(1, 6, 11), 
(4, 6, 9),
(1, 7, 4), 
(2, 7, 3), 
(3, 8, 7),
(4, 8, 6),
(1, 9, 7), 
(3, 9, 9);