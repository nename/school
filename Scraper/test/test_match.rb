require 'test/unit'
require 'scraper.rb'

class Test_match < Test::Unit::TestCase
  def test_get_match
    scraper = Scraper.new()
    match = scraper.get_match_by_id(2359348)

    assert_equal match.team1.id.to_i, 10577
    assert_equal match.team2.id.to_i, 11712
    end

  def test_raise
    scraper = Scraper.new()
    assert_raise(RuntimeError) { scraper.get_match_by_id(3) }
  end
end
