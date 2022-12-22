# Semestrální projekt NI-RUB

Jedná se o CLI webový scraper stránky [hltv.org](www.hltv.org), který získává data o zápasech, hráčích a týmech hry CS:GO.

## Instalace

Sestavení gemu

```bash
gem build scraper.gemspec
```
instalace gemu
```bash
gem install Scraper-0.1.0.gem
```
a následně je možné jej používat pomocí
```bash
scraper [options]
```

## Funkce

- vyhledat zápas podle id => [`get_match_by_id`](README.md#match)
- vyhledat hráče podle id => [`get_player_by_id`](README.md#player)
- vyhledat tým podle id => [`get_team_by_id`](README.md#team)
- vyhledat nejlepší hráče => [`get_top_players`](README.md#players)
- vyhledat nejlepší týmy => [`get_top_teams`](README.md#teams)
- najít aktuální výsledky => [`get_results`](README.md#results)
- najít probíhající zápasy => [`get_live_matches`](README.md#live)
- najít nadcházející zápasy => [`get_upcoming_matches`](README.md#upcoming)
- výpis výše zmíněných funkcí
- uložení výše zmíněných funkcí

## Spuštění

Program spuštěný bez možností vypíše nápovědu.

### Obecné možnosti

Počet výsledků, se kterými má program pracovat.
```
-c, --count COUNT [Integer]
```
Název souboru, kam má uložit data.
```
-s, --save FILE [String]
```
Vypnutí tisku.
```
--noPrint
```
Výpis nápovědy.
```
-h, --help
```

### Funkce

#### **Match**
Vyhledat zápas podle ID.
```
-m, --match MATCH_ID [Integer]
```

#### **Player**
Vyhledat hráče podle ID.
```
-p, --player PLAYER_ID [Integer]
```

#### **Team**
Vyhledat tým podle ID.
```
-t, --team TEAM_ID [Integer]
```

#### **Players**
Vyhledat nejlepší hráče. Možné použít parametr COUNT.
```
-a, --topPlayers
```

#### **Teams**
Vyhledat nejlepší týmy. Možné použít parametr COUNT.
```
-e, --topTeams
```

#### **Results**
Vyhledat aktuální výsledky zápasů. Možné použít parametr COUNT.
```
-r, --results
```

##### Další možnosti
Filtrování dle hodnocení zápasu 0-5 hvězd dle kvality.
```
--rating RATING [Integer]
```
Filtrování dle datumu od kdy bere výsledky. Formát YYYY-MM-DD
```
--startDate DATE [String]
```
Filtrování dle datumu do kdy bere výsledky. Formát YYYY-MM-DD
```
--endDate DATE [String]
```
Filtrování dle typu zápasu. Formát Lan nebo Online.
```
--type TYPE [String]
```
Filtrování dle konkrétní mapy (viz. [list of csgo maps](https://developer.valvesoftware.com/wiki/List_of_CS:GO_Maps)).
```
--map MAP [String]
```
Filtrování dle specifického eventu.
```
--eventID EVENT_ID [Integer]
```
Filtrování dle specifického hráče.
```
--playerID PLAYER_ID [Integer]
```
Filtrování dle specifického týmu.
```
--teamID TEAM_ID [Integer]
```
Posunutí ve výsledcích.
```
--offset OFFSET [Integer]
```

#### **Live**
Vyhledat probíhající zápasy.
```
-l, --liveMatches
```

#### **Upcoming**
Vyhledat nadcházející zápasy.
```
-u, --upcomingMatches
```

## Testy
Testy jsou v záložce test. Spuštění probíhá pomocí:
```
rake test
```

## Dokumentace
Dokumentace je generovaná pomocí gemu *Yard*. Nachází se ve složce *doc*.