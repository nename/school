# frozen_string_literal: false

# Function maps months to their numbers.
#
# @param month [String] Name of the month
# @return [Integer] Number of month
def get_month(month)
  {
    'January' => 1,
    'February' => 2,
    'March' => 3,
    'April' => 4,
    'May' => 5,
    'June' => 6,
    'July' => 7,
    'August' => 8,
    'September' => 9,
    'October' => 10,
    'November' => 11,
    'December' => 12
  }[month]
end
