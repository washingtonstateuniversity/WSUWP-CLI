Feature: Test the wsuwp user command

  Scenario: WSUWP user command exists
    Given a WP install

    When I run `wp wsuwp user`
    Then STDOUT should contain:
      """
      usage: wp wsuwp user create <user-email> [--first_name=<first_name>] [--last_name=<last_name>] [--porcelain]
      """
