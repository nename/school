require 'test/unit'
require 'scraper.rb'

class Test_player < Test::Unit::TestCase
  def test_get_player
    scraper = Scraper.new()

    player = scraper.get_player_by_id(7998)

    assert_equal player.realname, 'Oleksandr Kostyliev'
    assert_equal player.origin, 'Ukraine'
    assert_equal player.nickname, 's1mple'
    assert_equal player.team, 'Natus Vincere'
  end

  def test_raise
    scraper = Scraper.new()
    assert_raise(RuntimeError) { scraper.get_player_by_id(9999999) }
  end
end
