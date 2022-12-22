# frozen_string_literal: false

require_relative 'request'
require_relative 'month'

# module provides functions to pull results of recent matches
module Results
  # Function returns actual results.
  # Can return filtered results by rating, from start_date to end_date, match_type,
  # map, event_id, player_id, team_id, offset and count.
  #
  # @param rating [Integer] Specific url to data
  # @param start_date [Date] From date
  # @param end_date [Date] To date
  # @param match_type [String] Type of match
  # @param map [String] Specific map
  # @param event_id [Integer] Event id
  # @param player_id [Integer] Player id
  # @param team_id [Integer] Team id
  # @param offset [Integer] Offset
  # @param count [Integer] Number of matches
  # @return [Array<OpenStruct>] Parsed data
  def get_data(rating: 0, start_date: '', end_date: '', match_type: '', map: '',
               event_id: 0, player_id: 0, team_id: 0, offset: 0, count: 100)
    # set offset
    uri = offset.nil? ? 'results?offset=000' : "results?offset=#{offset}"

    # searching by rating of match
    uri += "&stars=#{rating}" unless rating.nil?

    # from date
    uri += "&startDate=#{start_date}" unless start_date.nil?

    # to date
    uri += "&endDate=#{end_date}" unless end_date.nil?

    # type of match
    uri += "&matchType=#{match_type}" unless match_type.nil?

    # by map
    uri += "&map=#{map}" unless map.nil?

    # by event
    uri += "&event=#{event_id}" unless event_id.nil?

    # by player
    uri += "&player=#{player_id}" unless player_id.nil?

    # by team
    uri += "&team=#{team_id}" unless team_id.nil?

    # html doc
    doc = get_response(URI(uri))

    raise 'Something went wrong while pulling data' if doc == -1

    # get results
    results = doc.css('.results-holder.allres .results-sublist')

    raise 'Something went wrong: missing data' if results.size.zero?

    matches = []

    results.each do |result|
      # date
      headline = result.css('.standard-headline').text.split(' ')[2..]
      year = headline[2]
      month = get_month(headline[0])
      day = headline[1].scan(/\d/).join('')
      date = "#{day}.#{month}.#{year}"

      # matches
      result.css('.result-con').each do |match|
        id = match.css('a').map { |a| a['href'].split('/')[2] }[0]
        team1 = match.css('.team')[0].text
        team2 = match.css('.team')[1].text
        score = match.css('.result-score').text
        event = match.css('.event .event-name').text

        matches.append(OpenStruct.new(id: id, team1: team1, team2: team2, score: score, date: date,
                                      event: event))

        return matches if matches.size > count - 1
      end
    end

    matches
  end

  # @see #get_data
  # Main function to get data about results.
  # Can return filtered results by rating, from start_date to end_date, match_type,
  # map, event_id, player_id, team_id, offset and count.
  #
  # @param rating [Integer] Specific url to data
  # @param start_date [Date] From date
  # @param end_date [Date] To date
  # @param match_type [String] Type of match
  # @param map [String] Specific map
  # @param event_id [Integer] Event id
  # @param player_id [Integer] Player id
  # @param team_id [Integer] Team id
  # @param offset [Integer] Offset
  # @param count [Integer] Number of matches
  # @return [Array<OpenStruct>] Parsed data
  def get_results(rating: 0, start_date: '', end_date: '', match_type: '', map: '',
                  event_id: 0, player_id: 0, team_id: 0, offset: 0, count: 100)
    # can get only 100 matches per request
    cycles = (count / 100).to_i
    rest = count % 100

    matches = []
    offset = offset.nil? ? 0 : offset
    (1..cycles).each do |_num|
      matches.concat(get_data(rating: rating, start_date: start_date, end_date: end_date,
                              match_type: match_type, map: map, event_id: event_id,
                              player_id: player_id, team_id: team_id, offset: offset, count: 100))
      offset += 100
    end

    unless rest.zero?
      matches.concat(get_data(rating: rating, start_date: start_date, end_date: end_date,
                              match_type: match_type, map: map, event_id: event_id,
                              player_id: player_id, team_id: team_id, offset: offset, count: rest))
    end

    matches
  end
end
