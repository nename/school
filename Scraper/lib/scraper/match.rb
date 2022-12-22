# frozen_string_literal: false

require_relative 'month'
require_relative 'request'

# module provides functions to pull data about specific match, printing the pulled data and saving the data
module Match
  # Data strcuture for match.
  #
  # @attr id [Integer] Match id
  # @attr score [String] Score
  # @attr date [String] Date of the match
  # @attr event [String] Event name
  # @attr team1 [Team_t] Team structure
  # @attr team2 [Team_t] Team structure
  # @attr maps [Array<Map_t>] Played maps
  Match_t = Struct.new(:id, :score, :date, :event, :team1, :team2, :maps)
  # Data strcuture for team.
  #
  # @attr id [Integer] Team id
  # @attr name [String] Name of the team
  # @attr players [Array<Player_t>] Players of the team
  Team_t = Struct.new(:id, :name, :players)
  # Data strcuture for player.
  #
  # @attr id [Integer] Player id
  # @attr name [String] Name of the player
  # @attr kills [Integer] Number of kills
  # @attr deaths [Integer] Number of deaths
  # @attr adr [Float] Average damage per round
  # @attr kast [Float] Average usefulness in round
  # @attr rating [Float] Rating
  Player_t = Struct.new(:id, :name, :kills, :deaths, :adr, :kast, :rating)
  # Data strcuture for map.
  #
  # @attr name [String] Name of the map
  # @attr score [String] Final score
  # @attr firsthalf [String] Firsthalf sides
  # @attr firsthalf_score [String] Firshalf score
  # @attr secondhalf [String] Secondhalf sides
  # @attr secondhalf_score [String] Secondhalf score
  # @attr overtime [String] Overtime score
  Map_t = Struct.new(:name, :score, :firsthalf, :firsthalf_score, :secondhalf, :secondhalf_score, :overtime)

  # Print match function.
  #
  # @param match [Match_t] Structure containing data
  # @return [nil]
  def print_match(match)
    puts "#{match.team1.name} (#{match.team1.id}) vs #{match.team2.name} (#{match.team2.id})\
    \nresult: #{match.score}\n#{match.date} #{match.event}"
    puts "\nplayed on:"
    match.maps.each { |map| print_struct(map) }
    puts "players of #{match.team1.name}:\n"
    match.team1.players.each { |player| print_struct(player) }
    puts "players of #{match.team2.name}:\n"
    match.team2.players.each { |player| print_struct(player) }
  end

  # Helper functions for saving player details.
  #
  # @param match [Match_t] Structure containing data
  # @param file [String] Filename where to save data
  # @return [nil]
  def save_players_details(match, file)
    # check if exists otherwise create file
    if File.file?(file)
      f = File.open(file, 'a')
    else
      f = File.new(file, 'w')
      f.puts('match_id;team_id;player_id;player_name;kills;deaths;adr;kast;rating')
    end

    # save players of team 1
    match.team1.players.each do |player|
      f.puts("#{match.id};#{match.team1.id};#{player.id};#{player.name};"\
        "#{player.kills};#{player.deaths};#{player.adr};#{player.kast};#{player.rating}")
    end

    # save players of team 2
    match.team2.players.each do |player|
      f.puts("#{match.id};#{match.team2.id};#{player.id};#{player.name};"\
        "#{player.kills};#{player.deaths};#{player.adr};#{player.kast};#{player.rating}")
    end
    f.close
  end

  # Helper functions for saving map details.
  #
  # @param match [Match_t] Structure containing data
  # @param file [String] Filename where to save data
  # @return [nil]
  def save_map_details(match, file)
    if File.file?(file)
      f = File.open(file, 'a')
    else
      f = File.new(file, 'w')
      f.puts('match_id;team1_id;team2_id;map_name;final_score;firsthalf_sides;firsthalf_score;'\
        'secondhalf_sides;secondhalf_score;overtime_score')
    end

    match.maps.each do |map|
      f.puts("#{match.id};#{match.team1.id};#{match.team2.id};"\
        "#{map.name};#{map.score};#{map.firsthalf};#{map.firsthalf_score};"\
        "#{map.secondhalf};#{map.secondhalf_score};#{map.overtime}")
    end
    f.close
  end

  # Functions for saving match information.
  #
  # @param match [Match_t] Structure containing data
  # @param file [String] Filename where to save data
  # @return [nil]
  def save_match(match, file)
    file = file.split('.')[0] if file.match(/\./)

    save_players_details(match, "#{file}_players.txt")
    save_map_details(match, "#{file}_maps.txt")
  end

  # Helper functions for extracting player information.
  #
  # @param stats [Nokogiri::html.body] Html to be parsed
  # @return [Array<Player_t>] Extracted data
  def get_players(stats)
    players = []
    stats.css('tr')[1..].each do |tr|
      id = tr.css('.players a').map { |a| a['href'].split('/')[2] }[0]
      name = tr.css('.player-nick').text
      kd = tr.css('.kd').text.split('-')
      adr = tr.css('.adr').text
      kast = tr.css('.kast').text.chop
      rating = tr.css('.rating').text

      players.append(Player_t.new(id, name, kd[0], kd[1], adr, kast, rating))
    end

    players
  end

  # Helper functions for extracting map information.
  #
  # @param maps_table [Nokogiri::html.body] Html to be parsed
  # @return [Array<Map_t>] Extracted data
  def get_maps(maps_table)
    maps = []
    # go through maps
    maps_table.each do |map|
      begin
        # map name
        name = map.css('.mapname').text

        # missing data
        next if name.eql? 'TBA'

        # map result
        score = "#{map.css('.results-team-score')[0].text}-#{map.css('.results-team-score')[1].text}"

        # get halftime scores
        halftime = map.css('.results-center-half-score span')

        # check if map was played
        next if halftime.empty?

        # first half
        firsthalf = "#{halftime[1].classes[0]}-#{halftime[3].classes[0]}"
        firsthalf_score = "#{halftime[1].text}-#{halftime[3].text}"

        # second half
        secondhalf = "#{halftime[5].classes[0]}-#{halftime[7].classes[0]}"
        secondhalf_score = "#{halftime[5].text}-#{halftime[7].text}"

        # overtime
        overtime = if halftime.size > 10
                     "#{halftime[11].text}-#{halftime[13].text}"
                   else
                     '0-0'
                   end
      rescue StandardError
        # on the page there are sometimes partially or completely missing data for maps
        # usually when team surrenders (set as bo3 and played only 1 game) or some bugs occur
        next
      end

      maps.append(Map_t.new(name, score, firsthalf, firsthalf_score, secondhalf, secondhalf_score, overtime))
    end

    maps
  end

  # Function parses data about match.
  #
  # @param match_id [Integer] Match id
  # @return [Match_t] Extracted data in structure
  def get_match_by_id(match_id)
    # html document
    doc = get_response("matches/#{match_id}/_")

    raise 'Something went wrong while pulling data' if doc == -1

    # data
    maps_table = doc.css('.g-grid.maps .mapholder')

    raise "Something went wrong: missing data #{match_id}" if maps_table.size.zero?

    # maps
    maps = get_maps(maps_table)

    # event
    event = doc.css('.timeAndEvent .event a').text

    # date
    date = doc.css('.timeAndEvent .date').text.split(' ')
    day = date[0].scan(/\d/).join('')
    month = get_month(date[2])
    year = date[3]
    date = "#{day}.#{month}.#{year}"

    # stats
    stats = doc.css('.stats-content .table.totalstats')

    raise 'something went wrong: missing data' if stats.size.zero?

    # TEAM 1
    team1 = Team_t.new
    team1.id = doc.css('.team1-gradient a').map { |a| a['href'].split('/')[2] }[0]
    team1.name = doc.css('.team1-gradient .teamName').text
    team1_score = doc.css('.team1-gradient .won, .team1-gradient .lost').text

    # table team 1 (stats)
    team1.players = get_players(stats[0])

    # TEAM 2
    team2 = Team_t.new
    team2.id = doc.css('.team2-gradient a').map { |a| a['href'].split('/')[2] }[0]
    team2.name = doc.css('.team2-gradient .teamName').text
    team2_score = doc.css('.team2-gradient .won, .team2-gradient .lost').text

    # table team 2 (stats)
    team2.players = get_players(stats[-1])

    Match_t.new(match_id, "#{team1_score}-#{team2_score}", date, event, team1, team2, maps)
  end
end
