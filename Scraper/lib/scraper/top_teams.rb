# frozen_string_literal: false

require_relative 'request'

# module provides functions to pull data about top teams
module TopTeams
  # Function scrapes information about top teams.
  #
  # @param count [Integer] Player count
  # @return [Array<OpenStruct>] Parsed data
  def get_top_teams(count = 10)
    # html doc
    doc = get_response('stats/teams')

    raise 'Something went wrong while pulling data' if doc == -1

    # stats
    stats = doc.css('.stats-table.player-ratings-table tbody')

    raise 'Something went wrong: missing data' if stats.size.zero?

    # returning array of teams
    teams = []

    # stats
    stats.css('tr')[0..count - 1].each do |team|
      id = team.css('.teamCol-teams-overview a').map { |a| a['href'].split('/')[3] }[0]
      name = team.css('.teamCol-teams-overview a').text
      maps = team.css('.statsDetail')[0].text
      kd_diff = team.css('.kdDiffCol').text
      kd = team.css('.statsDetail')[1].text
      rating = team.css('.ratingCol').text

      teams.append(OpenStruct.new(id: id, name: name, maps: maps, kd_diff: kd_diff, kd: kd,
                                  rating: rating))
    end

    teams
  end
end
