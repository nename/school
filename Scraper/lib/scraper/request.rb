# frozen_string_literal: false

require 'net/https'
require 'nokogiri'
require 'ostruct'

# Function sends request to obtain data.
#
# @param url [String] Url to data
# @return [Nokogiri::HTML] Body of response
def get_response(url)
  base_uri = URI('https://www.hltv.org')
  user_agent = { 'user-agent' => 'Ruby, Mozilla/5.0 (Macintosh; Intel Mac' \
     'OS X 10.9; rv:30.0) Gecko/20100101 Firefox/30.0' }

  begin
    uri = URI("#{base_uri}/#{URI(url)}")
    html = Net::HTTP.get_response(uri, user_agent)
  rescue StandardError => e
    puts e
    return -1
  end

  Nokogiri::HTML(html.body)
end
