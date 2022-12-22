# frozen_string_literal: false

require_relative 'scraper/request'
require_relative 'scraper/match'
require_relative 'scraper/player'
require_relative 'scraper/results'
require_relative 'scraper/top_players'
require_relative 'scraper/top_teams'
require_relative 'scraper/upcoming_matches'
require_relative 'scraper/team'
require_relative 'scraper/parse'

# hltv.org scraper
class Scraper
  include Match
  include Player
  include Team
  include Results
  include TopPlayers
  include TopTeams
  include UpcomingMatches
  include Parser

  # Helper function for printing OpenStruct content.
  #
  # @param st [OpenStruct] OpenStruct to be printed
  # @return [nil]
  def print_struct(st)
    st.each_pair do |name, val|
      if val.is_a?(Array)
        print "\n"
        puts name
        val.each { |item| print_struct(item) }
        next
      else
        puts "#{name}: #{val}"
      end
    end
    print "\n"
  end

  # Print function for objects.
  #
  # @param arg [Struct or Array] Strucure or Array to be printed
  # @return [nil]
  def print_res(arg)
    # Match structure contains multiple structures where some of them don't contain
    # all of saved / printed data so I need specific function to save it
    case arg
    when Match_t
      print_match(arg)
    when Array
      # some data are returned in array
      arg.each { |item| print_struct(item) }
    when OpenStruct
      print_struct(arg)
    end
  end

  # Writes data into file.
  #
  # @param arg [Struct] Structure containing data
  # @param file [String] File where to save data
  # @return [nil]
  def save_res(arg, file)
    # MatchMa structure contains multiple structures where some of them don't contain
    # all of saved / printed data so I need specific function to save it
    if arg.is_a?(Match_t)
      save_match(arg, file)
    else
      # if exists, just append
      if File.file?(file)
        f = File.open(file, 'a')

      # if doesn't, create file
      else
        f = File.new(file, 'w')

        # some data are returned in array
        if arg.is_a?(Array)
          f.puts(arg[0].to_h.keys.join(';'))
        else
          f.puts(arg.to_h.keys.join(';'))
        end
      end

      # some data are returned in array
      if arg.is_a?(Array)
        arg.each { |item| f.puts(item.to_h.values.join(';')) }
      else
        f.puts(arg.to_h.values.join(';'))
      end
    end
  end

  # Main run function. Parses arguments and runs functions.
  #
  # @return [nil]
  def run(*)
    params = parse

    # check for multiple Get-commands
    test = %i[match topPlayers topTeams liveMatches upcomingMatches player results team]
    comm = test & params.keys
    raise 'Use only one Get-command at the time.' if comm.size > 1

    params[:count] = 10 if params[:count].nil?

    # apply command
    if comm.include?(:match)
      res = get_match_by_id(params[:match])
    elsif comm.include?(:team)
      res = get_team_by_id(params[:team])
    elsif comm.include?(:topPlayers)
      res = get_top_players(params[:count])
    elsif comm.include?(:topTeams)
      res = get_top_teams(params[:count])
    elsif comm.include?(:liveMatches)
      res = get_live_matches
    elsif comm.include?(:upcomingMatches)
      res = get_upcoming_matches(params[:count])
    elsif comm.include?(:player)
      res = get_player_by_id(params[:player])
    elsif comm.include?(:results)
      res = get_results(rating: params[:rating], start_date: params[:start_date], end_date: params[:end_date],
                        match_type: params[:match_type], map: params[:map], event_id: params[:event_id],
                        player_id: params[:player_id], team_id: params[:team_id], offset: params[:offset],
                        count: params[:count])
    end

    # print or/and save
    print_res(res) unless params[:noPrint]
    save_res(res, "#{Dir.pwd}/#{params[:save]}") if params[:save]
  end
end
