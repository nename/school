require 'test/unit'
require 'scraper.rb'

class Test_team < Test::Unit::TestCase
  def test_get_team
    scraper = Scraper.new()

    results = scraper.get_team_by_id(4608)

    assert_equal results.id, 4608
    assert_equal results.name, 'Natus Vincere'
  end

  def test_raise
    scraper = Scraper.new()

    assert_raise(RuntimeError) { scraper.get_team_by_id(9999999) }
  end
end
