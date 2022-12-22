require 'test/unit'
require 'scraper.rb'

class Test_top_teams < Test::Unit::TestCase
    def test_get_top_teams
        scraper = Scraper.new()

        teams = scraper.get_top_teams()

        assert_equal teams.size(), 10

        team = teams[0]

        assert_equal team.id.to_i, 4863
        assert_equal team.name, 'TYLOO'
        assert_equal team.rating.to_f, 1.06
    end
end
