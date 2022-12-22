# frozen_string_literal: false

require_relative 'request'
require 'date'

# module provides functions to pull data about upcoming matches
module UpcomingMatches
  # Function scrapes information about live matches.
  #
  # @param count [Integer] Match count
  # @return [Array<OpenStruct>] Parsed data
  def get_live_matches(count = 10)
    # html doc
    doc = get_response('matches')

    raise 'Something went wrong while pulling data' if doc == -1

    # stats
    live_matches = doc.css('.liveMatchesContainer')

    raise 'There are no live matches.' if live_matches.size.zero?

    matches = []

    live_matches.css('.liveMatch-container')[0..count-1].each do |match|
      id = match.css('a').map { |a| a['href'].split('/')[2] }[0]
      team1 = match.css('.matchTeamName')[0].text.strip
      team2 = match.css('.matchTeamName')[1].text.strip
      time = match.css('.matchTime').text.strip
      event = match.css('.matchEventName').text.strip
      link = "https://www.hltv.org#{match.css('a').map { |url| url['href'] }[0]}"

      matches.append(OpenStruct.new(id: id, team1: team1, team2: team2, time: time, date: Date.today,
                                    event: event, link: link))
    end

    matches
  end

  # Function scrapes information about upcoming matches.
  #
  # @param count [Integer] Match count
  # @return [Array<OpenStruct>] Parsed data
  def get_upcoming_matches(count = 10)
    # html doc
    doc = get_response('matches')

    # stats
    upcoming_matches = doc.css('.upcomingMatchesSection')

    raise 'something went wrong: missing data' if upcoming_matches.size.zero?

    date = upcoming_matches.css('.matchDayHeadline')[0].text

    matches = []

    upcoming_matches.css('.upcomingMatch')[0..count - 1].each do |match|
      id = match.css('a').map { |a| a['href'].split('/')[2] }[0]
      team1 = match.css('.matchTeam.team1').text.strip
      team2 = match.css('.matchTeam.team2').text.strip
      time = match.css('.matchTime').text
      event = match.css('.matchEvent .matchEventName').text
      link = "https://www.hltv.org#{match.css('a').map { |url| url['href'] }[0]}"

      matches.append(OpenStruct.new(id: id, team1: team1, team2: team2, time: time,
                                    date: date, event: event, link: link))
    end

    matches
  end
end
