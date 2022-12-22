# frozen_string_literal: false

require 'optparse'

# input parser
module Parser
  # Function parses input.
  #
  # @return [Hash] Program options
  def parse(*)
    ARGV << '-h' if ARGV.empty?

    params = {}
    OptionParser.new do |opts|
      # basic
      opts.on('-c COUNT', '--count COUNT', Integer, 'Count of how many items should program return.') do |num|
        raise 'Wrong count, format has to be positive integer.' unless num.positive?

        num
      end
      opts.on('-s FILE', '--save FILE', String, 'Saves data into specific file with name FILE.')
      opts.on('--noPrint', 'Option stops priting of fetched data.')

      opts.on('-h', '--help', 'Prints help.') do
        puts opts
        puts "\nRun: scraper [options]\n\tUse only one Get-command at the time."
        exit
      end

      # match
      opts.on('-m MATCH_ID', '--match MATCH_ID', Integer, 'Get match with specific ID.') do |num|
        raise 'Wrong id, format has to be positive integer.' unless num.positive?

        num
      end

      # player
      opts.on('-p PLAYER_ID', '--player PLAYER_ID', Integer, 'Get player with specific ID.') do |num|
        raise 'Wrong id, format has to be positive integer.' unless num.positive?

        num
      end

      # team
      opts.on('-t TEAM_ID', '--team TEAM_ID', Integer, 'Get team with specific ID.') do |num|
        raise 'Wrong id, format has to be positive integer.' unless num.positive?

        num
      end

      # top players
      opts.on('-a', '--topPlayers', 'Get top players.')

      # top teams
      opts.on('-e', '--topTeams', 'Get top teams.')

      # live
      opts.on('-l', '--liveMatches', 'Get live matches.')

      # upcoming
      opts.on('-u', '--upcomingMatches', 'Get upcoming matches.')

      # results
      opts.on('-r', '--results',
              'Get most actual results. Can be modified by options. Check help for more information.')

      opts.on('--rating RATING', Integer,
              'Option for results. Sorting by rating. Format integer betwenn 0 and 5.') do |num|
        raise 'Wrong rating, format has to be integer within [0,5]' if num.negative? || num > 5 || !num.is_a?(Integer)

        num
      end
      opts.on('--startDate DATE', String, 'Option for results. Sorting from start date. Format YYYY-MM-DD.') do |date|
        raise 'Wrong date, format has to be YYYY-MM-DD.' unless date.match(/\d{4}-\d{2}-\d{2}/)

        date
      end
      opts.on('--endDate DATE', String, 'Option for results. Sorting to end date. Format YYYY-MM-DD.') do |date|
        raise 'Wrong date, format has to be YYYY-MM-DD.' unless date.match(/\d{4}-\d{2}-\d{2}/)

        date
      end
      opts.on('--type TYPE', String, 'Option for results. Sorting by match type. Can be Lan or Online.') do |type|
        raise 'Wrong match type, type has to be Lan or Online.' unless type.match(/^(Lan|Online)$/)

        type
      end
      opts.on('--map MAP', String, 'Option for results. Sorting by specific map.')
      opts.on('--eventID EVENT_ID', Integer, 'Option for results. Sorting by specific event.') do |num|
        raise 'Wrong event id, format has to be positive integer.' if num <= 0 || !num.is_a?(Integer)

        num
      end
      opts.on('--playerID PLAYER_ID', Integer, 'Option for results. Sorting by specific player.') do |num|
        raise 'Wrong player id, format has to be positive integer.' if num <= 0 || !num.is_a?(Integer)

        num
      end
      opts.on('--teamID TEAM_ID', Integer, 'Option for results. Sorting by specific team.') do |num|
        raise 'Wrong team id, format has to be positive integer.' if num <= 0 || !num.is_a?(Integer)

        num
      end
      opts.on('--offset OFFSET', Integer, 'Option for results. Sorting by specific offset.') do |num|
        raise 'Wrong offset, format has to be positive integer.' if num <= 0 || !num.is_a?(Integer)

        num
      end
    end.parse!(into: params)

    params
  end
end
