require 'test/unit'
require 'scraper.rb'

class Test_results < Test::Unit::TestCase
  def test_get_results
    scraper = Scraper.new()

    results = scraper.get_results(rating: nil, start_date: nil, end_date: nil, match_type: nil, 
                map: nil, event_id: nil, player_id: nil, team_id: 4608, offset: nil, count: 10)

    assert_equal results.size(), 10
    assert_equal results[0].team1, 'Natus Vincere'
  end

  def test_raise
    scraper = Scraper.new()

    assert_raise(RuntimeError) { scraper.get_results(rating: nil, start_date: nil, end_date: nil, 
      match_type: nil, map: nil, event_id: nil, player_id: nil, team_id: 3, offset: nil, count: 10) }
  end
end
