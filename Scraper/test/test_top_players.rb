require 'test/unit'
require 'scraper.rb'

class Test_top_players < Test::Unit::TestCase
	def test_get_top_players
		scraper = Scraper.new()

		players = scraper.get_top_players()

		assert_equal players.size(), 10

		player = players[0]

		assert_equal player.id.to_i, 11893
		assert_equal player.nickname, 'ZywOo'
		assert_equal player.team, 'Vitality'
		assert_equal player.team_id.to_i, 9565
	end	
end
