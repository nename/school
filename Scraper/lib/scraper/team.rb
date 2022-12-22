# frozen_string_literal: false

require_relative 'request'

# module provides functions to pull data about specific player
module Team
  # Function scrapes info about team.
  #
  # @param team_id [Integer] Team id
  # @return [OpenStruct] Parsed data
  def get_team_by_id(team_id)
    # get html body
    doc = get_response("team/#{team_id}/_")

    raise 'Something went wrong while pulling data' if doc == -1

    # extract stats
    stats = doc.css('.teamProfile')

    raise 'Something went wrong: data missing' if stats.size.zero?

    # data about players
    team_players = stats.css('.bodyshot-team')

    players = []
    team_players.css('a').map { |a| a['href'] }.each do |link|
      players.append(OpenStruct.new(id: link.split('/')[2], name: link.split('/')[3]))
    end

    # data about team
    team = stats.css('.standard-box.profileTopBox')
    name = stats.css('.profile-team-container img').map { |img| img['alt'] }[0]

    stats = []
    team.css('.profile-team-stat').each do |stat|
      stats.append(stat.css('.right').text)
    end

    OpenStruct.new(id: team_id, name: name, world_ranking: stats[0], num_weeks_top30: stats[1],
                   avg_age: stats[2], coach: stats[3], players: players)
  end
end
