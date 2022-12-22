# frozen_string_literal: true

Gem::Specification.new do |s|
  s.name        = 'Scraper'
  s.version     = '0.1.0'
  s.homepage    = 'https://gitlab.fit.cvut.cz/NI-RUB/B221/hamplto3'
  s.license     = 'MIT'
  s.author      = 'Tomáš Hampl'
  s.email       = 'hamplto3@fit.cvut.cz'

  s.summary     = 'Simple scraper of www.hltv.org'

  s.files       = Dir['bin/*', 'lib/**/*', '*.gemspec', 'LICENSE*', 'README*']
  s.executables = Dir['bin/*'].map { |f| File.basename(f) }
  s.has_rdoc    = 'yard'

  s.required_ruby_version = '>= 2.2'


  s.add_development_dependency 'rake', '~> 12.0'
  s.add_development_dependency 'rspec', '~> 3.6'
  s.add_development_dependency 'yard', '~> 0.9'
end
