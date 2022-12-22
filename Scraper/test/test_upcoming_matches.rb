require 'test/unit'
require 'scraper.rb'

class Test_upcoming_matches < Test::Unit::TestCase
    def test_get_upcoming_matches
        scraper = Scraper.new()

        matches = scraper.get_upcoming_matches()

        assert_equal matches.size(), 10

        match = matches[0]

        assert_not_equal match.id.to_i, 0
    end
end
