# frozen_string_literal: false

require_relative 'request'

# module provides functions to pull data about top players
module TopPlayers
  # Function scrapes information about top players.
  #
  # @param count [Integer] Player count
  # @return [Array<OpenStruct>] Parsed data
  def get_top_players(count = 10)
    # html doc
    doc = get_response('stats/players')

    raise 'Something went wrong while pulling data' if doc == -1

    # stats
    table = doc.css('.stats-table.player-ratings-table')

    raise 'something went wrong: missing stats' if table.size.zero?

    # returning array
    players = []

    # players stats
    table.css('tr')[1..count].each do |stats|
      id = stats.css('.playerCol a').map { |a| a['href'].split('/')[3] }[0]
      nickname = stats.css('.playerCol a').text
      team = stats.css('.teamCol').map { |data| data['data-sort'] }[0]
      team_id = stats.css('.teamCol a').map { |a| a['href'].split('/')[3] }[0]
      maps = stats.css('.statsDetail')[0].text
      rounds = stats.css('.statsDetail')[1].text
      kd_diff = stats.css('.kdDiffCol').text
      kd = stats.css('.statsDetail')[2].text
      rating = stats.css('.ratingCol').text

      players.append(OpenStruct.new(id: id, nickname: nickname, team: team, team_id: team_id,
                                    maps: maps, rounds: rounds, kd_diff: kd_diff, kd: kd, rating: rating))
    end

    players
  end
end
