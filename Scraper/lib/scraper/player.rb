# frozen_string_literal: false

require_relative 'request'

# module provides functions to pull data about specific player
module Player
  # Function scrapes info about specific player
  #
  # @param player_id [Integer] Player id
  # @return [OpenStruct] Data about player
  def get_player_by_id(player_id)
    # response into nokogiri document
    doc = get_response("stats/players/#{player_id}/_")

    raise 'Something went wrong while pulling data' if doc == -1

    # player info and stats
    info = doc.css('.playerSummaryStatBox')

    raise 'something went wrong: data missing' if info.size.zero?

    # player info
    realname = doc.css('.summaryRealname').text.strip
    origin = doc.css('.summaryRealname img').map { |img| img['alt'] }[0]
    nickname = doc.css('.summaryNickname').text.strip
    team = doc.css('.SummaryTeamname').text.strip
    team_id = doc.css('.SummaryTeamname a').map { |a| a['href'].split('/')[3] }[0]
    age = doc.css('.summaryPlayerAge').text.strip

    # player stats
    stats = info.css('.summaryStatBreakdownDataValue')

    raise 'something went wrong: stats missing' if stats.size.zero?

    # player stats
    rating = stats[0].text
    dpr = stats[1].text
    kast = stats[2].text
    impact = stats[3].text
    adr = stats[4].text
    kpr = stats[5].text

    OpenStruct.new(player_id: player_id, realname: realname, origin: origin, nickname: nickname, team_id: team_id,
                   team: team, age: age, rating: rating, dpr: dpr, kast: kast, impact: impact, adr: adr, kpr: kpr)
  end
end
